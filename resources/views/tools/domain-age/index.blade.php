@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 mt-20">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <!-- Form Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Domain Yaşı Sorgulama</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Herhangi bir domain adının kayıt tarihini ve yaşını öğrenin.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="p-6">
                <form id="domainForm" class="space-y-4">
                    <div>
                        <label for="domain" class="block text-sm font-medium text-gray-700">Domain Adı</label>
                        <div class="mt-1">
                            <input type="text" name="domain" id="domain"
                                class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                placeholder="ornek.com">
                        </div>
                        <!-- Hata mesajı için alan -->
                        <p id="domainError" class="mt-1 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <button type="submit" id="submitButton"
                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span id="buttonText">Sorgula</span>
                            <!-- Yükleme Animasyonu -->
                            <svg id="loadingIcon" class="hidden animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Results Section -->
                <div id="results" class="mt-6 hidden">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">Domain Bilgileri</h3>
                        </div>

                        <!-- Domain Info Card -->
                        <div class="p-6 bg-blue-50 border-b border-blue-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-600">Domain Adı</p>
                                    <p id="domainName" class="mt-1 text-xl font-semibold text-blue-900"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-blue-600">Domain Yaşı</p>
                                    <p id="domainAge" class="mt-1 text-xl font-semibold text-blue-900"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Info Table -->
                        <div class="divide-y divide-gray-200">
                            <!-- Kayıt Tarihi -->
                            <div class="grid grid-cols-2 hover:bg-gray-50">
                                <div class="px-6 py-4">
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">Kayıt Tarihi</span>
                                    </div>
                                </div>
                                <div class="px-6 py-4 text-right">
                                    <span id="createdAt" class="text-sm text-gray-900 font-medium"></span>
                                </div>
                            </div>

                            <!-- Son Güncelleme -->
                            <div class="grid grid-cols-2 hover:bg-gray-50">
                                <div class="px-6 py-4">
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">Son Güncelleme</span>
                                    </div>
                                </div>
                                <div class="px-6 py-4 text-right">
                                    <span id="updatedAt" class="text-sm text-gray-900 font-medium"></span>
                                </div>
                            </div>

                            <!-- Bitiş Tarihi -->
                            <div class="grid grid-cols-2 hover:bg-gray-50">
                                <div class="px-6 py-4">
                                    <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">Bitiş Tarihi</span>
                                    </div>
                                </div>
                                <div class="px-6 py-4 text-right">
                                    <span id="expiresAt" class="text-sm text-gray-900 font-medium"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Info -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="flex-shrink-0 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <p>Bilgiler WHOIS sunucularından alınmaktadır ve anlık değişiklik gösterebilir.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Alert -->
                <div id="errorAlert" class="hidden mt-6">
                    <div class="bg-red-50 border-l-4 border-red-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p id="errorMessage" class="text-sm text-red-700"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('domainForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const domain = document.getElementById('domain').value;
    const resultsDiv = document.getElementById('results');
    const errorAlert = document.getElementById('errorAlert');
    const submitButton = document.getElementById('submitButton');
    const loadingIcon = document.getElementById('loadingIcon');
    const buttonText = document.getElementById('buttonText');
    const domainError = document.getElementById('domainError');

    // Reset UI
    resultsDiv.classList.add('hidden');
    errorAlert.classList.add('hidden');
    domainError.classList.add('hidden');
    loadingIcon.classList.remove('hidden');
    buttonText.textContent = 'Sorgulanıyor...';
    submitButton.disabled = true;

    try {
        const response = await fetch('{{ route("tools.domain-age.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ domain })
        });

        const result = await response.json();

        if (result.success) {
            // Sonuçları göster
            document.getElementById('domainName').textContent = result.data.domain;

            // Domain yaşını göster
            const age = result.data.age;
            document.getElementById('domainAge').textContent = age.formatted || `${age.years} yıl ${age.months} ay ${age.days} gün`;

            // Tarihleri göster
            document.getElementById('createdAt').textContent = result.data.dates.created;
            document.getElementById('updatedAt').textContent = result.data.dates.updated || 'Bilgi yok';
            document.getElementById('expiresAt').textContent = result.data.dates.expires || 'Bilgi yok';

            resultsDiv.classList.remove('hidden');
        } else {
            document.getElementById('errorMessage').textContent = result.message;
            errorAlert.classList.remove('hidden');
        }
    } catch (error) {
        document.getElementById('errorMessage').textContent = 'Bir hata oluştu: ' + error.message;
        errorAlert.classList.remove('hidden');
    } finally {
        // Reset button state
        loadingIcon.classList.add('hidden');
        buttonText.textContent = 'Sorgula';
        submitButton.disabled = false;
    }
});

// Domain input validation
document.getElementById('domain').addEventListener('input', function(e) {
    const domainError = document.getElementById('domainError');
    const domain = e.target.value;

    if (domain && !domain.match(/^(?!:\/\/)(?:[a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}$/)) {
        domainError.textContent = 'Lütfen geçerli bir domain adı girin';
        domainError.classList.remove('hidden');
    } else {
        domainError.classList.add('hidden');
    }
});
</script>
@endpush
@endsection
