<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use App\Models\Dokter;
use App\Models\Terapis;
use App\Models\Apoteker;
use App\Models\Pasien;
use App\Models\TreatmentCategory;
use App\Models\ProductCategory;
use App\Models\Treatment;
use App\Models\Product;
use App\Models\Consultation;

class AishiBeautySeeder extends Seeder
{
    public function run(): void
    {
        // --- Section 1 — Roles & Permissions ---
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole      = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        $dokterRole     = Role::firstOrCreate(['name' => 'dokter',      'guard_name' => 'web']);
        $terapisRole    = Role::firstOrCreate(['name' => 'terapis',     'guard_name' => 'web']);
        $apotekerRole   = Role::firstOrCreate(['name' => 'apoteker',    'guard_name' => 'web']);
        $pasienRole     = Role::firstOrCreate(['name' => 'pasien',      'guard_name' => 'web']);

        $dokterRole->syncPermissions(
            Permission::where('name', 'like', '%rekam_medis%')
                ->orWhere('name', 'like', '%konsultasi%')
                ->orWhere('name', 'like', '%appointment%')
                ->orWhere('name', 'like', '%patient_form%')
                ->orWhere('name', 'like', '%forum%')
                ->get()
        );

        $terapisRole->syncPermissions(
            Permission::where('name', 'like', '%treatment%')
                ->orWhere('name', 'like', '%appointment%')
                ->orWhere('name', 'like', '%jadwal%')
                ->get()
        );

        $apotekerRole->syncPermissions(
            Permission::where('name', 'like', '%product%')
                ->orWhere('name', 'like', '%stock%')
                ->orWhere('name', 'like', '%order%')
                ->get()
        );

        $pasienRole->syncPermissions(
            Permission::where('name', 'like', '%appointment%')
                ->orWhere('name', 'like', '%forum%')
                ->orWhere('name', 'like', '%order%')
                ->get()
        );

        $superAdminRole->syncPermissions(Permission::all());
        $adminRole->syncPermissions(Permission::all());

        // --- Section 2 — Users (with profile tables) ---
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@aishi.test'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'phone'    => null,
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@aishi.test'],
            [
                'name'     => 'Admin Aishi',
                'password' => Hash::make('password'),
                'phone'    => null,
            ]
        );
        $admin->syncRoles(['admin']);

        $dokterUser = User::firstOrCreate(
            ['email' => 'dokter@aishi.test'],
            [
                'name'     => 'Dr. Budi Santoso',
                'password' => Hash::make('password'),
                'phone'    => '081200000001',
            ]
        );
        $dokterUser->syncRoles(['dokter']);
        Dokter::firstOrCreate(
            ['user_id' => $dokterUser->id],
            [
                'kode_dokter'    => 'DKR-SEED-001',
                'status_praktik' => 'aktif',
                'foto_profil_path' => null,
            ]
        );

        $terapisUser = User::firstOrCreate(
            ['email' => 'terapis@aishi.test'],
            [
                'name'     => 'Sari Rahayu',
                'password' => Hash::make('password'),
                'phone'    => '081200000002',
            ]
        );
        $terapisUser->syncRoles(['terapis']);
        Terapis::firstOrCreate(
            ['user_id' => $terapisUser->id],
            [
                'kode_terapis' => 'TRP-SEED-001',
                'status_kerja' => 'aktif',
                'foto_profil_path' => null,
            ]
        );

        $apotekerUser = User::firstOrCreate(
            ['email' => 'apoteker@aishi.test'],
            [
                'name'     => 'Apt. Rina Kusuma',
                'password' => Hash::make('password'),
                'phone'    => '081200000003',
            ]
        );
        $apotekerUser->syncRoles(['apoteker']);
        if(Schema::hasTable('apotekers')) {
            Apoteker::firstOrCreate(
                ['user_id' => $apotekerUser->id],
                [
                    'kode_apoteker' => 'APT-SEED-001',
                    'status_kerja' => 'aktif',
                    'foto_profil_path' => null,
                ]
            );
        }

