<?php

use App\Http\Controllers\SeoAnalyzerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('home');

Route::post('/analyze', [SeoAnalyzerController::class, 'analyze'])
    ->name('analyze')
    ->middleware('seo.limit');

Route::get('/results', function () {
    return view('results', ['results' => session('analysis_results', [])]);
})->name('results');
