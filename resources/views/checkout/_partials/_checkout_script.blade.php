    <script>
        function checkoutData() {
            return {
                // Data Ongkir
                costs: [],
                selectedShippingCost: 0,
                selectedShippingCourier: '',
                isLoadingOngkir: false,
                ongkirError: '',

                // [BARU] Data Promo
                promoCodeInput: '', // Apa yang diketik user
                appliedPromoCode: null, // Kode yang valid dan tersimpan
                discountAmount: 0, // Nominal diskon
                isLoadingPromo: false,
                promoMessage: '',
                promoValid: false,
                
                // Static Subtotal dari PHP
                subtotalDB: {{ $subtotal }}, 

                init() {
                    console.log('🚀 Checkout initialized');
                    @if($pasien && $pasien->district_id)
                        this.checkOngkir();
                    @endif
                },

                // --- FUNGSI ONGKIR (SAMA) ---
                async checkOngkir() {
                    const districtId = '{{ $pasien->district_id ?? "" }}';
                    if (!districtId) return;

                    this.isLoadingOngkir = true;
                    this.ongkirError = '';
                    this.costs = [];
                    
                    try {
                        const response = await fetch("{{ route('api.checkOngkir') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ district_id: districtId })
                        });

                        if (!response.ok) throw new Error('Gagal load ongkir');
                        const data = await response.json();
                        
                        if (Array.isArray(data) && data.length > 0) {
                            this.costs = data;
                        } else {
                            this.ongkirError = 'Kurir tidak tersedia';
                        }
                    } catch (error) {
                        this.ongkirError = 'Gagal menghitung ongkir';
                    } finally {
                        this.isLoadingOngkir = false;
                    }
                },

                selectCourier(price, name) {
                    this.selectedShippingCost = price;
                    this.selectedShippingCourier = name;
                },

                /** Group courier costs by `courier_code` for accordion UI */
                get courierGroups() {
                    const groups = {};
                    for (const c of this.costs) {
                        const code = (c.courier_code || 'lainnya').toLowerCase();
                        if (!groups[code]) groups[code] = [];
                        groups[code].push(c);
                    }
                    return groups;
                },

                // --- [BARU] FUNGSI PROMO ---
                async checkPromo() {
                    if(!this.promoCodeInput) return;

                    this.isLoadingPromo = true;
                    this.promoMessage = '';
                    
                    try {
                        const response = await fetch("{{ route('api.checkPromo') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            // Kirim kode dan subtotal (buat validasi min pembelian)
                            body: JSON.stringify({ 
                                kode: this.promoCodeInput,
                                subtotal: this.subtotalDB 
                            })
                        });

                        const data = await response.json();

                        if (data.valid) {
                            // Sukses
                            this.promoValid = true;
                            this.promoMessage = data.message;
                            this.discountAmount = data.discount_amount;
                            this.appliedPromoCode = data.kode; // Kunci kode agar tidak bisa diedit
                        } else {
                            // Gagal (Syarat tidak penuhi / Salah kode)
                            this.promoValid = false;
                            this.promoMessage = data.message;
                            this.discountAmount = 0;
                            this.appliedPromoCode = null;
                        }
                    } catch (error) {
                        this.promoValid = false;
                        this.promoMessage = 'Terjadi kesalahan sistem.';
                    } finally {
                        this.isLoadingPromo = false;
                    }
                },

                cancelPromo() {
                    this.promoCodeInput = '';
                    this.appliedPromoCode = null;
                    this.discountAmount = 0;
                    this.promoMessage = '';
                    this.promoValid = false;
                }
            }
        }
    </script>