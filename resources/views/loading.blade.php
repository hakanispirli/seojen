@extends('layouts.app')

@section('title', 'Analiz Ediliyor')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
        <h2 class="mt-4 text-xl font-semibold text-gray-700">Analiz Ediliyor</h2>
        <p class="mt-2 text-gray-500">Lütfen bekleyin, site analiz ediliyor...</p>
    </div>
</div>

@section('scripts')
<script>
    // 3 saniye sonra sonuç sayfasına yönlendir
    setTimeout(() => {
        window.location.href = '{{ route("results") }}';
    }, 3000);
</script>
@endsection
@endsection
