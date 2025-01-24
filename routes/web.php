<?php

use App\Http\Controllers\SeoAnalyzerController;
use App\Http\Controllers\RobotsGeneratorController;
use App\Http\Controllers\KeywordDensityController;
use App\Http\Controllers\DomainAgeController;
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

// Robots.txt Generator Routes
Route::prefix('tools/robots')->group(function () {
    Route::get('/', [RobotsGeneratorController::class, 'index'])->name('tools.robots.index');
    Route::post('/generate', [RobotsGeneratorController::class, 'generate'])->name('tools.robots.generate');
    Route::post('/download', [RobotsGeneratorController::class, 'download'])->name('tools.robots.download');
});

// Keyword Density Routes
Route::prefix('tools/keyword-density')->group(function () {
    Route::get('/', [KeywordDensityController::class, 'index'])->name('tools.keyword-density.index');
    Route::post('/analyze', [KeywordDensityController::class, 'analyze'])->name('tools.keyword-density.analyze');
});

// Domain Age Checker Routes
Route::prefix('tools/domain-age')->group(function () {
    Route::get('/', [DomainAgeController::class, 'index'])->name('tools.domain-age.index');
    Route::post('/check', [DomainAgeController::class, 'check'])->name('tools.domain-age.check');
});
