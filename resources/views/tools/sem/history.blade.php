@extends('layouts.app')

@section('content')
<div class="border-b pb-6 mb-6 mt-6">
    <div class="text-center mb-6">
        <h2 class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xl font-semibold text-gray-800">Arama Geçmişi</span>
        </h2>
    </div>

    <div class="bg-white border rounded-lg p-6 max-w-4xl mx-auto">
        <div class="flex items-center justify-between pb-4 border-b mb-4">
            <div class="flex items-center">
                <a href="{{ route('tools.sem.index') }}" class="mr-2 text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h3 class="text-lg font-semibold text-gray-800">Tüm Arama Geçmişi</h3>
            </div>

            @if(count($searchHistory) > 0)
            <form action="{{ route('tools.sem.clearHistory') }}" method="POST" onsubmit="return confirm('Tüm arama geçmişinizi silmek istediğinizden emin misiniz?');">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Geçmişi Temizle</button>
            </form>
            @endif
        </div>

        @if(count($searchHistory) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anahtar Kelime</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Website</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sonuç</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($searchHistory as $id => $search)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ date('d.m.Y H:i', $search['timestamp']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                                {{ $search['keyword'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $search['domain'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ !empty($search['results']['positions']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ !empty($search['results']['positions']) ? 'Bulundu' : 'Bulunamadı' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    <a href="{{ route('tools.sem.results', $id) }}" class="text-blue-600 hover:text-blue-800" title="Görüntüle">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('tools.sem.deleteSearch', $id) }}" method="POST" onsubmit="return confirm('Bu aramayı silmek istediğinizden emin misiniz?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Sil">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-12 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-600 mb-2">Henüz bir arama geçmişiniz bulunmuyor.</p>
                <a href="{{ route('tools.sem.index') }}" class="text-blue-600 hover:underline font-medium">Aramaya Başla</a>
            </div>
        @endif
    </div>
</div>
@endsection
