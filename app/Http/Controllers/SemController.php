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
    protected $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/116.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36',
    ];

    protected $searchBaseUrl = 'https://www.google.com/search';
    protected $maxPages = 10; // Default 10 pages (100 results)

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
     * Prepare domain for search by removing www and handling various formats
     */
    protected function prepareDomain($domain)
    {
        // Remove www. if present
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Ensure domain is properly formatted
        $domain = trim($domain);
        
        // Log the prepared domain
        Log::info("Prepared domain for search: {$domain}");
        
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
        
        // Log the domain and TLD for debugging
        $domainParts = explode('.', $domain);
        $tld = end($domainParts);
        Log::info("Processing search for domain: {$domain} with TLD: {$tld}");

        // Process the search
        try {
            $pages = $request->input('pages', $this->maxPages);
            $checkAllPages = $request->has('check_all_pages');
            $results = $this->processSearch($request->input('keyword'), $domain, $pages, $checkAllPages);

            // Save search to session history
            $searchHistory = $request->session()->get('search_history', []);

            // Create a unique ID for this search
            $searchId = uniqid();

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
     * Process the search through Google
     */
    protected function processSearch($keyword, $domain, $pages, $checkAllPages = false)
    {
        $result = [
            'keyword' => $keyword,
            'domain' => $domain,
            'pages_checked' => $pages,
            'positions' => [],
            'found' => false,
            'total_results' => 0,
            'timestamp' => time(),
            'search_time' => 0, // Track how long the search takes
        ];
        
        $startTime = microtime(true);
        
        // Determine if keyword is a domain name or contains a domain name
        $isDomainSearch = (stripos($keyword, $domain) !== false) || 
                          filter_var($keyword, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
        
        // For domain searches, we'll try multiple search variants
        $searchQueries = [$keyword]; // Default to just the keyword
        
        if ($isDomainSearch) {
            Log::info("Detected domain in keyword: {$keyword}");
            // Add a site: search to help find the exact domain
            $searchQueries[] = "site:{$domain}";
            // Also search for the domain with quotes to get exact matches
            $searchQueries[] = "\"{$domain}\"";
        }
        
        $resultsPerPage = 10;
        $searchDelay = rand(1, 2); // Slightly reduced delay to avoid long waits

        // Try each search query until we find results
        foreach ($searchQueries as $searchQuery) {
            Log::info("Trying search query: {$searchQuery}");
            $tempResults = [
                'positions' => [],
                'found' => false,
                'total_results' => 0,
            ];
            
            // Search through requested number of pages
            for ($page = 0; $page < $pages; $page++) {
                // Wait a bit before each request (except the first one)
                if ($page > 0) {
                    sleep($searchDelay);
                }

                $start = $page * $resultsPerPage;
                $html = $this->fetchSearchResults($searchQuery, $start);

                if (!$html) {
                    Log::warning("Failed to fetch results for page " . ($page+1));
                    continue;
                }

                $positions = $this->parseSearchResults($html, $domain, $start);

                // Add found positions to the temp results
                if (!empty($positions)) {
                    $tempResults['positions'] = array_merge($tempResults['positions'], $positions);
                    $tempResults['found'] = true;
                }

                // Extract total results count (only need to do this once)
                if ($page === 0 && $tempResults['total_results'] === 0) {
                    $tempResults['total_results'] = $this->extractTotalResults($html);
                    Log::info("Total results found: {$tempResults['total_results']} for query: {$searchQuery}");
                }

                // If we've found positions and not checking all pages, we can stop searching this query
                if (!empty($positions) && !$checkAllPages) {
                    break;
                }
            }
            
            // If we found positions with this query, use these results and stop trying other queries
            if ($tempResults['found']) {
                $result['positions'] = $tempResults['positions'];
                $result['found'] = true;
                $result['total_results'] = $tempResults['total_results'];
                
                Log::info("Found positions using query: {$searchQuery}. Stopping search.");
                break;
            }
            
            // If we didn't find anything but got total results, at least record that
            if ($tempResults['total_results'] > 0 && $result['total_results'] === 0) {
                $result['total_results'] = $tempResults['total_results'];
            }
        }
        
        // Sort positions by position number
        if (!empty($result['positions'])) {
            usort($result['positions'], function($a, $b) {
                return $a['position'] - $b['position'];
            });
        }
        
        // Calculate search time in seconds
        $result['search_time'] = round(microtime(true) - $startTime, 2);
        Log::info("Search completed in {$result['search_time']} seconds");
        
        return $result;
    }

    /**
     * Fetch search results from Google
     */
    protected function fetchSearchResults($keyword, $start = 0)
    {
        // Random user agent to avoid blocking
        $userAgent = $this->userAgents[array_rand($this->userAgents)];

        // Enhance the search query with site-specific parameters if needed
        $params = [
            'q' => $keyword,
            'start' => $start,
            'num' => 10,
            'hl' => 'en',   // Language for search interface
            'gl' => 'us',   // Geographic location
            'safe' => 'off', // No safesearch
            'filter' => '0', // Show all results, don't filter similar results
            'pws' => '0',    // Don't personalize search results
        ];

        try {
            Log::info("Searching Google for: {$keyword} (start: {$start})");
            
            $response = Http::withHeaders([
                'User-Agent' => $userAgent,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
            ])->timeout(15)->get($this->searchBaseUrl, $params);

            if ($response->successful()) {
                $responseBody = $response->body();
                $responseSize = strlen($responseBody);
                Log::info("Search successful, received {$responseSize} bytes");
                
                // For debugging, save a sample of the HTML response
                if ($start === 0) {
                    Log::debug("HTML Response Sample: " . substr($responseBody, 0, 500) . "...");
                }
                
                return $responseBody;
            }

            Log::error('Google search error: ' . $response->status() . ' - ' . $response->body());
            return null;
        } catch (Exception $e) {
            Log::error('Google search exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse the search results HTML
     */
    protected function parseSearchResults($html, $domain, $start = 0)
    {
        $positions = [];

        // Create a new DOM document
        $dom = new DOMDocument();

        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Find all search results - try multiple possible selectors because Google changes their HTML structure often
        $searchSelectors = [
            '//div[@class="g"]',           // Standard Google result container
            '//div[contains(@class, "g")]', // Variation with additional classes
            '//div[contains(@class, "tF2Cxc")]', // Another common result container
            '//div[contains(@class, "yuRUbf")]', // Link container in results
            '//div[@data-sokoban-container]' // Data attribute sometimes used
        ];
        
        $resultsCount = 0;
        $position = $start + 1;
        
        // Try each selector until we find results
        foreach ($searchSelectors as $selector) {
            $searchResults = $xpath->query($selector);
            $resultsCount = $searchResults->length;
            
            if ($resultsCount > 0) {
                Log::info("Found {$resultsCount} results using selector: {$selector}");
                break;
            }
        }
        
        if ($resultsCount === 0) {
            Log::warning("No search results found with any selector. HTML structure may have changed.");
            return $positions;
        }

        // Process each search result
        foreach ($searchResults as $result) {
            // Try multiple link selectors
            $linkSelectors = [
                './/a[@href]',
                './/h3/parent::a',
                './/h3/parent::*/a',
                './/h3/..//a'
            ];
            
            $linkNodes = null;
            
            // Try each link selector
            foreach ($linkSelectors as $linkSelector) {
                $linkNodes = $xpath->query($linkSelector, $result);
                if ($linkNodes && $linkNodes->length > 0) {
                    break;
                }
            }

            if (!$linkNodes || $linkNodes->length === 0) {
                continue; // No link found, skip this result
            }

            // Get the URL from the first link
            $url = $linkNodes->item(0)->getAttribute('href');
            
            // Clean and normalize the URL
            if (strpos($url, '/url?') !== false || strpos($url, '/search?') !== false) {
                // Extract URL from Google redirect
                parse_str(parse_url($url, PHP_URL_QUERY), $params);
                if (isset($params['q'])) {
                    $url = $params['q'];
                } elseif (isset($params['url'])) {
                    $url = $params['url'];
                }
            }
            
            // Ensure URL is properly decoded
            $url = urldecode($url);
            
            // Try to extract hostname from URL
            $urlHost = parse_url($url, PHP_URL_HOST);
            
            // If parsing failed or no host found, try to match the raw URL
            if (empty($urlHost)) {
                Log::warning("Failed to parse host from URL: {$url}");
                $urlHost = "";
            } else {
                // Normalize the host by removing www.
                $urlHost = preg_replace('/^www\./', '', $urlHost);
            }
            
            Log::debug("Checking URL: {$url}, Host: {$urlHost}, Against Domain: {$domain}");
            
            // Multiple matching strategies
            $isDomainMatch = false;
            $matchStrategy = '';
            
            // Strategy 1: Exact domain match (most reliable)
            if (strcasecmp($urlHost, $domain) === 0) {
                $isDomainMatch = true;
                $matchStrategy = 'exact_host';
            }
            // Strategy 2: Domain is a subdomain
            elseif (preg_match("/\\.{$domain}$/i", $urlHost)) {
                $isDomainMatch = true;
                $matchStrategy = 'subdomain';
            }
            // Strategy 3: URL contains the domain (less reliable but catches more)
            elseif (stripos($url, $domain) !== false) {
                $isDomainMatch = true;
                $matchStrategy = 'url_contains';
            }

            if ($isDomainMatch) {
                // Extract the title - try different methods
                $title = '';
                $titleSelectors = [
                    './/h3',
                    './/h3[contains(@class, "LC20lb")]',
                    './/a//text()'
                ];
                
                foreach ($titleSelectors as $titleSelector) {
                    $titleNodes = $xpath->query($titleSelector, $result);
                    if ($titleNodes && $titleNodes->length > 0) {
                        $title = $titleNodes->item(0)->textContent;
                        break;
                    }
                }
                
                // If still no title, use domain as fallback
                if (empty(trim($title))) {
                    $title = $domain;
                }

                $positions[] = [
                    'position' => $position,
                    'page' => floor($start / 10) + 1,
                    'url' => $url,
                    'title' => $title,
                    'type' => 'organic', // Default to organic results
                    'is_target' => true,
                    'matched_domain' => $domain,
                    'match_strategy' => $matchStrategy
                ];
                
                Log::info("Found match at position {$position} for domain {$domain}: {$url} (Strategy: {$matchStrategy})");
            }

            $position++;
        }

        Log::info("Found " . count($positions) . " positions for domain {$domain} on page " . (floor($start / 10) + 1));
        return $positions;
    }

    /**
     * Extract the total number of search results
     */
    protected function extractTotalResults($html)
    {
        // Create DOM document
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Get the stats element
        $statsNode = $xpath->query('//div[@id="result-stats"]');

        if ($statsNode->length > 0) {
            $stats = $statsNode->item(0)->textContent;
            // Extract number from string like "About 1,570,000,000 results (0.35 seconds)"
            if (preg_match('/[\d,]+/', $stats, $matches)) {
                return (int) str_replace(',', '', $matches[0]);
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
