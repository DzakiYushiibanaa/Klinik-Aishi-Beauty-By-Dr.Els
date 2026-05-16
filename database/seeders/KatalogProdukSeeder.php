<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        // Mapping nama produk -> nama file di public/assets/img_produk/
        $products = [
            ['nama' => 'Facial Wash AHA',                 'image' => 'facial wash AHA.png',           'berat' => 60, 'deskripsi' => 'Kandungan: AHA dan Betaine. Manfaat: Meningkatkan tekstur kulit, mengatasi kulit kusam, dan meningkatkan kandungan kolagen. Detail: 60 ml | BPOM: NA 18221206004.'],
            ['nama' => 'Facial Foam Acne Care',           'image' => 'facial foam acne care.png',     'berat' => 60, 'deskripsi' => 'Kandungan: Salicylic Acid dan Betaine. Manfaat: Mengatasi jerawat, mengurangi peradangan, dan memperbaiki tanda penuaan. Detail: 60 ml | BPOM: NA 18221206069.'],
            ['nama' => 'Face Toner AHA',                  'image' => 'face toner AHA.png',            'berat' => 60, 'deskripsi' => 'Kandungan: AHA dan Witch Hazel. Manfaat: Mengencangkan pori-pori, menyegarkan kulit, dan meningkatkan tekstur kulit. Detail: 60 ml | BPOM: NA 18221206068.'],
            ['nama' => 'Acne Face Toner',                 'image' => 'acne face toner.png',           'berat' => 60, 'deskripsi' => 'Kandungan: Salicylic Acid, Witch Hazel, dan Collagen. Manfaat: Eksfoliasi kulit, mengatur produksi minyak, mengurangi hiperpigmentasi, dan mengencangkan pori. Detail: 60 ml | BPOM: NA 18220109233.'],
            ['nama' => '24K AA+ Whitening Serum',         'image' => '24K AA+ whitening serum.png',   'berat' => 20, 'deskripsi' => 'Kandungan: Vitamin C (Generasi terbaru/GOV C), Unshiu Peel, dan Nano Partikel Gold. Manfaat: Mencerahkan kulit, melindungi dari radikal bebas, dan membuat kulit lebih sehat. Detail: 20 ml | BPOM: NA 18231902477.'],
            ['nama' => 'Platinum Acne Serum',             'image' => 'platinum acne serum.png',       'berat' => 20, 'deskripsi' => 'Kandungan: Vitamin C (GOV C), Unshiu Peel, dan Nano Partikel Silver. Manfaat: Mencerahkan kulit dan menghambat bakteri penyebab jerawat. Detail: BPOM: NA 18230110781.'],
            ['nama' => 'Whitening Daily Cream',           'image' => 'whitening daily cream.png',     'berat' => 15, 'deskripsi' => 'Kandungan: Vitamin B3 (Niacinamide), Vitamin C & E, dan Defensil Plus. Manfaat: Mencerahkan kulit, memperbaiki skin barrier, anti-inflamasi, dan melindungi dari kerusakan lingkungan. Detail: BPOM: NA 18231902489.'],
            ['nama' => 'Whitening CC Glow',               'image' => 'whitening cc glow.png',         'berat' => 15, 'deskripsi' => 'Kandungan: Vitamin B3 (Niacinamide), Vitamin C & E, dan Squalane. Manfaat: Mencerahkan kulit, memperbaiki barrier kulit, mengunci kelembapan, dan mengurangi kemerahan. Detail: BPOM: NA 18230300729.'],
            ['nama' => 'UV Protect White',                'image' => 'UV protect white.png',          'berat' => 15, 'deskripsi' => 'Kandungan: Vitamin B3 (Niacinamide), Vitamin C & E, dan Squalane. Manfaat: Perlindungan tanpa white cast, oil free, tidak menyumbat pori, dan mencerahkan kulit. Detail: BPOM: NA 18221701648.'],
            ['nama' => 'Liposome Glow Night Cream',       'image' => 'LIPOSOME GLOW.png',             'berat' => 13, 'deskripsi' => 'Kandungan: Q10, Niacinamide, dan Aloe Vera. Manfaat: Anti-aging, antioksidan, menjaga skin barrier, dan mengurangi pigmentasi. Detail: 13 g | BPOM: NA 18240102642.'],
            ['nama' => 'S-DNA Mochiskin Facial Wash',     'image' => 'S-DNA MOCHISKIN FACIAL WASH.png','berat' => 60, 'deskripsi' => 'Kandungan: AHA. Manfaat: Mencerahkan kulit dan memperbaiki skin barrier. Detail: BPOM: NA 18241206372.'],
            ['nama' => 'S-DNA Mochiskin Serum with Pitera','image' => 'S-DNA MOCHISKIN SERUM.png',    'berat' => 20, 'deskripsi' => 'Kandungan: S-DNA, Pitera, Snail Filtrate, Niacinamide, Poria Cocos Ext, dan Tranexamic Acid. Manfaat: Menstimulasi produksi kolagen, mencerahkan kulit, menghidrasi, dan memperbaiki kerusakan kulit. Detail: 20 ml | BPOM: NA 18241901391.'],
            ['nama' => 'S-DNA Mochiskin Daily Gel',       'image' => 'S-DNA MOCHISKIN DAILY GEL.png', 'berat' => 20, 'deskripsi' => 'Kandungan: S-DNA, 5x Ceramide, Centella, Collagen, Tranexamic Acid, dan Q10. Manfaat: Merangsang regenerasi sel, mengatasi melasma, mengunci kelembapan, dan mencegah penuaan dini. Detail: BPOM: NA 18240107735.'],
            ['nama' => 'Mochiskin Powder',                'image' => 'DR. ELS MOCHISKIN POWDER.png',  'berat' => 15, 'deskripsi' => 'Kandungan: Squalane, TiO2, Octinoxate, dan Magnesium Myristate. Manfaat: Memberikan hasil matte, menahan riasan, melembapkan, dan melindungi dari sinar UV/UVB. Detail: BPOM: NA 18251203013.'],
        ];

        // Pastikan folder products/ tersedia di disk public
        $disk = Storage::disk('public');
        if (! $disk->exists('products')) {
            $disk->makeDirectory('products');
        }

        foreach ($products as $p) {
            // Salin gambar ke storage public bila tersedia, untuk dipakai $foto_path standar.
            $fotoPath = $this->copyImage($p['image']);

            $exists = DB::table('products')->where('nama', $p['nama'])->first();

            if ($exists) {
                // Update foto_path bila masih kosong agar seed ulang aman.
                if (empty($exists->foto_path) && $fotoPath) {
                    DB::table('products')
                        ->where('product_id', $exists->product_id)
                        ->update([
                            'foto_path' => $fotoPath,
                            'updated_at' => $now,
                        ]);
                }
                continue;
            }

            DB::table('products')->insert([
                'nama'                => $p['nama'],
                'deskripsi'           => $p['deskripsi'],
                'harga'               => 100000,
                'berat_gram'          => $p['berat'],
                'stok'                => 100,
                'tanggal_kadaluarsa'  => $expiredDate,
                'foto_path'           => $fotoPath,
                'product_category_id' => $catId,
                'is_active'           => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }
    }

    /**
     * Salin gambar produk dari public/assets/img_produk/ ke storage/app/public/products/
     * dan kembalikan path relatif yang dipakai untuk foto_path.
     */
    private function copyImage(string $sourceFile): ?string
    {
        $sourcePath = public_path('assets/img_produk/' . $sourceFile);
        if (! file_exists($sourcePath)) {
            return null;
        }

        $ext      = pathinfo($sourceFile, PATHINFO_EXTENSION) ?: 'png';
        $slug     = preg_replace('/[^a-z0-9]+/i', '-', mb_strtolower(pathinfo($sourceFile, PATHINFO_FILENAME)));
        $slug     = trim($slug, '-');
        $destName = 'products/seed-' . $slug . '.' . $ext;
        $destPath = storage_path('app/public/' . $destName);

        if (! is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0755, true);
        }

        copy($sourcePath, $destPath);

        return $destName;
    }
}
