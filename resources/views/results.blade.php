@extends('layouts.app')

@section('title', 'Analiz Sonuçları')

@section('content')
<div class="max-w-4xl mx-auto mt-12">
    <div class="bg-white rounded-lg shadow-md p-8">
        <!-- Ana Başlık ve Skor -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-4">SEO Analiz Sonuçları</h1>
            <div class="inline-flex items-center bg-gray-100 rounded-full px-6 py-2">
                <span class="text-sm text-gray-500 mr-2">Genel Skor:</span>
                <span class="text-2xl font-bold {{ $results['overall_score'] >= 80 ? 'text-green-600' : ($results['overall_score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $results['overall_score'] ?? 0 }}/100
                </span>
            </div>
            <div class="mt-4">
                <div class="h-2 bg-gray-200 rounded-full max-w-md mx-auto">
                    <div class="h-2 rounded-full {{ $results['overall_score'] >= 80 ? 'bg-green-500' : ($results['overall_score'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                         style="width: {{ $results['overall_score'] }}%">
                    </div>
                </div>
            </div>
        </div>

        <!-- URL Bilgisi -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-8 text-center">
            <h2 class="text-sm font-medium text-gray-500 mb-1">Analiz Edilen URL</h2>
            <a href="{{ $results['url'] }}" target="_blank"
               class="text-blue-600 hover:text-blue-800 break-all font-medium">
                {{ $results['url'] }}
            </a>
        </div>

        <!-- Analiz Bölümleri -->
        <div class="space-y-8">
            @include('results.meta_analizi')
            @include('results.baslik_analizi')
            @include('results.url_analizi')
            @include('results.teknik_analiz')
            @include('results.performance_metrics')
            @include('results.gorsel_analizi')
        </div>

        <!-- Yeni Analiz Butonu -->
        <div class="text-center pt-8 pb-12">
            <a href="{{ route('home') }}"
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Yeni Analiz Yap
            </a>
        </div>
    </div>
</div>
@endsection
