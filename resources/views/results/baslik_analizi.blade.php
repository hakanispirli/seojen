<!-- Başlık Analizi -->
<div class="border-b pb-6 mb-6">
    <div class="text-center mb-6">
        <h2 class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-xl font-semibold text-gray-800">Başlık Hiyerarşisi</span>
        </h2>
    </div>

    <!-- Başlık Sayıları -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        @for ($i = 1; $i <= 6; $i++)
            @php
                $count = $results['heading_analysis']['headings']['h'.$i]['count'] ?? 0;
                $status = match(true) {
                    $i === 1 && $count === 0 => 'error',
                    $i === 1 && $count > 1 => 'warning',
                    default => 'success'
                };
                $bgColor = match($status) {
                    'error' => 'bg-red-50',
                    'warning' => 'bg-yellow-50',
                    default => 'bg-gray-50'
                };
            @endphp
            <div class="{{ $bgColor }} p-4 rounded">
                <h3 class="text-sm font-medium text-gray-500">H{{ $i }} Başlıkları</h3>
                <p class="mt-1 text-lg font-semibold">{{ $count }}</p>
                @if($count > 0)
                    <div class="mt-2 text-xs text-gray-500">
                        @foreach($results['heading_analysis']['headings']['h'.$i]['content'] as $content)
                            <div class="truncate" title="{{ $content }}">{{ $content }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endfor
    </div>

    <!-- SEO Önerileri -->
    <div class="space-y-4">
        @if(!empty($results['heading_analysis']['structure']['issues']))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">İyileştirme Önerileri</h3>
                        <ul class="mt-2 text-sm text-yellow-700 list-disc list-inside space-y-1">
                            @foreach($results['heading_analysis']['structure']['issues'] as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- SEO İpuçları -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Başlık Hiyerarşisi İpuçları</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Her sayfada yalnızca bir adet H1 başlığı kullanın</li>
                            <li>H1 başlığı sayfanın ana konusunu temsil etmeli</li>
                            <li>Başlık hiyerarşisini sıralı kullanın (H2'den önce H3 kullanmayın)</li>
                            <li>Alt başlıkları (H2-H6) mantıklı bir şekilde gruplandırın</li>
                            <li>Başlıkları anahtar kelimelerle zenginleştirin</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skor Kartı -->
        <div class="bg-white border rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Başlık Hiyerarşisi Skoru</h3>
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="h-2 bg-gray-200 rounded-full">
                        <div class="h-2 rounded-full {{ $results['heading_analysis']['score'] >= 80 ? 'bg-green-500' : ($results['heading_analysis']['score'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $results['heading_analysis']['score'] }}%"></div>
                    </div>
                </div>
                <span class="ml-4 text-sm font-medium {{ $results['heading_analysis']['score'] >= 80 ? 'text-green-600' : ($results['heading_analysis']['score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $results['heading_analysis']['score'] }}/100
                </span>
            </div>
        </div>
    </div>
</div>