        $pasienUser = User::firstOrCreate(
            ['email' => 'pasien@aishi.test'],
            [
                'name'     => 'Pasien Demo',
                'password' => Hash::make('password'),
                'phone'    => '081200000004',
            ]
        );
        $pasienUser->syncRoles(['pasien']);
        Pasien::firstOrCreate(
            ['user_id' => $pasienUser->id],
            [
                'nama'            => 'Pasien Demo',
                'tanggal_lahir'   => '1995-01-01',
                'jenis_kelamin'   => 'P',
                'no_telepon'      => '081200000004',
                'no_rekam_medis'  => 'RM-SEED-001',
                'is_member'       => false,
                'member_since'    => null,
                'foto_profil_path'=> null,
                'alamat'          => null,
                'kota'            => null,
                'provinsi'        => null,
            ]
        );

        // --- Section 3 — Treatment Categories ---
        $treatmentCategories = [
            ['nama' => 'Relaxation Facial',              'deskripsi' => 'Treatment wajah klasik yang membersihkan pori-pori dari sebum, komedo, dan sel kulit mati. Gerakan pijat lembut membantu merelaksasi otot wajah sekaligus melancarkan sirkulasi darah untuk kulit yang lebih segar dan bercahaya.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => true],
            ['nama' => 'Exfoliation Facial',             'deskripsi' => 'Treatment pengangkatan sel kulit mati dari permukaan kulit untuk menghaluskan tekstur, memperbaiki regenerasi sel, dan merangsang pertumbuhan sel kulit baru agar wajah tampak lebih cerah dan kencang.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => true],
            ['nama' => 'Non Exfoliation Super Facial',   'deskripsi' => 'Facial tanpa proses pengelupasan yang berfokus pada penetrasi serum, pengencangan, dan peremajaan kulit. Tindakan bersifat menenangkan dan tanpa rasa sakit, cocok untuk kulit sensitif atau yang baru selesai treatment.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => true],
            ['nama' => 'I2PL (Korean & Germany Machine)','deskripsi' => 'Teknologi IPL generasi terbaru dengan filter lebih efektif. Cahaya polikromatik menembus hingga lapisan dermis untuk mengatasi berbagai kondisi kulit — dari jerawat, flek, hingga rambut tidak diinginkan — tanpa efek samping.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => true],
            ['nama' => 'Pico Laser Treatment',           'deskripsi' => 'Teknologi laser NDYAG picosecond terbaru dengan pulsa lebih pendek dan cepat dari laser konvensional. Menghancurkan melanin, merangsang kolagen, dan meremajakan kulit — efektif untuk flek, bekas jerawat, tato, hingga pori besar.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => false],
            ['nama' => 'Laser Fractional CO2',           'deskripsi' => 'Laser CO2 fraksional yang menciptakan ribuan titik mikro di kulit untuk memicu penyembuhan dan regenerasi kolagen secara intensif. Efektif untuk bekas jerawat, stretch mark, pengencangan, hingga perawatan area intim.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => false],
            ['nama' => 'Bio PRP',                        'deskripsi' => 'Memanfaatkan Platelet Rich Plasma (PRP) dari darah sendiri yang kaya antioksidan dan faktor pertumbuhan. Diaplikasikan dengan Nano Dermapen untuk memperbaiki sel kulit rusak, menutup pori, dan merangsang pertumbuhan rambut baru.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => false],
            ['nama' => 'HIFU',                           'deskripsi' => 'High Intensity Focused Ultrasound — gelombang ultrasonik terfokus yang menembus lapisan SMAS untuk merangsang kolagen dari kedalaman. Mengencangkan kulit, mempertegas rahang, dan mengurangi lemak wajah tanpa operasi.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => false],
            ['nama' => 'DNA Salmon Oxyfacial',           'deskripsi' => 'Menggunakan DNA Salmon (PDRN) yang diaplikasikan tanpa jarum melalui alat Messogun Vanadium Titanium. Meregenerasi dan meremajakan kulit, meningkatkan produksi kolagen, serta memperbaiki lapisan kulit yang menipis dan kendur.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => true],
            ['nama' => 'Skin Booster',                   'deskripsi' => 'Perawatan injeksi atau infusi bahan aktif konsentrasi tinggi langsung ke lapisan kulit untuk hasil yang lebih intensif dan tahan lama. Mulai dari serum glow, stem cell, hingga botox halal untuk peremajaan dan pengencangan optimal.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => false],
            ['nama' => 'Injection with Needle Wings',    'deskripsi' => 'Teknik injeksi antioksidan langsung ke pembuluh darah menggunakan jarum sangat kecil untuk meminimalkan rasa nyeri. Menyembuhkan jerawat, mencerahkan, melembapkan kulit, dan membantu mengurangi lemak tubuh dari dalam.', 'is_active' => true, 'doctor_allowed' => true, 'therapist_allowed' => false],
        ];

        foreach ($treatmentCategories as $cat) {
            TreatmentCategory::firstOrCreate(['nama' => $cat['nama']], $cat);
        }

        // --- Section 4 — Product Categories ---
        $productCategories = [
            ['nama' => 'Cleanser',               'deskripsi' => 'Sabun untuk membersihkan wajah dari debu, kotoran, dan sisa minyak.',                                                                                                       'is_active' => true],
            ['nama' => 'Toner',                  'deskripsi' => 'Cairan penyegar yang dipakai setelah cuci muka untuk menyiapkan kulit menerima skincare selanjutnya dan bantu angkat sel kulit mati.',                                       'is_active' => true],
            ['nama' => 'Serum',                  'deskripsi' => 'Cairan konsentrat dengan bahan aktif tinggi untuk fokus mengatasi masalah kulit spesifik seperti jerawat atau kusam.',                                                       'is_active' => true],
            ['nama' => 'Day Cream & UV Protection','deskripsi' => 'Krim untuk melembapkan wajah di pagi/siang hari sekaligus melindungi kulit dari bahaya sinar matahari.',                                                                  'is_active' => true],
            ['nama' => 'Night Cream',            'deskripsi' => 'Krim pelembap yang dipakai sebelum tidur untuk menutrisi dan memperbaiki kulit secara maksimal sepanjang malam.',                                                            'is_active' => true],
            ['nama' => 'Powder',                 'deskripsi' => 'Produk riasan akhir untuk menahan minyak di wajah dan bikin tampilan kulit lebih halus.',                                                                                    'is_active' => true],
        ];

        foreach ($productCategories as $cat) {
            ProductCategory::firstOrCreate(['nama' => $cat['nama']], $cat);
        }

        // --- Section 5 — Treatments ---
        $categories = TreatmentCategory::get()->keyBy('nama');
        $relaxationFacial = $categories->get('Relaxation Facial');
        $exfoliationFacial = $categories->get('Exfoliation Facial');
        $nonExfoliation = $categories->get('Non Exfoliation Super Facial');
        $i2pl = $categories->get('I2PL (Korean & Germany Machine)');
        $pico = $categories->get('Pico Laser Treatment');
        $co2 = $categories->get('Laser Fractional CO2');
        $prp = $categories->get('Bio PRP');
        $hifu = $categories->get('HIFU');
        $dnaSalmon = $categories->get('DNA Salmon Oxyfacial');
        $skinBooster = $categories->get('Skin Booster');
        $injection = $categories->get('Injection with Needle Wings');

        $treatments = [
            // Relaxation Facial
            ['treatment_category_id' => $relaxationFacial->treatment_category_id, 'nama' => 'Deep Facial Reguler',    'deskripsi' => 'Pembersihan mendalam dengan masker nutrisi dan terapi cahaya PDT untuk menenangkan kulit, mematikan bakteri jerawat, dan meningkatkan regenerasi sel. Cocok untuk perawatan rutin mingguan.', 'harga' => 150000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $relaxationFacial->treatment_category_id, 'nama' => 'Intensive Facial Acne',  'deskripsi' => 'Facial khusus kulit berjerawat dengan totok wajah untuk melancarkan aliran energi, dilengkapi masker anti-inflamasi dan teknologi Omega untuk meredakan kemerahan serta mempercepat penyembuhan jerawat aktif.', 'harga' => 195000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $relaxationFacial->treatment_category_id, 'nama' => 'Intensive Facial Whitening', 'deskripsi' => 'Facial pencerah kulit dengan masker whitening yang menargetkan hiperpigmentasi dan kulit tidak merata. Totok wajah memperlancar aliran darah agar nutrisi pemutih terserap lebih optimal ke lapisan kulit.', 'harga' => 195000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $relaxationFacial->treatment_category_id, 'nama' => 'Intensive Facial Gold',   'deskripsi' => 'Facial mewah dengan masker mengandung partikel emas yang merangsang produksi kolagen dan elastin. Cocok untuk kulit yang membutuhkan kelembapan ekstra sekaligus efek anti-penuaan dini.', 'harga' => 195000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $relaxationFacial->treatment_category_id, 'nama' => 'Galvanic Facial',         'deskripsi' => 'Menggunakan arus listrik galvanik bertegangan rendah untuk mendorong penetrasi serum lebih dalam ke lapisan kulit (iontophoresis) dan membersihkan pori-pori secara menyeluruh melalui proses desincrustation.', 'harga' => 195000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $relaxationFacial->treatment_category_id, 'nama' => 'Acne Citro Facial',       'deskripsi' => 'Facial intensif dengan ampul konsentrat khusus acne yang mengandung bahan aktif antibakteri tinggi. Dikombinasikan dengan teknologi Omega untuk mempercepat penyembuhan jerawat parah dan mencegah bekas hitam.', 'harga' => 375000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $relaxationFacial->treatment_category_id, 'nama' => 'White C Facial',          'deskripsi' => 'Facial pencerah premium dengan ampul Vitamin C konsentrasi tinggi yang bekerja menghambat produksi melanin berlebih. Hasilnya kulit tampak lebih cerah, merata, dan bercahaya dalam satu sesi treatment.', 'harga' => 375000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $relaxationFacial->treatment_category_id, 'nama' => 'Collagen Facial',         'deskripsi' => 'Facial anti-aging dengan ampul kolagen murni yang membantu memulihkan elastisitas dan kekencangan kulit. Ideal untuk kulit yang mulai kendur, terasa tipis, atau menunjukkan tanda-tanda penuaan dini.', 'harga' => 375000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            
            // Exfoliation Facial
            ['treatment_category_id' => $exfoliationFacial->treatment_category_id, 'nama' => 'Microdiamond',    'deskripsi' => 'Eksfoliasi mekanik menggunakan ujung berlian mikro yang mengikis sel kulit mati tanpa bahan kimia. Aman untuk kulit sensitif, efektif menghaluskan tekstur dan mengecilkan tampilan pori-pori.', 'harga' => 150000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $exfoliationFacial->treatment_category_id, 'nama' => 'Microcrystal',    'deskripsi' => 'Eksfoliasi menggunakan semburan kristal halus yang mengangkat sel kulit mati lebih merata. Meningkatkan produksi kolagen dan membuat kulit tampak lebih muda serta bercahaya setelah treatment.', 'harga' => 250000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $exfoliationFacial->treatment_category_id, 'nama' => 'Peeling Whitening','deskripsi' => 'Peeling kimia dengan Lactic Acid yang mengangkat sel kulit mati sekaligus menghambat produksi melanin. Memberikan efek pencerah alami dan meratakan warna kulit secara bertahap.', 'harga' => 250000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $exfoliationFacial->treatment_category_id, 'nama' => 'Peeling Acne', 'deskripsi' => 'Peeling dengan Salicylic Acid yang bersifat lipofilik — mampu masuk ke dalam pori-pori berminyak untuk membersihkan penyumbatan dari dalam, mengurangi komedo, dan mencegah jerawat baru.', 'harga' => 250000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $exfoliationFacial->treatment_category_id, 'nama' => 'London Peel', 'deskripsi' => 'Kombinasi peeling kimia dan biostimulasi yang merangsang produksi kolagen alami kulit. Mengatasi bekas jerawat, flek, dan tekstur tidak rata sekaligus dengan hasil yang lebih optimal dibanding peeling biasa.', 'harga' => 550000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $exfoliationFacial->treatment_category_id, 'nama' => 'Snow White', 'deskripsi' => 'Paket facial tiga langkah dalam satu sesi: eksfoliasi, infusi serum pencerah, dan masker penenang. Dirancang untuk memberikan efek kulit cerah instan yang tahan lama.', 'harga' => 375000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $exfoliationFacial->treatment_category_id, 'nama' => 'Hydra Facial', 'deskripsi' => 'Eksfoliasi dan hidrasi serentak menggunakan alat hydradermabrasi yang menyalurkan serum aktif langsung ke kulit. Membersihkan, mengeksfoliasi, dan melembapkan dalam satu gerakan tanpa iritasi.', 'harga' => 375000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],

            // Non Exfoliation Super Facial
            ['treatment_category_id' => $nonExfoliation->treatment_category_id, 'nama' => 'Messotherapy', 'deskripsi' => 'Penyaluran serum anti-aging ke lapisan mesodermal kulit menggunakan teknologi mesotherapy. Membantu meremajakan kulit dari dalam, meningkatkan kelembapan, dan memperbaiki elastisitas secara bertahap.', 'harga' => 300000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $nonExfoliation->treatment_category_id, 'nama' => 'RF Wajah', 'deskripsi' => 'Gelombang radio frekuensi memanaskan lapisan dermis untuk merangsang produksi kolagen baru. Efektif mengencangkan kulit kendur, mempertegas kontur wajah, dan mengurangi kerutan halus tanpa prosedur invasif.', 'harga' => 300000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            
            // I2PL
            ['treatment_category_id' => $i2pl->treatment_category_id, 'nama' => 'I2PL Acne (full)', 'deskripsi' => 'Terapi cahaya intensitas tinggi yang menargetkan bakteri penyebab jerawat (P. acnes) di seluruh wajah. Mengurangi peradangan, mempercepat penyembuhan, dan meminimalkan risiko bekas jerawat.', 'harga' => 1250000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $i2pl->treatment_category_id, 'nama' => 'I2PL Rejuve', 'deskripsi' => 'Peremajaan kulit menyeluruh dengan merangsang produksi kolagen dan elastin di lapisan dermis. Mengurangi kerutan halus, mencerahkan kulit, dan memperbaiki tekstur secara keseluruhan.', 'harga' => 750000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],

            // Pico Laser Treatment
            ['treatment_category_id' => $pico->treatment_category_id, 'nama' => 'Laser Pico Pigmentation / Flek', 'deskripsi' => 'Menargetkan flek hitam, melasma, lentigo, dan freckles dengan memecah pigmen melanin menjadi partikel lebih kecil agar mudah diserap tubuh. Kulit tampak lebih merata dan cerah.', 'harga' => 1000000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $pico->treatment_category_id, 'nama' => 'Tatto Removal — Alis', 'deskripsi' => 'Menghapus tato alis (sulam/microblading) menggunakan laser pico yang memecah pigmen tinta secara presisi. Prosedur bertahap dengan hasil bertahap hingga tato memudar sempurna.', 'harga' => 600000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],

            // Laser Fractional CO2
            ['treatment_category_id' => $co2->treatment_category_id, 'nama' => 'Skin Tightening', 'deskripsi' => 'Mengencangkan kulit wajah yang kendur dengan merangsang produksi kolagen baru secara masif. Cocok untuk kulit yang mulai mengalami sagging atau kehilangan kekencangan akibat penuaan.', 'harga' => 950000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $co2->treatment_category_id, 'nama' => 'Wrinkle Fineline', 'deskripsi' => 'Menargetkan kerutan dan garis halus di wajah dengan meremajakan lapisan dermis. Laser CO2 merangsang pembentukan kolagen baru yang mengisi dan menghaluskan kerutan dari dalam kulit.', 'harga' => 900000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],

            // Bio PRP
            ['treatment_category_id' => $prp->treatment_category_id, 'nama' => 'Vampire Facial', 'deskripsi' => 'PRP dari darah sendiri diaplikasikan ke wajah menggunakan Nano Dermapen. Memperbaiki bekas jerawat (bopeng), mengecilkan pori, mencegah penuaan dini, dan membuat wajah glowing dari dalam secara alami.', 'harga' => 1250000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $prp->treatment_category_id, 'nama' => 'Hair Grow', 'deskripsi' => 'PRP diinjeksikan ke kulit kepala untuk merangsang folikel rambut yang mengalami kerontokan atau penipisan. Faktor pertumbuhan dalam PRP mengaktifkan kembali folikel yang tidak aktif untuk menumbuhkan rambut baru.', 'harga' => 900000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],

            // HIFU
            ['treatment_category_id' => $hifu->treatment_category_id, 'nama' => 'Chubby Cheek', 'deskripsi' => 'HIFU difokuskan pada area pipi untuk memecah sel lemak dan mengencangkan jaringan yang menyebabkan pipi terlihat tembam. Kontur wajah tampak lebih tirus dan tegas tanpa prosedur bedah.', 'harga' => 700000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $hifu->treatment_category_id, 'nama' => 'Double Chin', 'deskripsi' => 'Mengatasi dagu ganda (double chin) akibat timbunan lemak atau kulit kendur di bawah rahang. HIFU memecah lemak lokal dan mengencangkan kulit sehingga rahang tampak lebih tegas dan proporsional.', 'harga' => 600000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],

            // DNA Salmon Oxyfacial
            ['treatment_category_id' => $dnaSalmon->treatment_category_id, 'nama' => 'DNA Salmon Oxyfacial', 'deskripsi' => 'Menggunakan DNA Salmon (PDRN) yang diaplikasikan tanpa jarum melalui alat Messogun Vanadium Titanium. Meregenerasi dan meremajakan kulit, meningkatkan produksi kolagen, serta memperbaiki lapisan kulit yang menipis dan kendur.', 'harga' => 399000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],

            // Skin Booster
            ['treatment_category_id' => $skinBooster->treatment_category_id, 'nama' => 'CC Glow + Growth Serum', 'deskripsi' => 'Kombinasi CC Glow untuk efek kecantikan instan dengan growth serum yang merangsang regenerasi sel kulit jangka panjang. Kulit tampak glowing, merata, dan lebih sehat dari dalam sekaligus dari luar.', 'harga' => 1500000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $skinBooster->treatment_category_id, 'nama' => 'Salmon Booster', 'deskripsi' => 'PDRN dari DNA salmon diinjeksikan langsung ke kulit untuk hasil yang lebih cepat dan intensif dibanding oxyfacial. Meregenerasi sel, meningkatkan elastisitas, dan memberikan kelembapan mendalam dari lapisan dalam kulit.', 'harga' => 900000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],

            // Injection with Needle Wings
            ['treatment_category_id' => $injection->treatment_category_id, 'nama' => 'I.V. Bolus Whitening', 'deskripsi' => 'Infusi vitamin dan antioksidan pencerah (seperti Glutathione dan Vitamin C) langsung ke aliran darah untuk hasil yang lebih cepat dan merata dibanding topikal. Kulit tampak lebih cerah dari dalam setelah beberapa sesi.', 'harga' => 300000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
            ['treatment_category_id' => $injection->treatment_category_id, 'nama' => 'I.V. Bolus Collagen', 'deskripsi' => 'Kolagen hidrolisat disalurkan langsung ke pembuluh darah untuk mendukung elastisitas dan kelembapan kulit dari dalam. Lebih efektif diserap tubuh dibanding konsumsi kolagen oral karena masuk langsung ke sirkulasi.', 'harga' => 450000, 'durasi_menit' => 120, 'is_active' => true, 'foto_path' => null],
        ];

        foreach ($treatments as $t) {
            Treatment::firstOrCreate(['nama' => $t['nama']], $t);
        }

        // --- Section 6 — Consultations ---
        $consultations = [
            ['nama' => 'Konsultasi Awal',     'harga' => 125000, 'durasi_menit' => 60, 'is_active' => true],
            ['nama' => 'Konsultasi Lanjutan', 'harga' => 100000, 'durasi_menit' => 60, 'is_active' => true],
        ];

        foreach ($consultations as $c) {
            Consultation::firstOrCreate(['nama' => $c['nama']], $c);
        }

        // --- Section 7 — Products ---
        $productCats = ProductCategory::get()->keyBy('nama');
        
        $cleanser    = $productCats->get('Cleanser');
        $toner       = $productCats->get('Toner');
        $serum       = $productCats->get('Serum');
        $dayCream    = $productCats->get('Day Cream & UV Protection');
        $nightCream  = $productCats->get('Night Cream');
        $powder      = $productCats->get('Powder');

        $products = [
            // Cleanser
            ['product_category_id' => $cleanser->product_category_id,  'nama' => 'Facial Wash AHA',          'deskripsi' => 'Kandungan: AHA dan Betaine. Manfaat: Meningkatkan tekstur kulit, mengatasi kulit kusam, dan meningkatkan kandungan kolagen. Detail: 60 ml | BPOM: NA 18221206004.',    'harga' => 97000,  'berat_gram' => 60, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            ['product_category_id' => $cleanser->product_category_id,  'nama' => 'Facial Foam Acne Care',     'deskripsi' => 'Kandungan: Salicylic Acid dan Betaine. Manfaat: Mengatasi jerawat, mengurangi peradangan, dan memperbaiki tanda penuaan. Detail: 60 ml | BPOM: NA 18221206069.',   'harga' => 97000,  'berat_gram' => 60, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            ['product_category_id' => $cleanser->product_category_id,  'nama' => 'S-DNA Mochiskin Facial Wash','deskripsi' => 'Kandungan: AHA. Manfaat: Mencerahkan kulit dan memperbaiki skin barrier. Detail: BPOM: NA 18241206372.',                                                          'harga' => 120000, 'berat_gram' => 60, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            // Toner
            ['product_category_id' => $toner->product_category_id,     'nama' => 'Face Toner AHA',            'deskripsi' => 'Kandungan: AHA dan Witch Hazel. Manfaat: Mengencangkan pori-pori, menyegarkan kulit, dan meningkatkan tekstur kulit. Detail: 60 ml | BPOM: NA 18221206068.',       'harga' => 97000,  'berat_gram' => 60, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            ['product_category_id' => $toner->product_category_id,     'nama' => 'Acne Face Toner',           'deskripsi' => 'Kandungan: Salicylic Acid, Witch Hazel, dan Collagen. Manfaat: Eksfoliasi kulit, mengatur produksi minyak, mengurangi hiperpigmentasi, dan mengencangkan pori. Detail: 60 ml | BPOM: NA 18220109233.', 'harga' => 97000, 'berat_gram' => 60, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            // Serum
            ['product_category_id' => $serum->product_category_id,     'nama' => '24K AA+ Whitening Serum',   'deskripsi' => 'Kandungan: Vitamin C (GOV C), Unshiu Peel, dan Nano Partikel Gold. Manfaat: Mencerahkan kulit, melindungi dari radikal bebas, dan membuat kulit lebih sehat. Detail: 20 ml | BPOM: NA 18231902477.', 'harga' => 235000, 'berat_gram' => 20, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            ['product_category_id' => $serum->product_category_id,     'nama' => 'Platinum Acne Serum',       'deskripsi' => 'Kandungan: Vitamin C (GOV C), Unshiu Peel, dan Nano Partikel Silver. Manfaat: Mencerahkan kulit dan menghambat bakteri penyebab jerawat. Detail: BPOM: NA 18230110781.', 'harga' => 235000, 'berat_gram' => 20, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            ['product_category_id' => $serum->product_category_id,     'nama' => 'S-DNA Mochiskin Serum with Pitera', 'deskripsi' => 'Kandungan: S-DNA, Pitera, Snail Filtrate, Niacinamide, Poria Cocos Ext, dan Tranexamic Acid. Manfaat: Menstimulasi produksi kolagen, mencerahkan kulit, menghidrasi, dan memperbaiki kerusakan kulit. Detail: 20 ml | BPOM: NA 18241901391.', 'harga' => 285000, 'berat_gram' => 20, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            // Day Cream & UV Protection
            ['product_category_id' => $dayCream->product_category_id,  'nama' => 'Whitening Daily Cream',     'deskripsi' => 'Kandungan: Niacinamide, Vitamin C & E, dan Defensil Plus. Manfaat: Mencerahkan kulit, memperbaiki skin barrier, anti-inflamasi, dan melindungi dari kerusakan lingkungan. Detail: BPOM: NA 18231902489.', 'harga' => 135000, 'berat_gram' => 15, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            ['product_category_id' => $dayCream->product_category_id,  'nama' => 'Whitening CC Glow',         'deskripsi' => 'Kandungan: Niacinamide, Vitamin C & E, dan Squalane. Manfaat: Mencerahkan kulit, memperbaiki barrier kulit, mengunci kelembapan, dan mengurangi kemerahan. Detail: BPOM: NA 18230300729.', 'harga' => 135000, 'berat_gram' => 15, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            ['product_category_id' => $dayCream->product_category_id,  'nama' => 'UV Protect White',          'deskripsi' => 'Kandungan: Niacinamide, Vitamin C & E, dan Squalane. Manfaat: Perlindungan tanpa white cast, oil free, tidak menyumbat pori, dan mencerahkan kulit. Detail: BPOM: NA 18221701648.', 'harga' => 135000, 'berat_gram' => 15, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            ['product_category_id' => $dayCream->product_category_id,  'nama' => 'S-DNA Mochiskin Daily Gel',  'deskripsi' => 'Kandungan: S-DNA, 5x Ceramide, Centella, Collagen, Tranexamic Acid, dan Q10. Manfaat: Merangsang regenerasi sel, mengatasi melasma, mengunci kelembapan, dan mencegah penuaan dini. Detail: BPOM: NA 18240107735.', 'harga' => 180000, 'berat_gram' => 20, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            // Night Cream
            ['product_category_id' => $nightCream->product_category_id,'nama' => 'Liposome Glow Night Cream', 'deskripsi' => 'Kandungan: Q10, Niacinamide, dan Aloe Vera. Manfaat: Anti-aging, antioksidan, menjaga skin barrier, dan mengurangi pigmentasi. Detail: 13 g | BPOM: NA 18240102642.', 'harga' => 180000, 'berat_gram' => 13, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
            // Powder
            ['product_category_id' => $powder->product_category_id,    'nama' => 'Mochiskin Powder',          'deskripsi' => 'Kandungan: Squalane, TiO2, Octinoxate, dan Magnesium Myristate. Manfaat: Memberikan hasil matte, menahan riasan, melembapkan, dan melindungi dari sinar UV/UVB. Detail: BPOM: NA 18251203013.', 'harga' => 235000, 'berat_gram' => 15, 'stok' => 50, 'is_active' => true, 'foto_path' => null],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(['nama' => $p['nama']], $p);
        }
    }
}
