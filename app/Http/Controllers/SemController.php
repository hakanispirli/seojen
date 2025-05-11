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
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Extract domain from website URL
        $website = $request->input('website');
        $domain = parse_url($website, PHP_URL_HOST);
        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid website URL format'
            ], 422);
        }

        // Remove www. if present
        $domain = preg_replace('/^www\./', '', $domain);

        // Process the search
        try {
            $pages = $request->input('pages', $this->maxPages);
            $checkAllPages = $request->input('check_all_pages', false);
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

            return response()->json([
                'success' => true,
                'data' => $results,
                'search_id' => $searchId
            ]);
        } catch (Exception $e) {
            Log::error('SEM search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your search: ' . $e->getMessage()
            ], 500);
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
        ];

        $resultsPerPage = 10;
        $searchDelay = rand(1, 3); // Random delay between searches to avoid blocking

        for ($page = 0; $page < $pages; $page++) {
            // Wait a bit before each request (except the first one)
            if ($page > 0) {
                sleep($searchDelay);
            }

            $start = $page * $resultsPerPage;
            $html = $this->fetchSearchResults($keyword, $start);

            if (!$html) {
                continue;
            }

            $positions = $this->parseSearchResults($html, $domain, $start);

            // Add found positions to the result
            if (!empty($positions)) {
                $result['positions'] = array_merge($result['positions'], $positions);
                $result['found'] = true;
            }

            // Extract total results count (only need to do this once)
            if ($page === 0) {
                $result['total_results'] = $this->extractTotalResults($html);
            }

            // If we've found positions and not checking all pages, we can stop searching
            if (!empty($positions) && !$checkAllPages) {
                break;
            }
        }

        return $result;
    }

    /**
     * Fetch search results from Google
     */
    protected function fetchSearchResults($keyword, $start = 0)
    {
        // Random user agent to avoid blocking
        $userAgent = $this->userAgents[array_rand($this->userAgents)];

        $params = [
            'q' => $keyword,
            'start' => $start,
            'num' => 10,
            'hl' => 'en',
            'gl' => 'us',
        ];

        try {
            $response = Http::withHeaders([
                'User-Agent' => $userAgent,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            ])->timeout(10)->get($this->searchBaseUrl, $params);

            if ($response->successful()) {
                return $response->body();
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

        // Find all search results (this selector may need adjusting based on Google's current HTML structure)
        $searchResults = $xpath->query('//div[@class="g"]');

        $position = $start + 1;

        foreach ($searchResults as $result) {
            // Extract URL from the result
            $linkNodes = $xpath->query('.//a[@href]', $result);

            if ($linkNodes->length > 0) {
                // Get the URL from the first link
                $url = $linkNodes->item(0)->getAttribute('href');

                // If URL is a Google redirect, extract the actual URL
                if (strpos($url, '/url?q=') !== false) {
                    $url = preg_replace('/^.*?\/url\?q=([^&]+)&.*$/', '$1', $url);
                    $url = urldecode($url);
                }

                // Check if the URL contains our domain
                if (strpos($url, $domain) !== false) {
                    // Extract the title
                    $titleNodes = $xpath->query('.//h3', $result);
                    $title = $titleNodes->length > 0 ? $titleNodes->item(0)->textContent : '';

                    $positions[] = [
                        'position' => $position,
                        'page' => floor($start / 10) + 1,
                        'url' => $url,
                        'title' => $title,
                    ];
                }
            }

            $position++;
        }

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
            return redirect()->route('tools.tools.sem.index')->with('error', 'Search not found');
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
