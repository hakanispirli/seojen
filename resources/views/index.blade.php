@extends('layouts.app')

@section('title', 'SEO Analiz Aracı - Web Sitenizi Analiz Edin')

@section('content')
    @include('sections.hero')
    @include('sections.features')
    @include('sections.how-it-works')
    @include('sections.faq')
@endsection

@push('scripts')
<script>
document.getElementById('analyzeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const loadingState = document.getElementById('loadingState');
    const submitButton = form.querySelector('button[type="submit"]');

    // Form verilerini al
    const formData = new FormData(form);

    // Loading durumunu göster
    loadingState.classList.remove('hidden');
    submitButton.disabled = true;

    // Analiz isteği gönder
    axios.post('/analyze', formData)
        .then(response => {
            if (response.data.success) {
                window.location.href = '/results';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: response.data.message,
                    background: '#1a1a1a',
                    color: '#fff'
                });
            }
        })
        .catch(error => {
            if (error.response.status === 429) {
                // Rate limit hatası
                const retryAfter = error.response.data.retry_after;
                Swal.fire({
                    icon: 'warning',
                    title: 'Çok Fazla İstek',
                    text: `Çok fazla analiz isteği gönderdiniz. Lütfen ${retryAfter} saniye bekleyin.`,
                    timer: retryAfter * 1000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    background: '#1a1a1a',
                    color: '#fff'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: 'Analiz sırasında bir hata oluştu. Lütfen tekrar deneyin.',
                    background: '#1a1a1a',
                    color: '#fff'
                });
            }
        })
        .finally(() => {
            loadingState.classList.add('hidden');
            submitButton.disabled = false;
        });
});
</script>
@endpush
