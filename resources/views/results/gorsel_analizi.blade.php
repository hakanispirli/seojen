<!-- Görsel Analizi -->
<div class="border-b pb-6 mb-6">
    <div class="text-center mb-6">
        <h2 class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-xl font-semibold text-gray-800">Görsel Analizi</span>
        </h2>
    </div>

    <!-- Genel İstatistikler -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-50 p-4 rounded">
            <h3 class="text-sm font-medium text-gray-500">Toplam Görsel</h3>
            <p class="mt-1 text-lg font-semibold">{{ $results['image_analysis']['statistics']['total'] ?? 0 }}</p>
        </div>
        <div class="bg-gray-50 p-4 rounded">
            <h3 class="text-sm font-medium text-gray-500">Alt Etiketi Olan</h3>
            <p class="mt-1 text-lg font-semibold text-green-600">{{ $results['image_analysis']['statistics']['with_alt'] ?? 0 }}</p>
        </div>
        <div class="bg-gray-50 p-4 rounded">
            <h3 class="text-sm font-medium text-gray-500">Alt Etiketi Boş</h3>
            <p class="mt-1 text-lg font-semibold text-yellow-600">{{ $results['image_analysis']['statistics']['with_empty_alt'] ?? 0 }}</p>
        </div>
        <div class="bg-gray-50 p-4 rounded">
            <h3 class="text-sm font-medium text-gray-500">Alt Etiketi Eksik</h3>
            <p class="mt-1 text-lg font-semibold text-red-600">{{ $results['image_analysis']['statistics']['without_alt'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Özet Analiz -->
    @if(!empty($results['image_analysis']['images']))
        <div class="bg-white border rounded-lg p-4 mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Görsel Raporu</h3>
            <div class="space-y-4">
                @php
                    $largeImages = collect($results['image_analysis']['images'])
                        ->filter(function($image) {
                            return isset($image['size']) && $image['size'] > 102400;
                        })
                        ->values();

                    $problematicImages = collect($results['image_analysis']['images'])
                        ->filter(function($image) {
                            return !$image['has_alt'] || (strlen($image['alt'] ?? '') === 0);
                        })
                        ->values();
                @endphp

                <!-- Boyut Uyarısı -->
                @if($largeImages->count() > 0)
                    <div class="space-y-2">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-2">
                                <p class="text-sm text-yellow-700">
                                    {{ $largeImages->count() }} adet görsel 100KB'dan büyük. Sayfa performansı için optimize edilmeli.
                                </p>
                                <div class="mt-1 text-xs text-gray-500 space-y-1">
                                    @foreach($largeImages as $image)
                                        <div class="flex items-center">
                                            <span class="inline-block w-14 text-right mr-2">
                                                {{ number_format($image['size'] / 1024, 1) }}KB
                                            </span>
                                            <span class="truncate" title="{{ $image['src'] }}">
                                                {{ basename($image['src']) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Alt Etiketi Uyarısı -->
                @if($problematicImages->count() > 0)
                    <div class="space-y-2">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-2">
                                <p class="text-sm text-red-700">
                                    {{ $problematicImages->count() }} adet görsel alt etiketi eksik veya boş. SEO için düzeltilmeli.
                                </p>
                                <div class="mt-1 text-xs text-gray-500 space-y-1">
                                    @foreach($problematicImages as $image)
                                        <div class="flex items-center">
                                            <span class="inline-block w-14 text-right mr-2">
                                                {{ !$image['has_alt'] ? 'Eksik' : 'Boş' }}
                                            </span>
                                            <span class="truncate" title="{{ $image['src'] }}">
                                                {{ basename($image['src']) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- SEO İpuçları -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3"></div>
                <h3 class="text-sm font-medium text-blue-800">Görsel Optimizasyonu İpuçları</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Her görsele açıklayıcı bir alt etiketi ekleyin</li>
                        <li>Görsel dosya boyutlarını 100KB altında tutmaya çalışın</li>
                        <li>Görselleri doğru formatta kullanın (JPEG, PNG, WebP)</li>
                        <li>Görsel dosya isimlerini SEO dostu yapın</li>
                        <li>Görselleri lazy loading ile yükleyin</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Skor Kartı -->
    <div class="bg-white border rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Görsel Optimizasyonu Skoru</h3>
        <div class="flex items-center">
            <div class="flex-1">
                <div class="h-2 bg-gray-200 rounded-full">
                    <div class="h-2 rounded-full {{ $results['image_analysis']['score'] >= 80 ? 'bg-green-500' : ($results['image_analysis']['score'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                         style="width: {{ $results['image_analysis']['score'] }}%"></div>
                </div>
            </div>
            <span class="ml-4 text-sm font-medium {{ $results['image_analysis']['score'] >= 80 ? 'text-green-600' : ($results['image_analysis']['score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $results['image_analysis']['score'] }}/100
            </span>
        </div>
    </div>
</div>
