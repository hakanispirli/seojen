@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100">
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 mt-20">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <!-- Form Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Robots.txt Oluşturucu</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Web siteniz için özelleştirilmiş robots.txt dosyası oluşturun. Arama motoru botlarının sitenizi nasıl taraması gerektiğini kontrol edin.
                        </p>
                    </div>
                </div>
            </div>

            <form id="robotsForm" class="divide-y divide-gray-200">
                <!-- User Agents Section -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">User Agents</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Hangi arama motoru botlarının sitenizi taramasını istediğinizi belirtin.
                            </p>
                        </div>
                        <button type="button" onclick="addUserAgent()" class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 text-sm font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            User Agent Ekle
                        </button>
                    </div>
                    <div id="userAgents" class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <input type="text" name="user_agents[]" value="*" required
                                class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                placeholder="Örn: Googlebot">
                            <button type="button" onclick="removeUserAgent(this)" class="p-2 text-gray-400 hover:text-red-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Rules Section -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">Kurallar</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Botların hangi sayfalara erişip erişemeyeceğini belirleyin.
                            </p>
                        </div>
                        <button type="button" onclick="addRule()" class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 text-sm font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Kural Ekle
                        </button>
                    </div>
                    <div id="rules" class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <select name="rules[0][type]" class="px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                                <option value="allow">Allow</option>
                                <option value="disallow">Disallow</option>
                            </select>
                            <input type="text" name="rules[0][path]"
                                class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                placeholder="/path">
                            <button type="button" onclick="removeRule(this)" class="p-2 text-gray-400 hover:text-red-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sitemaps Section -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">Sitemap URLs</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Site haritanızın konumunu arama motorlarına bildirin.
                            </p>
                        </div>
                        <button type="button" onclick="addSitemap()" class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 text-sm font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Sitemap Ekle
                        </button>
                    </div>
                    <div id="sitemaps" class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <input type="url" name="sitemaps[]"
                                class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                placeholder="https://example.com/sitemap.xml">
                            <button type="button" onclick="removeSitemap(this)" class="p-2 text-gray-400 hover:text-red-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Crawl Delay Section -->
                <div class="p-6">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">Crawl Delay</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Botların sayfalarınızı tarama sıklığını belirleyin.
                        </p>
                    </div>
                    <div class="max-w-xs mt-4">
                        <div class="flex items-center">
                            <input type="number" name="crawl_delay" min="0" max="60"
                                class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
                                placeholder="Saniye cinsinden">
                            <span class="ml-2 text-sm text-gray-500">(opsiyonel)</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="p-6 bg-gray-50">
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Robots.txt Oluştur
                        </button>
                    </div>
                </div>
            </form>

            <!-- Preview Section -->
            <div id="preview" class="hidden border-t border-gray-200">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Önizleme</h2>
                    <div class="bg-gray-900 text-gray-100 p-6 rounded-lg">
                        <pre id="previewContent" class="text-sm font-mono whitespace-pre-wrap"></pre>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button onclick="downloadRobots()" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            İndir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let ruleCount = 1;

function addUserAgent() {
    const container = document.getElementById('userAgents');
    const div = document.createElement('div');
    div.className = 'flex items-center space-x-2';
    div.innerHTML = `
        <input type="text" name="user_agents[]" required
            class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
            placeholder="Örn: Googlebot">
        <button type="button" onclick="removeUserAgent(this)" class="p-2 text-gray-400 hover:text-red-500">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
        </button>
    `;
    container.appendChild(div);
}

function addRule() {
    const container = document.getElementById('rules');
    const div = document.createElement('div');
    div.className = 'flex items-center space-x-2';
    div.innerHTML = `
        <select name="rules[${ruleCount}][type]" class="px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
            <option value="allow">Allow</option>
            <option value="disallow">Disallow</option>
        </select>
        <input type="text" name="rules[${ruleCount}][path]" required
            class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
            placeholder="/path">
        <button type="button" onclick="removeRule(this)" class="p-2 text-gray-400 hover:text-red-500">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
        </button>
    `;
    container.appendChild(div);
    ruleCount++;
}

function addSitemap() {
    const container = document.getElementById('sitemaps');
    const div = document.createElement('div');
    div.className = 'flex items-center space-x-2';
    div.innerHTML = `
        <input type="url" name="sitemaps[]"
            class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
            placeholder="https://example.com/sitemap.xml">
        <button type="button" onclick="removeSitemap(this)" class="p-2 text-gray-400 hover:text-red-500">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
        </button>
    `;
    container.appendChild(div);
}

function removeUserAgent(button) {
    if (document.getElementById('userAgents').children.length > 1) {
        button.closest('.flex').remove();
    }
}

function removeRule(button) {
    button.closest('.flex').remove();
}

function removeSitemap(button) {
    button.closest('.flex').remove();
}

// Form submit işlemini güncelleyelim
document.getElementById('robotsForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const data = {
        user_agents: [],
        rules: [],
        sitemaps: [],
        crawl_delay: document.querySelector('input[name="crawl_delay"]').value || null
    };

    // User Agents
    document.querySelectorAll('input[name="user_agents[]"]').forEach(input => {
        if (input.value.trim()) {
            data.user_agents.push(input.value.trim());
        }
    });

    // Rules - Bu kısmı düzelttik
    document.querySelectorAll('#rules .flex').forEach(ruleDiv => {
        const typeSelect = ruleDiv.querySelector('select[name^="rules["]');
        const pathInput = ruleDiv.querySelector('input[name^="rules["]');

        if (typeSelect && pathInput && pathInput.value.trim()) {
            data.rules.push({
                type: typeSelect.value,
                path: pathInput.value.trim()
            });
        }
    });

    // Sitemaps
    document.querySelectorAll('input[name="sitemaps[]"]').forEach(input => {
        if (input.value.trim()) {
            data.sitemaps.push(input.value.trim());
        }
    });

    try {
        const response = await fetch('{{ route("tools.robots.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            window.generatedContent = result.content;
            document.getElementById('previewContent').textContent = result.content;
            document.getElementById('preview').classList.remove('hidden');
        } else {
            const errorMessages = Object.values(result.errors).flat();
            alert('Hata oluştu:\n' + errorMessages.join('\n'));
        }
    } catch (error) {
        alert('Bir hata oluştu: ' + error.message);
    }
});

// Kural ekleme fonksiyonunu da güncelleyelim
function addRule() {
    const container = document.getElementById('rules');
    const ruleCount = container.children.length;
    const div = document.createElement('div');
    div.className = 'flex items-center space-x-2';
    div.innerHTML = `
        <select name="rules[${ruleCount}][type]" class="px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
            <option value="allow">Allow</option>
            <option value="disallow">Disallow</option>
        </select>
        <input type="text" name="rules[${ruleCount}][path]"
            class="block w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"
            placeholder="/path">
        <button type="button" onclick="removeRule(this)" class="p-2 text-gray-400 hover:text-red-500">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
        </button>
    `;
    container.appendChild(div);
}

async function downloadRobots() {
    const response = await fetch('{{ route("tools.robots.download") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ content: window.generatedContent })
    });

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'robots.txt';
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}
</script>
@endpush
@endsection
