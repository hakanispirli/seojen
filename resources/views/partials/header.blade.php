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

            <!-- Navigation -->
            <div class="flex items-center space-x-1">
                <a href="#features"
                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-900 rounded-lg hover:bg-gray-100/50 transition-all duration-200">
                    Özellikler
                </a>
                <a href="#how-it-works"
                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-900 rounded-lg hover:bg-gray-100/50 transition-all duration-200">
                    Nasıl Çalışır?
                </a>
            </div>
        </div>
    </nav>
</header>
