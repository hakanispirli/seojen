<div class="border-b pb-6 mb-6">
    <div class="text-center mb-6">
        <h2 class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-xl font-semibold text-gray-800">Teknik Seo Analizi</span>
        </h2>
    </div>

    <div class="bg-white border rounded-lg p-4">
        <!-- robots.txt Kontrolü -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">robots.txt Durumu</h3>
                <span class="px-2 py-1 text-xs font-medium rounded-full
                    {{ $results['technical_analysis']['robots_txt']['status'] === 'success' ? 'bg-green-100 text-green-800' :
                       ($results['technical_analysis']['robots_txt']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $results['technical_analysis']['robots_txt']['exists'] ? 'Mevcut' : 'Bulunamadı' }}
                </span>
            </div>

            @if($results['technical_analysis']['robots_txt']['exists'])
                <div class="bg-gray-50 p-3 rounded-lg mb-2">
                    <pre class="text-xs text-gray-600 whitespace-pre-wrap">{{ $results['technical_analysis']['robots_txt']['content'] }}</pre>
                </div>
                @if(!empty($results['technical_analysis']['robots_txt']['issues']))
                    <div class="mt-2">
                        <p class="text-sm font-medium text-red-600">Tespit Edilen Sorunlar:</p>
                        <ul class="mt-1 list-disc list-inside text-sm text-red-600">
                            @foreach($results['technical_analysis']['robots_txt']['issues'] as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>

        <!-- Sitemap Kontrolü -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">XML Sitemap Durumu</h3>
                <span class="px-2 py-1 text-xs font-medium rounded-full
                    {{ $results['technical_analysis']['sitemap']['status'] === 'success' ? 'bg-green-100 text-green-800' :
                       ($results['technical_analysis']['sitemap']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $results['technical_analysis']['sitemap']['exists'] ? 'Mevcut' : 'Bulunamadı' }}
                </span>
            </div>

            @if($results['technical_analysis']['sitemap']['exists'])
                <p class="text-sm text-blue-600 break-all mb-2">{{ $results['technical_analysis']['sitemap']['url'] }}</p>
                @if(!empty($results['technical_analysis']['sitemap']['issues']))
                    <div class="mt-2">
                        <p class="text-sm font-medium text-red-600">Tespit Edilen Sorunlar:</p>
                        <ul class="mt-1 list-disc list-inside text-sm text-red-600">
                            @foreach($results['technical_analysis']['sitemap']['issues'] as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>

        <!-- Canonical URL Kontrolü -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Canonical URL Durumu</h3>
                <span class="px-2 py-1 text-xs font-medium rounded-full
                    {{ $results['technical_analysis']['canonical']['status'] === 'success' ? 'bg-green-100 text-green-800' :
                       ($results['technical_analysis']['canonical']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $results['technical_analysis']['canonical']['exists'] ? 'Mevcut' : 'Bulunamadı' }}
                </span>
            </div>

            @if($results['technical_analysis']['canonical']['exists'])
                <p class="text-sm text-blue-600 break-all mb-2">{{ $results['technical_analysis']['canonical']['url'] }}</p>
                @if(!empty($results['technical_analysis']['canonical']['issues']))
                    <div class="mt-2">
                        <p class="text-sm font-medium text-red-600">Tespit Edilen Sorunlar:</p>
                        <ul class="mt-1 list-disc list-inside text-sm text-red-600">
                            @foreach($results['technical_analysis']['canonical']['issues'] as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>

        <!-- Schema Markup Kontrolü -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Schema Markup Durumu</h3>
                <span class="px-2 py-1 text-xs font-medium rounded-full
                    {{ $results['technical_analysis']['schema']['status'] === 'success' ? 'bg-green-100 text-green-800' :
                       ($results['technical_analysis']['schema']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $results['technical_analysis']['schema']['exists'] ? 'Mevcut' : 'Bulunamadı' }}
                </span>
            </div>

            @if($results['technical_analysis']['schema']['exists'])
                <div class="bg-gray-50 p-3 rounded-lg mb-2">
                    <p class="text-sm font-medium text-gray-600">Tespit Edilen Schema Tipleri:</p>
                    <ul class="mt-1 list-disc list-inside text-sm text-gray-600">
                        @foreach($results['technical_analysis']['schema']['types'] as $type)
                            <li>{{ $type }}</li>
                        @endforeach
                    </ul>
                </div>
                @if(!empty($results['technical_analysis']['schema']['issues']))
                    <div class="mt-2">
                        <p class="text-sm font-medium text-red-600">Tespit Edilen Sorunlar:</p>
                        <ul class="mt-1 list-disc list-inside text-sm text-red-600">
                            @foreach($results['technical_analysis']['schema']['issues'] as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>

        <!-- İyileştirme Önerileri -->
        @if(!empty($results['technical_analysis']['recommendations']))
            <div class="mb-6">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Teknik SEO İyileştirme Önerileri</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($results['technical_analysis']['recommendations'] as $recommendation)
                                        <li>{{ $recommendation }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white border rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Teknik Analiz Skoru</h3>
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="h-2 bg-gray-200 rounded-full">
                        <div class="h-2 rounded-full {{ $results['technical_analysis']['score'] >= 80 ? 'bg-green-500' : ($results['technical_analysis']['score'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $results['technical_analysis']['score'] }}%">
                        </div>
                    </div>
                </div>
                <span class="ml-4 text-sm font-medium {{ $results['technical_analysis']['score'] >= 80 ? 'text-green-600' : ($results['technical_analysis']['score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $results['technical_analysis']['score'] }}/100
                </span>
            </div>
        </div>
    </div>
</div>
