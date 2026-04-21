<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | RajaOngkir API Key
    |--------------------------------------------------------------------------
    |
    | API Key dari RajaOngkir Komerce.id
    | Dapatkan di: https://rajaongkir.komerce.id
    |
    */
    'api_key' => env('RAJAONGKIR_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Origin District ID
    |--------------------------------------------------------------------------
    |
    | ID District (Kecamatan) asal pengiriman
    | Contoh: 151 untuk Bandung Wetan, Bandung
    | 
    | Cara cari ID:
    | 1. Akses API: https://rajaongkir.komerce.id/api/v1/location/districts
    | 2. Cari kota/kecamatan Anda
    | 3. Ambil ID-nya
    |
    */
    'origin_district_id' => env('RAJAONGKIR_ORIGIN_DISTRICT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Minimal Weight (dalam gram)
    |--------------------------------------------------------------------------
    |
    | Minimal berat yang dikirim ke API RajaOngkir
    | Default: 100 gram (0.1 kg)
    | 
    | PENTING: Jangan set terlalu tinggi (misal 1000) karena akan membuat
    | ongkir jadi mahal untuk produk ringan!
    |
    */
    'min_weight' => env('RAJAONGKIR_MIN_WEIGHT', 50),

    /*
    |--------------------------------------------------------------------------
    | Supported Couriers
    |--------------------------------------------------------------------------
    |
    | Daftar kurir yang tersedia
    | 
    | Kurir yang support di RajaOngkir Komerce.id:
    | - jne, jnt, pos, tiki, sicepat, anteraja, ninja, lion, idexpress
    |
    */
    'couriers' => [
        'jne',      // JNE
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Duration (dalam menit)
    |--------------------------------------------------------------------------
    |
    | Berapa lama hasil cek ongkir di-cache
    | Untuk mengurangi hit ke API
    |
    */
    'cache_duration' => env('RAJAONGKIR_CACHE_DURATION', 5),

];