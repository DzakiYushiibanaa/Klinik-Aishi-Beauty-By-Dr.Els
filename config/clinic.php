<?php

/**
 * Informasi Klinik Aishi Beauty.
 *
 * Sumber data tunggal untuk:
 *   - Header email & invoice (alamat, kontak, brand)
 *   - Footer email (kontak resmi)
 *   - Origin pengiriman (RajaOngkir)
 *
 * Atur lewat .env, fallback default sudah di-set untuk klinik utama.
 */

return [
    /* ─── Brand ─── */
    'name'    => env('CLINIC_NAME', 'Aishi Beauty'),
    'tagline' => env('CLINIC_TAGLINE', 'Beauty Clinic & Skincare'),

    /* ─── Lokasi & Pengiriman (origin RajaOngkir) ─── */
    'district' => env('CLINIC_DISTRICT', 'Beji'),
    'city'     => env('CLINIC_CITY', 'Kota Depok'),
    'province' => env('CLINIC_PROVINCE', 'Jawa Barat'),

    /* ─── Alamat lengkap (untuk header/footer email & invoice) ─── */
    'address' => env(
        'CLINIC_ADDRESS',
        'Jl. Nusantara Raya No.99b, Beji, Kec. Beji, Kota Depok, Jawa Barat 16421'
    ),
    'address_short' => env('CLINIC_ADDRESS_SHORT', 'Jl. Nusantara Raya No.99b, Kota Depok'),

    /* ─── Kontak resmi ─── */
    'email'    => env('CLINIC_EMAIL', 'aishibeauty.byels@gmail.com'),
    'phone'    => env('CLINIC_PHONE', '(021) 2956-9090'),
    'whatsapp' => env('CLINIC_WHATSAPP', '+62 857 8005 0007'),

    /* ─── Sosial media (opsional) ─── */
    'instagram' => env('CLINIC_INSTAGRAM', '@aishibeauty.bydrels'),
    'website'   => env('CLINIC_WEBSITE', null),
];
