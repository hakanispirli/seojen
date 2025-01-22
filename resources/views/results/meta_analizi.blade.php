<!-- Meta Analizi -->
<div class="border-b pb-8">
    <!-- Bölüm Başlığı -->
    <div class="text-center mb-6">
        <h2 class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-xl font-semibold text-gray-800">Meta Etiketleri</span>
        </h2>
    </div>

    <!-- Title Analizi -->
    <div class="space-y-6">
        <div class="bg-white border rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Başlık (Title) Etiketi</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $results['meta_analysis']['title']['status'] === 'success' ? 'bg-green-100 text-green-800' :
                       ($results['meta_analysis']['title']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $results['meta_analysis']['title']['length'] ?? 0 }} karakter
                </span>
            </div>

            <!-- Title Preview -->
            <div class="mb-4">
                <div class="text-blue-800 text-base hover:underline cursor-pointer truncate">
                    {{ $results['meta_analysis']['title']['content'] ?? 'Başlık bulunamadı' }}
                </div>
                <div class="text-green-800 text-xs truncate">
                    {{ $results['url'] }}
                </div>
            </div>

            <!-- Title Değerlendirmesi -->
            @php
                $titleLength = $results['meta_analysis']['title']['length'] ?? 0;
                $titleStatus = match(true) {
                    $titleLength === 0 => [
                        'icon' => 'error',
                        'color' => 'red',
                        'message' => 'Title etiketi bulunamadı! SEO açısından kritik bir eksiklik.'
                    ],
                    $titleLength < 30 => [
                        'icon' => 'warning',
                        'color' => 'yellow',
                        'message' => 'Title etiketi çok kısa. En az 30 karakter kullanmanız önerilir.'
                    ],
                    $titleLength > 60 => [
                        'icon' => 'warning',
                        'color' => 'yellow',
                        'message' => 'Title etiketi çok uzun. 60 karakteri geçmemeniz önerilir.'
                    ],
                    default => [
                        'icon' => 'success',
                        'color' => 'green',
                        'message' => 'Title etiketi ideal uzunlukta!'
                    ]
                };
            @endphp

            <div class="flex items-start">
                @if($titleStatus['icon'] === 'error')
                    <svg class="h-5 w-5 text-red-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                @elseif($titleStatus['icon'] === 'warning')
                    <svg class="h-5 w-5 text-yellow-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                @else
                    <svg class="h-5 w-5 text-green-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                @endif
                <p class="ml-2 text-sm text-{{ $titleStatus['color'] }}-700">
                    {{ $titleStatus['message'] }}
                </p>
            </div>
        </div>

        <!-- Description Analizi -->
        <div class="bg-white border rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Açıklama (Description) Etiketi</h3>
                @if(isset($results['meta_analysis']['meta_tags']['description']))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $results['meta_analysis']['meta_tags']['description']['status'] === 'success' ? 'bg-green-100 text-green-800' :
                           ($results['meta_analysis']['meta_tags']['description']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $results['meta_analysis']['meta_tags']['description']['length'] }} karakter
                    </span>
                @endif
            </div>

            <!-- Description Preview -->
            <div class="mb-4">
                <div class="text-sm text-gray-800 line-clamp-2">
                    {{ $results['meta_analysis']['meta_tags']['description']['content'] ?? 'Description etiketi bulunamadı' }}
                </div>
            </div>

            <!-- Description Değerlendirmesi -->
            @php
                $descLength = $results['meta_analysis']['meta_tags']['description']['length'] ?? 0;
                $descStatus = match(true) {
                    !isset($results['meta_analysis']['meta_tags']['description']) => [
                        'icon' => 'error',
                        'color' => 'red',
                        'message' => 'Description etiketi bulunamadı! Arama sonuçlarında görünürlük için önemli.'
                    ],
                    $descLength < 120 => [
                        'icon' => 'warning',
                        'color' => 'yellow',
                        'message' => 'Description etiketi çok kısa. 120-160 karakter arası kullanmanız önerilir.'
                    ],
                    $descLength > 160 => [
                        'icon' => 'warning',
                        'color' => 'yellow',
                        'message' => 'Description etiketi çok uzun. 160 karakteri geçmemeniz önerilir.'
                    ],
                    default => [
                        'icon' => 'success',
                        'color' => 'green',
                        'message' => 'Description etiketi ideal uzunlukta!'
                    ]
                };
            @endphp

            <div class="flex items-start">
                @if($descStatus['icon'] === 'error')
                    <svg class="h-5 w-5 text-red-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                @elseif($descStatus['icon'] === 'warning')
                    <svg class="h-5 w-5 text-yellow-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                @else
                    <svg class="h-5 w-5 text-green-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                @endif
                <p class="ml-2 text-sm text-{{ $descStatus['color'] }}-700">
                    {{ $descStatus['message'] }}
                </p>
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
                    <h3 class="text-sm font-medium text-blue-800">Meta Etiketleri İpuçları</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Title etiketi 30-60 karakter arasında olmalıdır</li>
                            <li>Description etiketi 120-160 karakter arasında olmalıdır</li>
                            <li>Title etiketinde ana anahtar kelimenizi kullanın</li>
                            <li>Description etiketi kullanıcıyı sayfaya çekecek şekilde yazılmalıdır</li>
                            <li>Her sayfa için benzersiz title ve description kullanın</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skor Kartı -->
        <div class="bg-white border rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Meta Etiketleri Skoru</h3>
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="h-2 bg-gray-200 rounded-full">
                        <div class="h-2 rounded-full {{ $results['meta_analysis']['score'] >= 80 ? 'bg-green-500' : ($results['meta_analysis']['score'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $results['meta_analysis']['score'] }}%"></div>
                    </div>
                </div>
                <span class="ml-4 text-sm font-medium {{ $results['meta_analysis']['score'] >= 80 ? 'text-green-600' : ($results['meta_analysis']['score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $results['meta_analysis']['score'] }}/100
                </span>
            </div>
        </div>
    </div>
</div>
