{{-- Partial: Scripts --}}
<script>
    async function sendEmailInvoice(orderKode, btnEvent) {
        const btn = btnEvent.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-[#8B6F1B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...`;
        
        try {
            const response = await fetch('/orders/' + orderKode + '/invoice/email', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            let data;
            try {
                data = await response.json();
            } catch (jsonErr) {
                throw new Error('Server mengembalikan response tidak valid (status: ' + response.status + ')');
            }
            
            if (response.ok && data.success) {
                window.showToast && window.showToast('success', data.message || 'Email berhasil dikirim.', 'Berhasil');
            } else {
                window.showToast && window.showToast('error', data.error || 'Gagal mengirim email. Silakan coba lagi.', 'Gagal');
            }
        } catch (err) {
            console.error('Email send error:', err);
            window.showToast && window.showToast('error', 'Gagal mengirim email: ' + err.message, 'Gagal');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
