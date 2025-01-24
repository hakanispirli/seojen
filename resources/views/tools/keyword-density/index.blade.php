@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 mt-20">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <!-- Form Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Anahtar Kelime Yoğunluğu Analizi</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Metin veya URL içeriğindeki kelimelerin kullanım sıklığını analiz edin.
                        </p>
                    </div>
                </div>
            </div>

            <form id="densityForm" class="divide-y divide-gray-200">
                <!-- Content Type Selection -->
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">İçerik Tipi</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="content_type" value="text" class="form-radio text-blue-600" checked>
                                    <span class="ml-2">Metin</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="content_type" value="url" class="form-radio text-blue-600">
                                    <span class="ml-2">URL</span>
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Metin: Doğrudan analiz edilecek içeriği yapıştırın veya yazın.<br>
                                URL: Web sayfasının adresini girin, içerik otomatik olarak alınacak.
                            </p>
                        </div>

                        <div id="textInput" class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Metin</label>
                            <textarea name="content" rows="6"
                                class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                placeholder="Analiz edilecek metni girin..."></textarea>
                            <p class="mt-1 text-sm text-gray-500">
                                Analiz edilecek metni bu alana yapıştırın. HTML etiketleri otomatik olarak temizlenecektir.
                            </p>
                        </div>

                        <div id="urlInput" class="space-y-2 hidden">
                            <label class="block text-sm font-medium text-gray-700">URL</label>
                            <input type="url" name="url"
                                class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                placeholder="https://example.com">
                            <p class="mt-1 text-sm text-gray-500">
                                Web sayfasının tam URL'sini girin (örn: https://example.com/sayfa). Sayfa içeriği otomatik olarak alınacak ve analiz edilecektir.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Analiz Seçenekleri</h2>
                    <div class="space-y-4">
                        <div class="bg-blue-50 p-4 rounded-lg mb-6">
                            <p class="text-sm text-blue-700">
                                Bu seçenekler analiz sonuçlarının daha doğru olmasını sağlar. İhtiyacınıza göre aktif edebilirsiniz.
                            </p>
                        </div>

                        <label class="flex items-center group">
                            <input type="checkbox" name="exclude_stop_words" class="form-checkbox text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Stop Words'leri Hariç Tut</span>
                            <div class="hidden group-hover:block absolute bg-white border border-gray-200 rounded-lg p-2 shadow-lg ml-8 mt-8 w-64 text-sm">
                                "ve", "veya", "için" gibi sık kullanılan bağlaç ve edat türü kelimeleri analizden çıkarır.
                            </div>
                        </label>

                        <label class="flex items-center group">
                            <input type="checkbox" name="use_stemming" class="form-checkbox text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Stemming Uygula</span>
                            <div class="hidden group-hover:block absolute bg-white border border-gray-200 rounded-lg p-2 shadow-lg ml-8 mt-8 w-64 text-sm">
                                Kelimelerin kök halini kullanır. Örneğin: "kitaplar" ve "kitap" aynı kelime olarak sayılır.
                            </div>
                        </label>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Kelime Uzunluğu</label>
                            <input type="number" name="min_word_length" value="3" min="1" max="10"
                                class="w-24 px-4 py-2 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                            <p class="mt-1 text-sm text-gray-500">
                                Bu değerden kısa kelimeler analize dahil edilmeyecektir. Önerilen değer: 3
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="p-6 bg-gray-50">
                    <div class="flex justify-end">
                        <!-- Loading Spinner -->
                        <div id="loadingSpinner" class="hidden mr-4">
                            <div class="flex items-center">
                                <svg class="animate-spin h-5 w-5 text-blue-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Analiz yapılıyor...</span>
                            </div>
                        </div>

                        <button type="submit" id="submitButton" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Analiz Et
                        </button>
                    </div>
                </div>
            </form>

            <!-- Results Section -->
            <div id="results" class="hidden border-t border-gray-200">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Analiz Sonuçları</h2>

                    <!-- Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-500">Toplam Kelime</div>
                            <div id="totalWords" class="text-2xl font-semibold text-gray-900">0</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-500">Benzersiz Kelime</div>
                            <div id="uniqueWords" class="text-2xl font-semibold text-gray-900">0</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="text-sm text-gray-500">Ortalama Yoğunluk</div>
                            <div id="avgDensity" class="text-2xl font-semibold text-gray-900">0%</div>
                        </div>
                    </div>

                    <!-- Keywords Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelime</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sayı</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yoğunluk</th>
                                </tr>
                            </thead>
                            <tbody id="keywordsTable" class="bg-white divide-y divide-gray-200">
                                <!-- JavaScript ile doldurulacak -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('input[name="content_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('textInput').classList.toggle('hidden', this.value === 'url');
        document.getElementById('urlInput').classList.toggle('hidden', this.value === 'text');
    });
});

document.getElementById('densityForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Loading durumunu göster
    const loadingSpinner = document.getElementById('loadingSpinner');
    const submitButton = document.getElementById('submitButton');
    loadingSpinner.classList.remove('hidden');
    submitButton.disabled = true;
    submitButton.classList.add('opacity-75', 'cursor-not-allowed');

    const formData = new FormData(this);
    const contentType = formData.get('content_type');

    const data = {
        content_type: contentType,
        exclude_stop_words: formData.get('exclude_stop_words') === 'on',
        use_stemming: formData.get('use_stemming') === 'on',
        min_word_length: parseInt(formData.get('min_word_length'))
    };

    // Content type'a göre content veya url ekle
    if (contentType === 'text') {
        data.content = formData.get('content');
    } else {
        data.url = formData.get('url');
    }

    try {
        const response = await fetch('{{ route("tools.keyword-density.analyze") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            displayResults(result.data);

            // Sonuçlara smooth scroll
            const resultsSection = document.getElementById('results');
            resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            const errors = Object.values(result.errors).flat();
            alert('Hata oluştu:\n' + errors.join('\n'));
        }
    } catch (error) {
        alert('Bir hata oluştu: ' + error.message);
    } finally {
        // Loading durumunu gizle
        loadingSpinner.classList.add('hidden');
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-75', 'cursor-not-allowed');
    }
});

function displayResults(data) {
    // İstatistikleri güncelle
    document.getElementById('totalWords').textContent = data.total_words;
    document.getElementById('uniqueWords').textContent = data.unique_words;
    document.getElementById('avgDensity').textContent = data.stats.avg_density + '%';

    // Tablo içeriğini oluştur
    const tbody = document.getElementById('keywordsTable');
    tbody.innerHTML = '';

    data.keywords.forEach(keyword => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${keyword.word}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${keyword.count}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${keyword.density}%</td>
        `;
        tbody.appendChild(tr);
    });

    // Sonuç bölümünü göster ve animasyonlu geçiş ekle
    const resultsSection = document.getElementById('results');
    resultsSection.classList.remove('hidden');
    resultsSection.classList.add('animate-fade-in');
}
</script>

<!-- Animasyon için CSS ekleyelim -->
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
</style>
@endpush
@endsection
