{{-- Partial: Order Scripts --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ordersPage', (initialOrders) => ({
            orderTab: 'produk',
            orderFilter: 'semua',
            orderSearch: '',
            showInvoiceModal: false,
            selectedOrder: null,
            orders: initialOrders,

            getFilteredOrders() {
                return this.orders.filter(o => {
                    if (this.orderTab !== 'semua' && o.type !== this.orderTab) return false;
                    if (this.orderFilter !== 'semua') {
                        const combo = this.getComboStatus(o);
                        if (combo !== this.orderFilter) return false;
                    }
                    if (this.orderSearch.trim()) {
                        const q = this.orderSearch.toLowerCase();
                        const match = o.kode.toLowerCase().includes(q) || o.items.some(i => i.name.toLowerCase().includes(q));
                        if (!match) return false;
                    }
                    return true;
                });
            },
            getComboStatus(o) {
                if (['batal','dibatalkan'].includes(o.status) || o.shipping === 'dibatalkan') return 'dibatalkan';
                if (o.status === 'cancel_requested') return 'cancel_requested';
                if ((o.status === 'menunggu_pembayaran' || o.status === 'draft') && o.expired_at) {
                    const now = new Date();
                    const expired = new Date(o.expired_at);
                    if (now > expired) return 'dibatalkan';
                }
                if (o.status === 'menunggu_pembayaran' || o.status === 'draft') return 'belum_bayar';
                if (o.shipping === 'selesai') return 'selesai';
                if (o.shipping === 'dikirim') return 'dikirim';
                return 'dikemas';
            },
            getCancelReason(o) {
                if ((o.status === 'menunggu_pembayaran' || o.status === 'draft') && o.expired_at) {
                    const now = new Date();
                    const expired = new Date(o.expired_at);
                    if (now > expired) return 'Batas waktu pembayaran habis';
                }
                return o.cancel_reason || 'Dibatalkan oleh sistem';
            },
            getFiltersForTab() {
                if (this.orderTab === 'produk') return ['semua','belum_bayar','dikemas','dikirim','selesai','dibatalkan'];
                return ['semua','belum_bayar','sudah_dibayar','selesai','dibatalkan'];
            },
            filterLabel(f) {
                return { semua:'Semua', belum_bayar:'Belum Bayar', dikemas:'Dikemas', dikirim:'Dikirim', selesai:'Selesai', dibatalkan:'Dibatalkan', sudah_dibayar:'Sudah Dibayar', cancel_requested:'Menunggu Konfirmasi Pembatalan' }[f] || f;
            },
            formatPaymentChannel(val) {
                if (!val) return 'Belum Dipilih';
                const labels = {
                    'bca': 'BCA Virtual Account', 'bni': 'BNI Virtual Account', 'bri': 'BRI Virtual Account',
                    'mandiri': 'Mandiri Bill Payment', 'mandiri_bill': 'Mandiri Bill Payment',
                    'permata': 'Permata Virtual Account', 'cimb': 'CIMB Virtual Account', 'bsi': 'BSI Virtual Account',
                    'bank_transfer': 'Transfer Bank', 'qris': 'QRIS', 'midtrans_qris': 'QRIS',
                    'gopay': 'GoPay', 'shopeepay': 'ShopeePay', 'dana': 'DANA', 'ovo': 'OVO', 'linkaja': 'LinkAja',
                    'alfamart': 'Alfamart', 'indomaret': 'Indomaret', 'cstore': 'Minimarket',
                    'credit_card': 'Kartu Kredit', 'akulaku': 'Akulaku', 'kredivo': 'Kredivo',
                    'cash': 'Tunai', 'web_redirect': 'Midtrans', 'midtrans_snap': 'Midtrans'
                };
                const key = String(val).toLowerCase();
                if (labels[key]) return labels[key];
                return key.replace(/[_-]+/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
            }
        }));
    });
    async function sendEmailInvoice(orderKode, btnEvent) {
        const btn = btnEvent.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-[#8B6F1B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...`;
        
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
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 500));
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

    async function processPayment(orderKode, btnEvent) {
        const btn = btnEvent.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...`;
        
        try {
            const response = await fetch('/payment/' + orderKode + '/token');
            const data = await response.json();
            
            if (!response.ok) {
                window.showToast && window.showToast('error', data.error || 'Gagal memproses pembayaran. Silakan coba lagi.', 'Gagal');
                if (data.reload) {
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
                return;
            }

            let paymentCompleted = false;
            window.snap.pay(data.token, {
                onSuccess: function(result) {
                    paymentCompleted = true;
                    const rawOrderId = result.order_id || orderKode;
                    const kodeOrder = rawOrderId.replace(/-[0-9]+$/, '');
                    window.location.href = "/payment-status/success?order_id=" + kodeOrder;
                },
                onPending: function(result) {
                    paymentCompleted = true;
                    window.showToast && window.showToast('warning', 'Pembayaran Anda masih tertunda.', 'Pembayaran Tertunda');
                    setTimeout(() => window.location.reload(), 1200);
                },
                onError: function(result) {
                    paymentCompleted = true;
                    window.showToast && window.showToast('error', 'Pembayaran gagal atau waktu pembayaran telah habis.', 'Pembayaran Gagal');
                    setTimeout(() => window.location.reload(), 1200);
                },
                onClose: function() {
                    if (!paymentCompleted) {
                        window.location.reload();
                    }
                }
            });
        } catch (err) {
            window.showToast && window.showToast('error', 'Terjadi kesalahan koneksi. Silakan periksa jaringan Anda.', 'Gagal Terhubung');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
