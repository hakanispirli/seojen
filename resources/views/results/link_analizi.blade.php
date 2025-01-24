<!-- Link Analizi -->
<div class="border-b pb-8">
    <!-- Bölüm Başlığı -->
    <div class="text-center mb-6">
        <h2 class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            <span class="text-xl font-semibold text-gray-800">Bağlantı Analizi</span>
        </h2>
    </div>

    <div class="space-y-6">
        <!-- İç Bağlantılar Analizi -->
        <div class="bg-white border rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">İç Bağlantılar</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $results['link_analysis']['internal_links']['count'] >= 3 && $results['link_analysis']['internal_links']['count'] <= 100
                        ? 'bg-green-100 text-green-800'
                        : 'bg-yellow-100 text-yellow-800' }}">
                    {{ $results['link_analysis']['internal_links']['count'] }} bağlantı
                </span>
            </div>

            <!-- İç Bağlantı Listesi -->
            <div class="mt-2 space-y-2">
                @foreach($results['link_analysis']['internal_links']['links'] as $link)
                    <div class="text-sm text-gray-600 flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        <div>
                            <div class="font-medium">{{ $link['text'] ?: 'Metin yok' }}</div>
                            <div class="text-xs text-blue-600">{{ $link['url'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- İç Bağlantı Sorunları -->
            @if(!empty($results['link_analysis']['internal_links']['issues']))
                <div class="mt-3 bg-yellow-50 border-l-4 border-yellow-400 p-3">
                    <div class="text-sm text-yellow-700">
                        @foreach($results['link_analysis']['internal_links']['issues'] as $issue)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $issue }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Dış Bağlantılar Analizi -->
        <div class="bg-white border rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Dış Bağlantılar</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $results['link_analysis']['external_links']['count'] > 0 && $results['link_analysis']['external_links']['count'] <= 50
                        ? 'bg-green-100 text-green-800'
                        : 'bg-yellow-100 text-yellow-800' }}">
                    {{ $results['link_analysis']['external_links']['count'] }} bağlantı
                </span>
            </div>

            <!-- Dış Bağlantı Listesi -->
            <div class="mt-2 space-y-2">
                @foreach($results['link_analysis']['external_links']['links'] as $link)
                    <div class="text-sm text-gray-600 flex items-start">
                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <div>
                            <div class="font-medium">{{ $link['text'] ?: 'Metin yok' }}</div>
                            <div class="text-xs text-blue-600">{{ $link['url'] }}</div>
                            <div class="text-xs text-gray-500">{{ $link['rel'] ? 'rel="'.$link['rel'].'"' : 'rel attribute yok' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Dış Bağlantı Sorunları -->
            @if(!empty($results['link_analysis']['external_links']['issues']))
                <div class="mt-3 bg-yellow-50 border-l-4 border-yellow-400 p-3">
                    <div class="text-sm text-yellow-700">
                        @foreach($results['link_analysis']['external_links']['issues'] as $issue)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $issue }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Kırık Bağlantılar -->
        <div class="bg-white border rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Kırık Bağlantılar</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $results['link_analysis']['broken_links']['broken_count'] === 0
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800' }}">
                    {{ $results['link_analysis']['broken_links']['broken_count'] }} kırık bağlantı
                </span>
            </div>

            @if($results['link_analysis']['broken_links']['broken_count'] > 0)
                <div class="mt-2 space-y-2">
                    @foreach($results['link_analysis']['broken_links']['links'] as $link)
                        <div class="text-sm text-red-600 flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <div>{{ $link['url'] }}</div>
                                <div class="text-xs text-red-500">
                                    {{ isset($link['status']) ? 'HTTP ' . $link['status'] : $link['error'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-green-600">Kırık bağlantı tespit edilmedi.</div>
            @endif
        </div>

        <!-- Anchor Text Analizi -->
        <div class="bg-white border rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Anchor Text Analizi</h3>
            </div>

            @if(!empty($results['link_analysis']['anchor_texts']['issues']))
                <div class="mt-2 bg-yellow-50 border-l-4 border-yellow-400 p-3">
                    <div class="text-sm text-yellow-700">
                        @foreach($results['link_analysis']['anchor_texts']['issues'] as $issue)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $issue }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Follow/Nofollow Durumu -->
        <div class="bg-white border rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Follow/Nofollow Durumu</h3>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">
                        {{ $results['link_analysis']['follow_status']['status']['follow'] }}
                    </div>
                    <div class="text-sm text-green-600">Follow Bağlantılar</div>
                </div>
                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600">
                        {{ $results['link_analysis']['follow_status']['status']['nofollow'] }}
                    </div>
                    <div class="text-sm text-yellow-600">Nofollow Bağlantılar</div>
                </div>
            </div>
        </div>

        <!-- SEO İpuçları -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Bağlantı İpuçları</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>İç bağlantılar için açıklayıcı anchor text kullanın</li>
                            <li>Dış bağlantılarda rel="noopener noreferrer" kullanın</li>
                            <li>Kırık bağlantıları düzenli olarak kontrol edin</li>
                            <li>"Buraya tıklayın" gibi genel ifadelerden kaçının</li>
                            <li>Önemli sayfalara dofollow bağlantılar verin</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skor Kartı -->
        <div class="bg-white border rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Bağlantı Analizi Skoru</h3>
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="h-2 bg-gray-200 rounded-full">
                        <div class="h-2 rounded-full {{ $results['link_analysis']['score'] >= 80 ? 'bg-green-500' : ($results['link_analysis']['score'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $results['link_analysis']['score'] }}%"></div>
                    </div>
                </div>
                <span class="ml-4 text-sm font-medium {{ $results['link_analysis']['score'] >= 80 ? 'text-green-600' : ($results['link_analysis']['score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $results['link_analysis']['score'] }}/100
                </span>
            </div>
        </div>
    </div>
</div>
