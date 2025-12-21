<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $now = Carbon::now();

            /*
            |--------------------------------------------------------------------------
            | 1) SEED KATEGORI PRODUK
            |--------------------------------------------------------------------------
            | Kamu bisa nambah/ubah sesuka hati.
            */
            $productCategories = [
                [
                    'nama' => 'Skincare Wajah',
                    'deskripsi' => 'Produk perawatan wajah harian seperti cleanser, serum, sunscreen, moisturizer.',
                    'is_active' => true,
                ],
                [
                    'nama' => 'Body Care',
                    'deskripsi' => 'Produk perawatan tubuh seperti body lotion dan body wash.',
                    'is_active' => true,
                ],
                [
                    'nama' => 'Acne Care',
                    'deskripsi' => 'Produk fokus jerawat: spot treatment, toner acne, dan pendukung pemulihan kulit.',
                    'is_active' => true,
                ],
            ];

            $productCategoryIds = [];
            foreach ($productCategories as $cat) {
                // jika kategori sudah ada, pakai yang lama
                $existing = DB::table('product_categories')->where('nama', $cat['nama'])->first();
                if ($existing) {
                    $productCategoryIds[$cat['nama']] = $existing->product_category_id;
                    continue;
                }

                $id = DB::table('product_categories')->insertGetId([
                    'nama' => $cat['nama'],
                    'deskripsi' => $cat['deskripsi'],
                    'is_active' => $cat['is_active'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'product_category_id');

                $productCategoryIds[$cat['nama']] = $id;
            }

            /*
            |--------------------------------------------------------------------------
            | 2) SEED KATEGORI TREATMENT
            |--------------------------------------------------------------------------
            */
            $treatmentCategories = [
                [
                    'nama' => 'Facial',
                    'deskripsi' => 'Perawatan facial untuk membersihkan, menenangkan, dan memperbaiki kondisi kulit.',
                    'is_active' => true,
                ],
                [
                    'nama' => 'Peeling',
                    'deskripsi' => 'Perawatan eksfoliasi untuk membantu mencerahkan dan meratakan tekstur kulit.',
                    'is_active' => true,
                ],
                [
                    'nama' => 'Laser & Device',
                    'deskripsi' => 'Perawatan berbasis alat (laser/device) untuk peremajaan dan masalah kulit tertentu.',
                    'is_active' => true,
                ],
            ];

            $treatmentCategoryIds = [];
            foreach ($treatmentCategories as $cat) {
                $existing = DB::table('treatment_categories')->where('nama', $cat['nama'])->first();
                if ($existing) {
                    $treatmentCategoryIds[$cat['nama']] = $existing->treatment_category_id;
                    continue;
                }

                $id = DB::table('treatment_categories')->insertGetId([
                    'nama' => $cat['nama'],
                    'deskripsi' => $cat['deskripsi'],
                    'is_active' => $cat['is_active'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'treatment_category_id');

                $treatmentCategoryIds[$cat['nama']] = $id;
            }

            /*
            |--------------------------------------------------------------------------
            | 3) SEED PRODUCTS (5 DATA)
            |--------------------------------------------------------------------------
            | Kolom wajib sesuai ERD:
            | product_category_id, nama, deskripsi, harga, berat_gram, stok,
            | tanggal_kadaluarsa, foto_path, is_active, created_at, updated_at
            */
            $products = [
                [
                    'product_category_id' => $productCategoryIds['Skincare Wajah'],
                    'nama' => 'Gentle Cleanser pH Balance',
                    'deskripsi' => 'Pembersih wajah lembut untuk pemakaian harian, bantu bersihkan tanpa bikin kulit ketarik.',
                    'harga' => 85000,
                    'berat_gram' => 120,
                    'stok' => 50,
                    'tanggal_kadaluarsa' => $now->copy()->addYears(2)->toDateString(),
                    'foto_path' => 'seed/products/gentle-cleanser.jpg',
                    'is_active' => true,
                ],
                [
                    'product_category_id' => $productCategoryIds['Skincare Wajah'],
                    'nama' => 'Hydrating Moisturizer Ceramide',
                    'deskripsi' => 'Melembapkan dan bantu perkuat skin barrier, cocok untuk kulit kering/kombinasi.',
                    'harga' => 135000,
                    'berat_gram' => 50,
                    'stok' => 35,
                    'tanggal_kadaluarsa' => $now->copy()->addYears(2)->toDateString(),
                    'foto_path' => 'seed/products/ceramide-moisturizer.jpg',
                    'is_active' => true,
                ],
                [
                    'product_category_id' => $productCategoryIds['Skincare Wajah'],
                    'nama' => 'Sunscreen SPF 50 PA++++',
                    'deskripsi' => 'Perlindungan UV untuk harian, tekstur ringan dan nyaman dipakai.',
                    'harga' => 125000,
                    'berat_gram' => 40,
                    'stok' => 40,
                    'tanggal_kadaluarsa' => $now->copy()->addYears(2)->toDateString(),
                    'foto_path' => 'seed/products/sunscreen-spf50.jpg',
                    'is_active' => true,
                ],
                [
                    'product_category_id' => $productCategoryIds['Acne Care'],
                    'nama' => 'Acne Spot Gel (Salicylic)',
                    'deskripsi' => 'Gel totol jerawat untuk bantu kempesin jerawat aktif dan mengurangi kemerahan.',
                    'harga' => 95000,
                    'berat_gram' => 15,
                    'stok' => 60,
                    'tanggal_kadaluarsa' => $now->copy()->addYears(2)->toDateString(),
                    'foto_path' => 'seed/products/acne-spot-gel.jpg',
                    'is_active' => true,
                ],
                [
                    'product_category_id' => $productCategoryIds['Body Care'],
                    'nama' => 'Body Lotion Bright & Smooth',
                    'deskripsi' => 'Melembapkan kulit tubuh dan bantu membuat tekstur lebih halus.',
                    'harga' => 99000,
                    'berat_gram' => 200,
                    'stok' => 30,
                    'tanggal_kadaluarsa' => $now->copy()->addYears(2)->toDateString(),
                    'foto_path' => 'seed/products/body-lotion.jpg',
                    'is_active' => true,
                ],
            ];

            foreach ($products as $p) {
                // biar aman, kalau ada nama sama (seed ulang) -> skip
                $exists = DB::table('products')->where('nama', $p['nama'])->first();
                if ($exists) continue;

                DB::table('products')->insert([
                    ...$p,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 4) SEED TREATMENTS (5 DATA)
            |--------------------------------------------------------------------------
            | Kolom wajib sesuai ERD:
            | treatment_category_id, nama, deskripsi, harga, durasi_menit, foto_path, is_active, created_at, updated_at
            */
            $treatments = [
                [
                    'treatment_category_id' => $treatmentCategoryIds['Facial'],
                    'nama' => 'Basic Facial Deep Cleansing',
                    'deskripsi' => 'Facial pembersihan dasar: cleansing, eksfoliasi ringan, ekstraksi ringan (opsional), dan soothing.',
                    'harga' => 180000,
                    'durasi_menit' => 60,
                    'foto_path' => 'seed/treatments/basic-facial.jpg',
                    'is_active' => true,
                ],
                [
                    'treatment_category_id' => $treatmentCategoryIds['Facial'],
                    'nama' => 'Acne Facial Control',
                    'deskripsi' => 'Facial untuk kulit berjerawat: fokus kontrol minyak, calming, dan edukasi aftercare.',
                    'harga' => 220000,
                    'durasi_menit' => 70,
                    'foto_path' => 'seed/treatments/acne-facial.jpg',
                    'is_active' => true,
                ],
                [
                    'treatment_category_id' => $treatmentCategoryIds['Peeling'],
                    'nama' => 'Chemical Peeling Brightening',
                    'deskripsi' => 'Peeling untuk bantu mencerahkan, meratakan warna kulit, dan memperbaiki tekstur.',
                    'harga' => 350000,
                    'durasi_menit' => 45,
                    'foto_path' => 'seed/treatments/chemical-peel.jpg',
                    'is_active' => true,
                ],
                [
                    'treatment_category_id' => $treatmentCategoryIds['Laser & Device'],
                    'nama' => 'Laser Rejuvenation',
                    'deskripsi' => 'Perawatan laser untuk peremajaan: bantu tampilan pori dan tone kulit lebih merata (sesuai indikasi dokter).',
                    'harga' => 650000,
                    'durasi_menit' => 60,
                    'foto_path' => 'seed/treatments/laser-rejuvenation.jpg',
                    'is_active' => true,
                ],
                [
                    'treatment_category_id' => $treatmentCategoryIds['Laser & Device'],
                    'nama' => 'Microneedling Skin Repair',
                    'deskripsi' => 'Microneedling untuk bantu tekstur, bekas jerawat ringan, dan regenerasi kulit (sesuai kondisi).',
                    'harga' => 550000,
                    'durasi_menit' => 60,
                    'foto_path' => 'seed/treatments/microneedling.jpg',
                    'is_active' => true,
                ],
            ];

            foreach ($treatments as $t) {
                $exists = DB::table('treatments')->where('nama', $t['nama'])->first();
                if ($exists) continue;

                DB::table('treatments')->insert([
                    ...$t,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }
}
