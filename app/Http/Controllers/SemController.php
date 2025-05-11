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
    protected $searchEngines = [
        'google' => [
            'url' => 'https://www.google.com/search',
            'resultsPerPage' => 10,
            'maxPages' => 5, // Limit to 5 pages to avoid CAPTCHA
        ],
        'bing' => [
            'url' => 'https://www.bing.com/search',
            'resultsPerPage' => 10,
            'maxPages' => 10,
        ]
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
            $pages = min($request->input('pages', 5), 5); // Limit to 5 pages max to prevent CAPTCHA
            $checkAllPages = $request->has('check_all_pages');
            
            // Try search engines in order until one succeeds
            $results = $this->searchWithFallback($request->input('keyword'), $domain, $pages, $checkAllPages);

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
     * Try multiple search engines with fallback
     */
    protected function searchWithFallback($keyword, $domain, $pages, $checkAllPages)
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
            'search_engine' => '',
        ];

        // First try with Google
        try {
            $googleResults = $this->processSingleEngineSearch('google', $keyword, $domain, $pages, $checkAllPages);
            
            if ($googleResults['found']) {
                $result = array_merge($result, $googleResults);
                $result['search_engine'] = 'Google';
                Log::info("Search successful with Google");
            } else {
                // Then try with Bing if Google failed or found no results
                Log::info("Google search found no results, trying Bing");
                $bingResults = $this->processSingleEngineSearch('bing', $keyword, $domain, $pages, $checkAllPages);
                
                if ($bingResults['found']) {
                    $result = array_merge($result, $bingResults);
                    $result['search_engine'] = 'Bing';
                    Log::info("Search successful with Bing");
                } else {
                    // If both engines failed, use any results we got
                    $result = array_merge($result, 
                        $googleResults['total_results'] > $bingResults['total_results'] 
                            ? $googleResults 
                            : $bingResults
                    );
                    $result['search_engine'] = $googleResults['total_results'] > $bingResults['total_results'] 
                        ? 'Google' 
                        : 'Bing';
                }
            }
        } catch (Exception $e) {
            // If Google throws error, try Bing
            Log::warning("Google search error: " . $e->getMessage() . ". Trying Bing");
            
            try {
                $bingResults = $this->processSingleEngineSearch('bing', $keyword, $domain, $pages, $checkAllPages);
                $result = array_merge($result, $bingResults);
                $result['search_engine'] = 'Bing';
            } catch (Exception $e2) {
                Log::error("Both search engines failed: " . $e2->getMessage());
                throw new Exception("All search engines failed: " . $e2->getMessage());
            }
        }

        // Calculate search time
        $result['search_time'] = round(microtime(true) - $startTime, 2);
        return $result;
    }

    /**
     * Process search with a single engine
     */
    protected function processSingleEngineSearch($engine, $keyword, $domain, $pages, $checkAllPages)
    {
        if (!isset($this->searchEngines[$engine])) {
            throw new Exception("Unknown search engine: $engine");
        }
        
        $config = $this->searchEngines[$engine];
        $pages = min($pages, $config['maxPages']);
        
        $result = [
            'positions' => [],
            'found' => false,
            'total_results' => 0,
        ];
        
        // Generate search queries for better domain detection
        $searchQueries = $this->generateSearchQueries($engine, $keyword, $domain);
        $resultsPerPage = $config['resultsPerPage'];
        
        foreach ($searchQueries as $searchQuery) {
            Log::info("Trying $engine search with query: $searchQuery");
            
            for ($page = 0; $page < $pages; $page++) {
                // Progressive delay to avoid CAPTCHA
                $delay = ($page + 1) * rand(2, 3);
                if ($page > 0) {
                    Log::info("Waiting $delay seconds before next page request");
                    sleep($delay);
                }
                
                $start = $page * $resultsPerPage;
                
                try {
                    $html = $this->fetchSearchResults($engine, $searchQuery, $start);
                    
                    if (!$html) {
                        Log::warning("No results from $engine for query: $searchQuery (page " . ($page+1) . ")");
                        continue;
                    }
                    
                    $positions = $this->parseSearchResults($engine, $html, $domain, $start);
                    
                    if (!empty($positions)) {
                        $result['positions'] = array_merge($result['positions'], $positions);
                        $result['found'] = true;
                        
                        // Extract total results count on first page
                        if ($page === 0 && $result['total_results'] === 0) {
                            $result['total_results'] = $this->extractTotalResults($engine, $html);
                        }
                        
                        if (!$checkAllPages) {
                            break; // Stop searching pages if we found results and aren't checking all
                        }
                    } else if ($page === 0) {
                        // If first page has no results but we can extract total count
                        $result['total_results'] = $this->extractTotalResults($engine, $html);
                    }
                } catch (Exception $e) {
                    // Log issue and try next page
                    Log::warning("Error searching $engine (page " . ($page+1) . "): " . $e->getMessage());
                    
                    // If we hit CAPTCHA or serious error, stop trying more pages
                    if (stripos($e->getMessage(), 'captcha') !== false) {
                        Log::error("CAPTCHA detected. Stopping search.");
                        break;
                    }
                }
            }
            
            // If we found positions with this query, stop trying other queries
            if ($result['found']) {
                break;
            }
        }
        
        // Sort positions by position number
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
        
        return $result;
    }
    
    /**
     * Generate effective search queries
     */
    protected function generateSearchQueries($engine, $keyword, $domain)
    {
        $queries = [$keyword]; // Basic keyword search
        
        // Check if keyword contains the domain or is a domain-like structure
        $isDomainSearch = (stripos($keyword, $domain) !== false || filter_var($keyword, FILTER_VALIDATE_DOMAIN));
        
        if ($isDomainSearch) {
            // For domain searches, add specialized queries
            if ($engine === 'google') {
                $queries[] = "site:$domain"; // Google site: operator
                $queries[] = "\"$domain\""; // Exact match
            } else if ($engine === 'bing') {
                $queries[] = "site:$domain"; // Bing also supports site:
                $queries[] = "domain:$domain"; // Bing domain: operator
            }
        } else {
            // For regular keyword searches, add domain-targeted queries
            $queries[] = "$keyword site:$domain";
            
            // Check if keyword is likely a brand name or specific term
            if (strlen($keyword) > 4 && !preg_match('/\s/', $keyword)) {
                $queries[] = "\"$keyword\" $domain"; // Exact match keyword + domain
            }
        }
        
        return $queries;
    }

    /**
     * Fetch search results from a search engine
     */
    protected function fetchSearchResults($engine, $query, $start = 0)
    {
        if (!isset($this->searchEngines[$engine])) {
            throw new Exception("Unknown search engine: $engine");
        }
        
        $config = $this->searchEngines[$engine];
        $userAgent = $this->userAgents[array_rand($this->userAgents)];
        $params = [];
        
        // Configure search parameters based on engine
        if ($engine === 'google') {
            $params = [
                'q' => $query,
                'start' => $start,
                'num' => 10,
                'hl' => 'en',
                'gl' => 'us',
            ];
        } else if ($engine === 'bing') {
            $params = [
                'q' => $query,
                'first' => $start + 1, // Bing uses 1-based indexing
                'count' => 10,
                'setlang' => 'en',
            ];
        }
        
        try {
            Log::info("Fetching $engine search results for: '$query' (start: $start)");
            
            $response = Http::withHeaders([
                'User-Agent' => $userAgent,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Referer' => $engine === 'google' ? 'https://www.google.com/' : 'https://www.bing.com/',
            ])->timeout(10)->get($config['url'], $params);
            
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
            
            Log::error("$engine search error: " . $response->status());
            return null;
        } catch (Exception $e) {
            // Trim any response body from exception message to avoid polluting logs
            $message = $e->getMessage();
            if (strlen($message) > 150) {
                $message = substr($message, 0, 150) . '...';
            }
            
            Log::error("$engine search exception: $message");
            throw $e;
        }
    }

    /**
     * Parse the search results HTML
     */
    protected function parseSearchResults($engine, $html, $domain, $start = 0)
    {
        $positions = [];
        $domParser = new DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $domParser->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($domParser);
        $position = $start + 1;
        
        // Select appropriate parsing strategy based on search engine
        if ($engine === 'google') {
            $positions = $this->parseGoogleResults($xpath, $domain, $position);
        } else if ($engine === 'bing') {
            $positions = $this->parseBingResults($xpath, $domain, $position);
        }
        
        if (count($positions) > 0) {
            Log::info("Found " . count($positions) . " matching results for domain $domain");
        }
        
        return $positions;
    }
    
    /**
     * Parse Google search results
     */
    protected function parseGoogleResults($xpath, $domain, $position)
    {
        $positions = [];
        
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
     * Parse Bing search results
     */
    protected function parseBingResults($xpath, $domain, $position)
    {
        $positions = [];
        
        // Bing result containers
        $results = $xpath->query('//li[@class="b_algo"]');
        
        if (!$results || $results->length === 0) {
            return $positions;
        }
        
        foreach ($results as $result) {
            // Extract URL
            $links = $xpath->query('.//h2/a', $result);
            if (!$links || $links->length === 0) {
                $position++;
                continue;
            }
            
            $link = $links->item(0);
            $url = $link->getAttribute('href');
            
            // Check if this URL matches our domain
            if ($this->isDomainMatch($url, $domain)) {
                // Extract title
                $title = $link->textContent;
                
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
    protected function extractTotalResults($engine, $html)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        if ($engine === 'google') {
            // Google result stats
            $statsNode = $xpath->query('//div[@id="result-stats"]');
            
            if ($statsNode && $statsNode->length > 0) {
                $stats = $statsNode->item(0)->textContent;
                // Extract number from string like "About 1,570,000,000 results (0.35 seconds)"
                if (preg_match('/[\d,\.]+/', $stats, $matches)) {
                    return (int) str_replace([',', '.'], '', $matches[0]);
                }
            }
        } else if ($engine === 'bing') {
            // Bing result count
            $countNode = $xpath->query('//span[@class="sb_count"]');
            
            if ($countNode && $countNode->length > 0) {
                $count = $countNode->item(0)->textContent;
                // Extract number from string like "1,570,000 results"
                if (preg_match('/[\d,\.]+/', $count, $matches)) {
                    return (int) str_replace([',', '.'], '', $matches[0]);
                }
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
