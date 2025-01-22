import './bootstrap';

axios.post('/analyze', formData)
    .then(response => {
        // ... başarılı yanıt işleme ...
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
                showConfirmButton: false
            });
        } else {
            // ... diğer hata işleme ...
        }
    });
