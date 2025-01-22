<!-- Footer -->
<footer class="bg-gradient-to-b from-white to-gray-50 border-t border-gray-200/20">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="col-span-1">
                <a href="{{ route('home') }}" class="flex items-center space-x-3">
                    <div class="bg-gradient-to-r from-blue-900 to-black p-2 rounded-lg">
                        <svg class="h-8 w-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-blue-900 to-black bg-clip-text text-transparent">Seojen</span>
                </a>
                <p class="mt-4 text-sm text-gray-500">
                    Seojen.org ile web sitenizin SEO performansını analiz edin, rakiplerinizin önüne geçin.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="col-span-1">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Hızlı Erişim</h3>
                <ul class="mt-4 space-y-3">
                    <li>
                        <a href="#features" class="text-base text-gray-500 hover:text-gray-900 transition-colors">
                            Özellikler
                        </a>
                    </li>
                    <li>
                        <a href="#how-it-works" class="text-base text-gray-500 hover:text-gray-900 transition-colors">
                            Nasıl Çalışır?
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition-colors">
                            SSS
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Resources -->
            <div class="col-span-1">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">SEO Araçları</h3>
                <ul class="mt-4 space-y-3">
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition-colors">
                            SEO Analiz
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition-colors">
                            Site Hız Testi
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition-colors">
                            Backlink Analizi
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-span-1">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">İletişim</h3>
                <ul class="mt-4 space-y-3">
                    <li>
                        <a href="mailto:info@seojen.org" class="text-base text-gray-500 hover:text-gray-900 transition-colors">
                            info@seojen.org
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-base text-gray-500 hover:text-gray-900 transition-colors">
                            Destek Merkezi
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-base text-gray-400">
                    &copy; {{ date('Y') }} Seojen.org | Tüm hakları saklıdır.
                </div>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        Gizlilik Politikası
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        Kullanım Koşulları
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
