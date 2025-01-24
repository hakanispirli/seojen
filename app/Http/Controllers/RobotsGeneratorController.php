<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RobotsGeneratorController extends Controller
{
    public function index()
    {
        return view('tools.robots.index');
    }

    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_agents' => 'required|array|min:1',
            'user_agents.*' => 'required|string|min:1',
            'rules' => 'nullable|array',
            'rules.*.type' => 'required_with:rules|in:allow,disallow',
            'rules.*.path' => 'required_with:rules|string|min:1',
            'sitemaps' => 'nullable|array',
            'sitemaps.*' => 'nullable|url',
            'crawl_delay' => 'nullable|integer|min:0|max:60'
        ], [
            'user_agents.required' => 'En az bir User Agent belirtmelisiniz.',
            'user_agents.min' => 'En az bir User Agent belirtmelisiniz.',
            'user_agents.*.required' => 'User Agent alanı boş bırakılamaz.',
            'rules.*.type.in' => 'Geçersiz kural tipi.',
            'rules.*.path.min' => 'Kural yolu boş olamaz.',
            'sitemaps.*.url' => 'Geçerli bir sitemap URL\'i giriniz.',
            'crawl_delay.integer' => 'Crawl delay sayısal olmalıdır.',
            'crawl_delay.min' => 'Crawl delay 0\'dan küçük olamaz.',
            'crawl_delay.max' => 'Crawl delay 60\'dan büyük olamaz.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $content = $this->generateRobotsContent(
            $request->user_agents,
            $request->rules ?? [],
            $request->sitemaps ?? [],
            $request->crawl_delay
        );

        return response()->json([
            'success' => true,
            'content' => $content
        ]);
    }

    private function generateRobotsContent(array $userAgents, array $rules, array $sitemaps, ?int $crawlDelay): string
    {
        $content = [];

        foreach ($userAgents as $agent) {
            $content[] = "User-agent: $agent";

            if ($crawlDelay !== null) {
                $content[] = "Crawl-delay: $crawlDelay";
            }

            foreach ($rules as $rule) {
                $directive = ucfirst($rule['type']);
                $path = $rule['path'];
                $content[] = "$directive: $path";
            }

            $content[] = "";
        }

        foreach ($sitemaps as $sitemap) {
            $content[] = "Sitemap: $sitemap";
        }

        return implode("\n", $content);
    }

    public function download(Request $request)
    {
        $content = $request->input('content');

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="robots.txt"');
    }
}
