<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DOMDocument;
use DOMXPath;
use Exception;

class SemController extends Controller
{
    /**
     * User agent strings to rotate through
     */
    protected $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    ];

    /**
     * Search engine configuration
     */
    protected $searchEngineConfig = [
        'url' => 'https://www.google.com/search',
        'resultsPerPage' => 10,
        'maxPages' => 2, // Reduced from 5 to 2 to avoid CAPTCHA
    ];

    /**
     * Show the search form
     */
    public function index(Request $request)
    {
        // Get search history from session if exists
        $searchHistory = $request->session()->get('search_history', []);

        return view('tools.sem.index', compact('searchHistory'));
    }

    /**
     * Prepare domain for search
     */
    protected function prepareDomain($domain)
    {
        // Remove www. if present
        $domain = preg_replace('/^www\./', '', $domain);
        $domain = trim($domain);
        return $domain;
    }

    /**
     * Process the search request
     */
    public function search(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string|max:255',
            'website' => 'required|url',
            'pages' => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            // Return validation errors based on request type
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Extract domain from website URL
        $website = $request->input('website');
        $domain = parse_url($website, PHP_URL_HOST);
        if (!$domain) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid website URL format'
                ], 422);
            }
            
            return redirect()->back()->with('error', 'Invalid website URL format')->withInput();
        }

        // Prepare domain for search
        $domain = $this->prepareDomain($domain);
        
        // Process the search
        try {
            $pages = min($request->input('pages', 2), 2); // Limit to 2 pages max to prevent CAPTCHA
            $checkAllPages = $request->has('check_all_pages');
            
            // Process search
            $results = $this->processSearch($request->input('keyword'), $domain, $pages, $checkAllPages);

            // Create a unique ID for this search
            $searchId = uniqid();

            // Save search to session history
            $searchHistory = $request->session()->get('search_history', []);

            // Add search to history
            $searchHistory[$searchId] = [
                'id' => $searchId,
                'keyword' => $request->input('keyword'),
                'website' => $website,
                'domain' => $domain,
                'pages_checked' => $pages,
                'timestamp' => time(),
                'results' => $results
            ];

            // Limit history to last 10 searches
            if (count($searchHistory) > 10) {
                $searchHistory = array_slice($searchHistory, -10, 10, true);
            }

            // Save back to session
            $request->session()->put('search_history', $searchHistory);

            // Return response based on request type
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $results,
                    'search_id' => $searchId
                ]);
            }
            
            // Redirect to results page for form submissions
            return redirect()->route('tools.sem.results', ['id' => $searchId]);
            
        } catch (Exception $e) {
            Log::error('SEM search error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing your search: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while processing your search: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Process search
     */
    protected function processSearch($keyword, $domain, $pages, $checkAllPages)
    {
        $startTime = microtime(true);
        $result = [
            'keyword' => $keyword,
            'domain' => $domain,
            'pages_checked' => $pages,
            'positions' => [],
            'found' => false,
            'total_results' => 0,
            'timestamp' => time(),
            'search_time' => 0,
            'search_engine' => 'Google',
        ];

        // Generate a single optimized query - prioritize exact domain match if search is domain-like
        $searchQuery = $this->generateOptimizedQuery($keyword, $domain);
        $resultsPerPage = $this->searchEngineConfig['resultsPerPage'];
        
        Log::info("Searching with query: {$searchQuery}");
        
        try {
            // Only search first page to minimize rate limits
            $html = $this->fetchSearchResults($searchQuery, 0);
            
            if ($html) {
                $positions = $this->parseSearchResults($html, $domain, 0);
                
                if (!empty($positions)) {
                    $result['positions'] = $positions;
                    $result['found'] = true;
                    $result['total_results'] = $this->extractTotalResults($html);
                    
                    Log::info("Found " . count($positions) . " matches on first page");
                } else {
                    // Extract total results count even if no matches
                    $result['total_results'] = $this->extractTotalResults($html);
                    
                    // If checkAllPages is true and pages > 1, check additional pages
                    if ($checkAllPages && $pages > 1) {
                        // Add a fixed delay to avoid rate limiting
                        sleep(5);
                        
                        $html = $this->fetchSearchResults($searchQuery, $resultsPerPage);
                        
                        if ($html) {
                            $morePositions = $this->parseSearchResults($html, $domain, $resultsPerPage);
                            
                            if (!empty($morePositions)) {
                                $result['positions'] = array_merge($result['positions'], $morePositions);
                                $result['found'] = true;
                                Log::info("Found " . count($morePositions) . " matches on second page");
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Don't throw here, just log and continue with empty results
            Log::warning("Search error: " . $e->getMessage());
            
            // Add message to result so UI can display it
            $result['error_message'] = "Search engine limit exceeded. Try again later.";
        }
        
        // Sort positions by position number if we have any
        if (!empty($result['positions'])) {
            usort($result['positions'], function($a, $b) {
                return $a['position'] - $b['position'];
            });
            
            // Clean up duplicates
            $urls = [];
            $uniquePositions = [];
            
            foreach ($result['positions'] as $position) {
                $url = $position['url'];
                if (!in_array($url, $urls)) {
                    $urls[] = $url;
                    $uniquePositions[] = $position;
                }
            }
            
            $result['positions'] = $uniquePositions;
        }
        
        // Calculate search time
        $result['search_time'] = round(microtime(true) - $startTime, 2);
        return $result;
    }
    
    /**
     * Generate a single optimized search query
     */
    protected function generateOptimizedQuery($keyword, $domain)
    {
        // Check if keyword is likely a domain itself
        if (stripos($keyword, $domain) !== false || filter_var($keyword, FILTER_VALIDATE_DOMAIN)) {
            return "site:$domain"; // Use site: operator for domain searches
        }
        
        // For normal keywords, we'll use the keyword as is
        return $keyword;
    }

    /**
     * Fetch search results
     */
    protected function fetchSearchResults($query, $start = 0)
    {
        $userAgent = $this->userAgents[array_rand($this->userAgents)];
        
        $params = [
            'q' => $query,
            'start' => $start,
            'num' => 10,
            'hl' => 'en',
            'gl' => 'us',
        ];
        
        try {
            Log::info("Fetching search results for: '$query' (start: $start)");
            
            $response = Http::withHeaders([
                'User-Agent' => $userAgent,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Referer' => 'https://www.google.com/',
            ])->timeout(10)->get($this->searchEngineConfig['url'], $params);
            
            if ($response->successful()) {
                $body = $response->body();
                
                // Check for CAPTCHA
                if (stripos($body, 'captcha') !== false && 
                   (stripos($body, 'unusual traffic') !== false || 
                    stripos($body, 'automated requests') !== false)) {
                    throw new Exception("CAPTCHA detected - search engine blocked automated requests");
                }
                
                return $body;
            }
            
            if ($response->status() === 429) {
                throw new Exception("Rate limited by search engine (429 Too Many Requests)");
            }
            
            Log::error("Search error: " . $response->status());
            return null;
        } catch (Exception $e) {
            Log::error("Search exception: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse the search results HTML
     */
    protected function parseSearchResults($html, $domain, $start = 0)
    {
        $positions = [];
        $domParser = new DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $domParser->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($domParser);
        $position = $start + 1;
        
        // Google result containers - try multiple selectors for robustness
        $resultSelectors = [
            '//div[@class="g"]',
            '//div[contains(@class, "g")]',
            '//div[contains(@class, "tF2Cxc")]',
            '//div[@data-hveid]//div[@data-ved]'
        ];
        
        $results = null;
        
        // Try each selector until we get results
        foreach ($resultSelectors as $selector) {
            $results = $xpath->query($selector);
            if ($results && $results->length > 0) {
                break;
            }
        }
        
        if (!$results || $results->length === 0) {
            return $positions;
        }
        
        foreach ($results as $result) {
            // Extract URL - try different selectors
            $link = null;
            $url = null;
            
            $linkSelectors = [
                './/a[@href]',
                './/a[contains(@href, "http")]',
                './/a[@ping]'
            ];
            
            foreach ($linkSelectors as $selector) {
                $links = $xpath->query($selector, $result);
                if ($links && $links->length > 0) {
                    $link = $links->item(0);
                    $url = $link->getAttribute('href');
                    break;
                }
            }
            
            if (!$url) {
                $position++;
                continue;
            }
            
            // Clean Google redirect URLs
            if (strpos($url, '/url?') !== false) {
                parse_str(parse_url($url, PHP_URL_QUERY), $params);
                if (isset($params['q'])) {
                    $url = $params['q'];
                }
            }
            
            // Check if this URL matches our domain
            if ($this->isDomainMatch($url, $domain)) {
                // Extract title
                $title = '';
                $titleNodes = $xpath->query('.//h3', $result);
                if ($titleNodes && $titleNodes->length > 0) {
                    $title = $titleNodes->item(0)->textContent;
                }
                
                $positions[] = [
                    'position' => $position,
                    'page' => floor(($position - 1) / 10) + 1,
                    'url' => $url,
                    'title' => $title ?: $domain,
                    'type' => 'organic',
                    'is_target' => true
                ];
                
                Log::info("Found match at position $position: $url");
            }
            
            $position++;
        }
        
        return $positions;
    }
    
    /**
     * Check if a URL matches a domain using multiple strategies
     */
    protected function isDomainMatch($url, $domain)
    {
        // Extract hostname from URL
        $urlHost = parse_url($url, PHP_URL_HOST);
        
        // If parsing failed, try direct string matching
        if (empty($urlHost)) {
            return stripos($url, $domain) !== false;
        }
        
        // Remove www. prefix for consistent comparison
        $urlHost = preg_replace('/^www\./', '', $urlHost);
        
        // Strategy 1: Exact domain match (most reliable)
        if (strcasecmp($urlHost, $domain) === 0) {
            return true;
        }
        
        // Strategy 2: Domain is a subdomain
        if (preg_match("/\\.{$domain}$/i", $urlHost)) {
            return true;
        }
        
        // Strategy 3: URL contains the domain
        if (stripos($url, $domain) !== false) {
            return true;
        }
        
        return false;
    }

    /**
     * Extract total number of search results
     */
    protected function extractTotalResults($html)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Google result stats
        $statsNode = $xpath->query('//div[@id="result-stats"]');
        
        if ($statsNode && $statsNode->length > 0) {
            $stats = $statsNode->item(0)->textContent;
            // Extract number from string like "About 1,570,000,000 results (0.35 seconds)"
            if (preg_match('/[\d,\.]+/', $stats, $matches)) {
                return (int) str_replace([',', '.'], '', $matches[0]);
            }
        }
        
        return 0;
    }

    /**
     * Show search history
     */
    public function history(Request $request)
    {
        $searchHistory = $request->session()->get('search_history', []);

        // Sort by timestamp descending (newest first)
        uasort($searchHistory, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return view('tools.sem.history', compact('searchHistory'));
    }

    /**
     * Show detailed results for a specific search
     */
    public function results($id, Request $request)
    {
        $searchHistory = $request->session()->get('search_history', []);

        if (!isset($searchHistory[$id])) {
            return redirect()->route('tools.sem.index')->with('error', 'Search not found');
        }

        $searchResult = $searchHistory[$id];

        return view('results.sem_results', compact('searchResult'));
    }

    /**
     * Clear search history
     */
    public function clearHistory(Request $request)
    {
        $request->session()->forget('search_history');

        return redirect()->route('tools.sem.index')->with('success', 'Search history cleared');
    }

    /**
     * Delete a search from history
     */
    public function deleteSearch($id, Request $request)
    {
        $searchHistory = $request->session()->get('search_history', []);

        if (isset($searchHistory[$id])) {
            unset($searchHistory[$id]);
            $request->session()->put('search_history', $searchHistory);
        }

        return redirect()->route('tools.sem.history')->with('success', 'Search deleted');
    }
}
