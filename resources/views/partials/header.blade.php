<header class="fixed w-full top-0 z-50 backdrop-blur-lg bg-white/80 border-b border-gray-200/20">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                    <div class="bg-gradient-to-r from-blue-900 to-black p-2 rounded-lg">
                        <svg class="h-8 w-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-bold bg-gradient-to-r from-blue-900 to-black bg-clip-text text-transparent">Seojen</span>
                        <span class="block text-xs text-gray-500">SEO Analysis Tool</span>
                    </div>
                </a>
            </div>

            <!-- Navigation with Dropdown -->
            <div class="flex items-center space-x-4 relative group">
                <a href="#features" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-900 rounded-lg hover:bg-gray-100/50 transition-all duration-200">
                    Özellikler
                </a>
                <a href="#how-it-works" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-900 rounded-lg hover:bg-gray-100/50 transition-all duration-200">
                    Nasıl Çalışır?
                </a>

                <div class="relative">
                    <button class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-900 rounded-lg hover:bg-gray-100/50 transition-all duration-200 flex items-center">
                        Araçlar
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg">
                        <a href="{{route('tools.keyword-density.index')}}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Anahtar Kelime Analizi</a>
                        <a href="{{route('tools.robots.index')}}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Robots.txt Oluşturucu</a>
                        <a href="{{route('tools.domain-age.index')}}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Domain Yaşı Sorgulama</a>
                        <a href="{{route('tools.sem.index')}}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">SEM Aracı</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
 </header>
