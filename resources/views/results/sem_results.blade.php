@extends('layouts.app')
@section('content')
<div class="border-b pb-6 mb-6 mt-6">
    <div class="text-center mb-6">
        <h2 class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span class="text-xl font-semibold text-gray-800">SEM Arama Sonuçları</span>
        </h2>
    </div>
    <div class="bg-white border rounded-lg p-6 max-w-4xl mx-auto">
        <div class="flex items-center justify-between pb-4 border-b mb-4">
            <div class="flex items-center">
                <a href="{{ route('tools.sem.history') }}" class="mr-2 text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h3 class="text-lg font-semibold text-gray-800">Arama Detayları</h3>
            </div>
            <div class="text-sm text-gray-500">
                {{ date('d.m.Y H:i', $searchResult['timestamp']) }}
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Arama Bilgileri</h4>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm text-gray-500">Anahtar Kelime:</span>
                        <span class="text-sm font-medium text-gray-800 ml-1">{{ $searchResult['keyword'] }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Website:</span>
                        <span class="text-sm font-medium text-gray-800 ml-1">{{ $searchResult['website'] }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Domain:</span>
                        <span class="text-sm font-medium text-gray-800 ml-1">{{ $searchResult['domain'] }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Kontrol Edilen Sayfa:</span>
                        <span class="text-sm font-medium text-gray-800 ml-1">{{ $searchResult['pages_checked'] }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Sonuç Özeti</h4>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm text-gray-500">Durum:</span>
                        <span class="ml-1 px-2 py-0.5 text-xs font-medium rounded-full {{ !empty($searchResult['results']['positions']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ !empty($searchResult['results']['positions']) ? 'Bulundu' : 'Bulunamadı' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Google'da Toplam Sonuç:</span>
                        <span class="text-sm font-medium text-gray-800 ml-1">{{ number_format($searchResult['results']['total_results'], 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Arama Zamanı:</span>
                        <span class="text-sm font-medium text-gray-800 ml-1">{{ $searchResult['results']['search_time'] ?? '-' }} saniye</span>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($searchResult['results']['positions']))
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Pozisyon Bilgileri</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr>
                                <th class="py-2 px-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sıra</th>
                                <th class="py-2 px-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                <th class="py-2 px-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başlık</th>
                                <th class="py-2 px-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tip</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($searchResult['results']['positions'] as $position)
                                <tr class="{{ $position['is_target'] ?? false ? 'bg-blue-50' : '' }}">
                                    <td class="py-2 px-3 text-sm {{ $position['is_target'] ?? false ? 'font-bold text-blue-600' : 'text-gray-700' }}">{{ $position['position'] }}</td>
                                    <td class="py-2 px-3 text-sm text-gray-700">
                                        <div class="flex items-center">
                                            @if($position['is_target'] ?? false)
                                                <svg class="w-4 h-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                            <a href="{{ $position['url'] }}" target="_blank" class="text-blue-600 hover:text-blue-800 truncate max-w-xs">
                                                {{ $position['url'] }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3 text-sm text-gray-700 truncate max-w-xs">{{ $position['title'] }}</td>
                                    <td class="py-2 px-3 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ isset($position['type']) ? ($position['type'] == 'organic' ? 'bg-green-100 text-green-800' :
                                          ($position['type'] == 'paid' ? 'bg-purple-100 text-purple-800' :
                                          'bg-gray-100 text-gray-800')) : 'bg-green-100 text-green-800' }}">
                                            {{ isset($position['type']) ? ucfirst($position['type']) : 'Organic' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Belirtilen domain için bu anahtar kelimede herhangi bir sonuç bulunamadı. Anahtar kelimenizi kontrol edip tekrar deneyebilirsiniz.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($searchResult['results']['suggested_keywords']))
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Önerilen Anahtar Kelimeler</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($searchResult['results']['suggested_keywords'] as $keyword)
                        <a href="{{ route('tools.sem.search', ['keyword' => $keyword]) }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                            {{ $keyword }}
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="border-t pt-4 mt-6">
            <div class="flex justify-between">
                <a href="{{ route('tools.sem.history') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Arama Geçmişine Dön
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Any additional JavaScript if needed
});
</script>
@endsection
