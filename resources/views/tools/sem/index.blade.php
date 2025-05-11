@extends('layouts.app')

@section('content')
<div class="border-b pb-6 mb-6">
    <div class="text-center mb-6">
        <h2 class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span class="text-xl font-semibold text-gray-800">Google Sıralama Kontrolü</span>
        </h2>
    </div>

    <div class="bg-white border rounded-lg p-6 max-w-3xl mx-auto">
        <form id="searchForm" class="space-y-4">
            @csrf
            <div>
                <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">Anahtar Kelime</label>
                <input type="text" id="keyword" name="keyword" class="w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Aramak istediğiniz anahtar kelime" required>
            </div>

            <div>
                <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website URL</label>
                <input type="url" id="website" name="website" class="w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="https://ornek.com" required>
            </div>

            <div>
                <label for="pages" class="block text-sm font-medium text-gray-700 mb-1">Kontrol Edilecek Sayfa Sayısı (Max 10)</label>
                <input type="number" id="pages" name="pages" min="1" max="10" value="10" class="w-full px-4 py-2 border rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="check_all_pages" name="check_all_pages" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="check_all_pages" class="ml-2 block text-sm text-gray-700">Tüm sayfaları kontrol et (ilk sonuç bulunsa bile)</label>
            </div>

            <div class="pt-4">
                <button type="submit" id="searchButton" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center justify-center">
                    <svg id="searchIcon" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <svg id="loadingIcon" class="w-5 h-5 mr-2 animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sıralamayı Kontrol Et
                </button>
            </div>
        </form>
    </div>

    <div id="results" class="mt-8 bg-white border rounded-lg p-6 max-w-3xl mx-auto hidden">
        <div class="pb-4 border-b mb-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-1">Arama Sonuçları</h3>
            <div class="flex flex-wrap">
                <div class="mr-4 mb-2">
                    <span class="text-sm text-gray-500">Anahtar Kelime:</span>
                    <span id="result-keyword" class="text-sm font-medium text-gray-800 ml-1"></span>
                </div>
                <div class="mr-4 mb-2">
                    <span class="text-sm text-gray-500">Website:</span>
                    <span id="result-website" class="text-sm font-medium text-gray-800 ml-1"></span>
                </div>
                <div class="mr-4 mb-2">
                    <span class="text-sm text-gray-500">Kontrol Edilen Sayfa:</span>
                    <span id="result-pages" class="text-sm font-medium text-gray-800 ml-1"></span>
                </div>
                <div class="mr-4 mb-2">
                    <span class="text-sm text-gray-500">Toplam Sonuç:</span>
                    <span id="result-total" class="text-sm font-medium text-gray-800 ml-1"></span>
                </div>
            </div>
        </div>

        <div id="no-results" class="py-4 text-center hidden">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-600">Belirlenen sayfalarda siteniz için bir sonuç bulunamadı.</p>
        </div>

        <div id="found-results" class="hidden">
            <div class="mb-4">
                <h4 class="text-md font-medium text-gray-700 mb-2">Bulunan Pozisyonlar</h4>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-800" id="best-position-text">Siteniz <strong id="best-position"></strong>. sırada bulundu!</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pozisyon</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sayfa</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başlık</th>
                            <th class="py-2 px-4 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                        </tr>
                    </thead>
                    <tbody id="positions-table-body">
                        <!-- Pozisyonlar buraya dinamik olarak eklenecek -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(count($searchHistory) > 0)
    <div class="mt-8 bg-white border rounded-lg p-6 max-w-3xl mx-auto">
        <div class="flex items-center justify-between pb-4 border-b mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Son Aramalar</h3>
            <a href="{{ route('sem.history') }}" class="text-sm text-blue-600 hover:text-blue-800">Tüm Geçmişi Gör</a>
        </div>

        <div class="space-y-3">
            @foreach(array_slice($searchHistory, 0, 3) as $search)
            <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg">
                <div>
                    <div class="font-medium text-gray-800">{{ $search['keyword'] }}</div>
                    <div class="text-sm text-gray-500">{{ $search['domain'] }}</div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="text-xs {{ !empty($search['results']['positions']) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} px-2 py-1 rounded-full">
                        {{ !empty($search['results']['positions']) ? 'Bulundu' : 'Bulunamadı' }}
                    </div>
                    <a href="{{ route('sem.results', $search['id']) }}" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchButton = document.getElementById('searchButton');
    const searchIcon = document.getElementById('searchIcon');
    const loadingIcon = document.getElementById('loadingIcon');
    const resultsDiv = document.getElementById('results');
    const noResultsDiv = document.getElementById('no-results');
    const foundResultsDiv = document.getElementById('found-results');

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // UI göstergeleri
        searchButton.disabled = true;
        searchIcon.classList.add('hidden');
        loadingIcon.classList.remove('hidden');
        resultsDiv.classList.add('hidden');

        // Form verilerini al
        const formData = new FormData(searchForm);

        // Fetch API ile POST isteği
        fetch('{{ route("sem.search") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // UI göstergeleri sıfırla
            searchButton.disabled = false;
            searchIcon.classList.remove('hidden');
            loadingIcon.classList.add('hidden');
            resultsDiv.classList.remove('hidden');

            // Sonuçları göster
            document.getElementById('result-keyword').textContent = data.data.keyword;
            document.getElementById('result-website').textContent = data.data.domain;
            document.getElementById('result-pages').textContent = data.data.pages_checked;
            document.getElementById('result-total').textContent = data.data.total_results.toLocaleString();

            if (data.data.found && data.data.positions.length > 0) {
                // Sonuçlar bulundu
                noResultsDiv.classList.add('hidden');
                foundResultsDiv.classList.remove('hidden');

                // En iyi pozisyonu göster
                const bestPosition = data.data.positions.reduce((min, p) => p.position < min ? p.position : min, data.data.positions[0].position);
                document.getElementById('best-position').textContent = bestPosition;

                // Tablo gövdesini temizle
                const tableBody = document.getElementById('positions-table-body');
                tableBody.innerHTML = '';

                // Sonuçları tabloya ekle
                data.data.positions.forEach(position => {
                    const row = document.createElement('tr');
                    row.className = position.position === bestPosition ? 'bg-green-50' : '';

                    row.innerHTML = `
                        <td class="py-2 px-4 border-b text-sm font-medium ${position.position === bestPosition ? 'text-green-600' : 'text-gray-800'}">${position.position}</td>
                        <td class="py-2 px-4 border-b text-sm text-gray-600">${position.page}</td>
                        <td class="py-2 px-4 border-b text-sm text-gray-800">${position.title}</td>
                        <td class="py-2 px-4 border-b text-sm text-blue-600 truncate max-w-xs">
                            <a href="${position.url}" target="_blank" class="hover:underline">${position.url}</a>
                        </td>
                    `;

                    tableBody.appendChild(row);
                });
            } else {
                // Sonuç bulunamadı
                noResultsDiv.classList.remove('hidden');
                foundResultsDiv.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // UI göstergeleri sıfırla
            searchButton.disabled = false;
            searchIcon.classList.remove('hidden');
            loadingIcon.classList.add('hidden');

            // Hata mesajı göster
            alert('Arama işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.');
        });
    });
});
</script>
@endsection
