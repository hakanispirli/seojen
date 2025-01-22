<div class="border-b pb-6 mb-6">
    <div class="text-center mb-6">
        <h2 class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-xl font-semibold text-gray-800">URL Analizi</span>
        </h2>
    </div>

    <div class="bg-white border rounded-lg p-4">
        <!-- Mevcut URL -->
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Mevcut URL</h3>
            <div class="bg-gray-50 p-3 rounded-lg">
                <p class="text-sm text-blue-600 break-all">{{ $results['url'] }}</p>
            </div>
        </div>

        <!-- Tespit Edilen Sorunlar -->
        @if(!empty($results['url_analysis']['problematic_urls']))
            <div class="mb-6">
                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Tespit Edilen URL Sorunları</h3>
                            <div class="mt-2 space-y-4">
                                @foreach($results['url_analysis']['problematic_urls'] as $problematicUrl)
                                    <div class="text-sm">
                                        <p class="text-red-700 font-medium break-all">{{ $problematicUrl['url'] }}</p>
                                        <ul class="mt-1 list-disc list-inside text-red-600 ml-4">
                                            @foreach($problematicUrl['issues'] as $issue)
                                                <li>{{ $issue }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- İyileştirme Önerileri -->
        @if(!empty($results['url_analysis']['recommendations']))
            <div class="mb-6">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">URL İyileştirme Önerileri</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($results['url_analysis']['recommendations'] as $recommendation)
                                        <li>{{ $recommendation }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Skor Kartı -->
        <div class="bg-white border rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">URL Optimizasyonu Skoru</h3>
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="h-2 bg-gray-200 rounded-full">
                        <div class="h-2 rounded-full {{ $results['url_analysis']['score'] >= 80 ? 'bg-green-500' : ($results['url_analysis']['score'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $results['url_analysis']['score'] }}%">
                        </div>
                    </div>
                </div>
                <span class="ml-4 text-sm font-medium {{ $results['url_analysis']['score'] >= 80 ? 'text-green-600' : ($results['url_analysis']['score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $results['url_analysis']['score'] }}/100
                </span>
            </div>
        </div>
    </div>
</div>
