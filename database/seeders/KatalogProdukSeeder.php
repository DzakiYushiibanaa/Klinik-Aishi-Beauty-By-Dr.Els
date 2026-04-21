<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KatalogProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        // Kadaluarsa 2 tahun kedepan dari sekarang
        $expiredDate = $now->copy()->addYears(2)->toDateString();

        // Ambil kategori pertama sebagai default
        $category = DB::table('product_categories')->first();
        if (!$category) {
            $catId = DB::table('product_categories')->insertGetId([
                'nama' => 'Skincare Wajah',
                'deskripsi' => 'Produk perawatan wajah',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], 'product_category_id');
        }
        else {
            $catId = $category->product_category_id;
        }

        $products = [
            [
                'nama' => 'Facial Wash AHA',
                'deskripsi' => 'Kandungan: AHA dan Betaine. Manfaat: Meningkatkan tekstur kulit, mengatasi kulit kusam, dan meningkatkan kandungan kolagen. Detail: 60 ml | BPOM: NA 18221206004.',
                'harga' => 100000,
                'berat_gram' => 60,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'Facial Foam Acne Care',
                'deskripsi' => 'Kandungan: Salicylic Acid dan Betaine. Manfaat: Mengatasi jerawat, mengurangi peradangan, dan memperbaiki tanda penuaan. Detail: 60 ml | BPOM: NA 18221206069.',
                'harga' => 100000,
                'berat_gram' => 60,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'Face Toner AHA',
                'deskripsi' => 'Kandungan: AHA dan Witch Hazel. Manfaat: Mengencangkan pori-pori, menyegarkan kulit, dan meningkatkan tekstur kulit. Detail: 60 ml | BPOM: NA 18221206068.',
                'harga' => 100000,
                'berat_gram' => 60,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'Acne Face Toner',
                'deskripsi' => 'Kandungan: Salicylic Acid, Witch Hazel, dan Collagen. Manfaat: Eksfoliasi kulit, mengatur produksi minyak, mengurangi hiperpigmentasi, dan mengencangkan pori. Detail: 60 ml | BPOM: NA 18220109233.',
                'harga' => 100000,
                'berat_gram' => 60,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => '24K AA+ Whitening Serum',
                'deskripsi' => 'Kandungan: Vitamin C (Generasi terbaru/GOV C), Unshiu Peel, dan Nano Partikel Gold. Manfaat: Mencerahkan kulit, melindungi dari radikal bebas, dan membuat kulit lebih sehat. Detail: 20 ml | BPOM: NA 18231902477.',
                'harga' => 100000,
                'berat_gram' => 20,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'Platinum Acne Serum',
                'deskripsi' => 'Kandungan: Vitamin C (GOV C), Unshiu Peel, dan Nano Partikel Silver. Manfaat: Mencerahkan kulit dan menghambat bakteri penyebab jerawat. Detail: BPOM: NA 18230110781.',
                'harga' => 100000,
                'berat_gram' => 20,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'Whitening Daily Cream',
                'deskripsi' => 'Kandungan: Vitamin B3 (Niacinamide), Vitamin C & E, dan Defensil Plus. Manfaat: Mencerahkan kulit, memperbaiki skin barrier, anti-inflamasi, dan melindungi dari kerusakan lingkungan. Detail: BPOM: NA 18231902489.',
                'harga' => 100000,
                'berat_gram' => 15,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'Whitening CC Glow',
                'deskripsi' => 'Kandungan: Vitamin B3 (Niacinamide), Vitamin C & E, dan Squalane. Manfaat: Mencerahkan kulit, memperbaiki barrier kulit, mengunci kelembapan, dan mengurangi kemerahan. Detail: BPOM: NA 18230300729.',
                'harga' => 100000,
                'berat_gram' => 15,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'UV Protect White',
                'deskripsi' => 'Kandungan: Vitamin B3 (Niacinamide), Vitamin C & E, dan Squalane. Manfaat: Perlindungan tanpa white cast, oil free, tidak menyumbat pori, dan mencerahkan kulit. Detail: BPOM: NA 18221701648.',
                'harga' => 100000,
                'berat_gram' => 15,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'Liposome Glow Night Cream',
                'deskripsi' => 'Kandungan: Q10, Niacinamide, dan Aloe Vera. Manfaat: Anti-aging, antioksidan, menjaga skin barrier, dan mengurangi pigmentasi. Detail: 13 g | BPOM: NA 18240102642.',
                'harga' => 100000,
                'berat_gram' => 13,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'S-DNA Mochiskin Facial Wash',
                'deskripsi' => 'Kandungan: AHA. Manfaat: Mencerahkan kulit dan memperbaiki skin barrier. Detail: BPOM: NA 18241206372.',
                'harga' => 100000,
                'berat_gram' => 60,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'S-DNA Mochiskin Serum with Pitera',
                'deskripsi' => 'Kandungan: S-DNA, Pitera, Snail Filtrate, Niacinamide, Poria Cocos Ext, dan Tranexamic Acid. Manfaat: Menstimulasi produksi kolagen, mencerahkan kulit, menghidrasi, dan memperbaiki kerusakan kulit. Detail: 20 ml | BPOM: NA 18241901391.',
                'harga' => 100000,
                'berat_gram' => 20,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'S-DNA Mochiskin Daily Gel',
                'deskripsi' => 'Kandungan: S-DNA, 5x Ceramide, Centella, Collagen, Tranexamic Acid, dan Q10. Manfaat: Merangsang regenerasi sel, mengatasi melasma, mengunci kelembapan, dan mencegah penuaan dini. Detail: BPOM: NA 18240107735.',
                'harga' => 100000,
                'berat_gram' => 20,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
            [
                'nama' => 'Mochiskin Powder',
                'deskripsi' => 'Kandungan: Squalane, TiO2, Octinoxate, dan Magnesium Myristate. Manfaat: Memberikan hasil matte, menahan riasan, melembapkan, dan melindungi dari sinar UV/UVB. Detail: BPOM: NA 18251203013.',
                'harga' => 100000,
                'berat_gram' => 15,
                'stok' => 100,
                'tanggal_kadaluarsa' => $expiredDate,
                'foto_path' => null,
            ],
        ];

        foreach ($products as $p) {
            $exists = DB::table('products')->where('nama', $p['nama'])->first();
            if ($exists) {
                continue;
            }

            DB::table('products')->insert([
                ...$p,
                'product_category_id' => $catId,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
