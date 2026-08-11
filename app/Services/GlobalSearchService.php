<?php

namespace App\Services;

use App\Models\Appointment;

use App\Models\Dokter;
use App\Models\Order;
use App\Models\Pasien;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\RekamMedis;
use App\Models\Terapis;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Centralized Global Search untuk admin panel.
 *
 * Mendukung pencarian lintas entity:
 * Pasien, Treatment, Produk, Appointment, Transaksi (Order),
 * Dokter, Terapis, Rekam Medis, Promo, Menu admin.
 *
 * Format response: array of groups, dengan struktur:
 *   - type: string (label kategori)
 *   - items: array of {type, title, subtitle, url, icon, badge?}
 */
class GlobalSearchService
{
    /** Limit per group default. */
    public const DEFAULT_LIMIT = 5;

    public function __construct(public string $query = '', public int $limit = self::DEFAULT_LIMIT)
    {
        $this->query = trim($query);
    }

    /** Run all search and return grouped results. */
    public function search(): array
    {
        if (mb_strlen($this->query) < 2) {
            return [
                'query' => $this->query,
                'groups' => [],
                'total' => 0,
            ];
        }

        $groups = collect([
            $this->searchPatients(),
            $this->searchTreatments(),
            $this->searchProducts(),
            $this->searchAppointments(),
            $this->searchOrders(),
            $this->searchDoctors(),
            $this->searchTherapists(),
            $this->searchMedicalRecords(),
            $this->searchPromotions(),

            $this->searchMenu(),
        ])->filter(fn ($g) => !empty($g['items']))
          ->values()
          ->toArray();

        $total = collect($groups)->sum(fn ($g) => count($g['items']));

        return [
            'query' => $this->query,
            'groups' => $groups,
            'total' => $total,
        ];
    }

    protected function searchPatients(): array
    {
        $q = $this->query;
        $rows = Pasien::query()
            ->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('no_rekam_medis', 'like', "%{$q}%")
                  ->orWhere('no_telepon', 'like', "%{$q}%");
            })
            ->orderBy('nama')
            ->limit($this->limit)
            ->get(['pasien_id', 'nama', 'no_rekam_medis', 'no_telepon']);

        return [
            'type' => 'Pasien',
            'icon' => 'users',
            'items' => $rows->map(fn ($p) => [
                'type' => 'patient',
                'title' => $p->nama ?? '—',
                'subtitle' => $p->no_rekam_medis
                    ? ('No. RM: ' . $p->no_rekam_medis)
                    : ($p->no_telepon ? ('Telp: ' . $p->no_telepon) : 'Tanpa nomor RM'),
                'url' => url("/admin/pasiens/{$p->pasien_id}/edit"),
                'badge' => null,
                'initial' => mb_strtoupper(mb_substr($p->nama ?? '?', 0, 1)),
            ])->toArray(),
        ];
    }

    protected function searchTreatments(): array
    {
        $q = $this->query;
        $rows = Treatment::query()
            ->with('category')
            ->where('nama', 'like', "%{$q}%")
            ->orderBy('nama')
            ->limit($this->limit)
            ->get(['treatment_id', 'nama', 'harga', 'durasi_menit', 'is_active', 'treatment_category_id']);

        return [
            'type' => 'Treatment',
            'icon' => 'sparkles',
            'items' => $rows->map(fn ($t) => [
                'type' => 'treatment',
                'title' => $t->nama,
                'subtitle' => 'Rp ' . number_format($t->harga, 0, ',', '.') . ' • ' . ($t->durasi_menit ? "{$t->durasi_menit} menit" : '—') . ($t->category ? " • {$t->category->nama}" : ''),
                'url' => url("/admin/treatments/{$t->treatment_id}/edit"),
                'badge' => $t->is_active ? null : 'Nonaktif',
                'initial' => mb_strtoupper(mb_substr($t->nama, 0, 1)),
            ])->toArray(),
        ];
    }

    protected function searchProducts(): array
    {
        $q = $this->query;
        $rows = Product::query()
            ->with('category')
            ->where('nama', 'like', "%{$q}%")
            ->orderBy('nama')
            ->limit($this->limit)
            ->get(['product_id', 'nama', 'harga', 'stok', 'is_active', 'product_category_id']);

        return [
            'type' => 'Produk',
            'icon' => 'shopping-bag',
            'items' => $rows->map(fn ($p) => [
                'type' => 'product',
                'title' => $p->nama,
                'subtitle' => 'Rp ' . number_format($p->harga, 0, ',', '.') . ' • Stok ' . ($p->stok ?? 0) . ($p->category ? " • {$p->category->nama}" : ''),
                'url' => url("/admin/products/{$p->product_id}/edit"),
                'badge' => $p->is_active ? ($p->stok < 5 ? 'Stok rendah' : null) : 'Nonaktif',
                'initial' => mb_strtoupper(mb_substr($p->nama, 0, 1)),
            ])->toArray(),
        ];
    }

    protected function searchAppointments(): array
    {
        $q = $this->query;

        // Search by appointment_id (kalau angka), atau by pasien name, treatment name
        $rows = Appointment::query()
            ->with(['pasien', 'dokter', 'terapis', 'items.treatment'])
            ->where(function ($w) use ($q) {
                $w->whereHas('pasien', fn ($pq) => $pq->where('name', 'like', "%{$q}%"))
                  ->orWhereHas('items.treatment', fn ($tq) => $tq->where('nama', 'like', "%{$q}%"));

                if (is_numeric($q)) {
                    $w->orWhere('appointment_id', (int) $q);
                }
                // Match APT-XXXX format
                if (preg_match('/^apt-?(\d+)$/i', $q, $m)) {
                    $w->orWhere('appointment_id', (int) $m[1]);
                }
            })
            ->orderByDesc('tanggal_jam')
            ->limit($this->limit)
            ->get();

        return [
            'type' => 'Janji Temu',
            'icon' => 'calendar',
            'items' => $rows->map(function ($a) {
                $tgl = $a->tanggal_jam instanceof Carbon ? $a->tanggal_jam : Carbon::parse($a->tanggal_jam);
                $petugas = $a->jenis === 'treatment'
                    ? ($a->terapis?->name ?? '—')
                    : ($a->dokter?->name ?? '—');
                $kode = 'APT-' . str_pad($a->appointment_id, 4, '0', STR_PAD_LEFT);
                $pasien = $a->pasien?->name ?? '—';
                return [
                    'type' => 'appointment',
                    'title' => "{$kode} • {$pasien}",
                    'subtitle' => $tgl->translatedFormat('d M Y, H:i') . ' • ' . $petugas,
                    'url' => url("/admin/appointments/{$a->appointment_id}/edit"),
                    'badge' => self::statusBadge($a->status),
                    'initial' => 'A',
                ];
            })->toArray(),
        ];
    }

    protected function searchOrders(): array
    {
        $q = $this->query;
        $rows = Order::query()
            ->with('user')
            ->where(function ($w) use ($q) {
                $w->where('kode_order', 'like', "%{$q}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$q}%"));
            })
            ->orderByDesc('created_at')
            ->limit($this->limit)
            ->get(['order_id', 'kode_order', 'user_id', 'order_type', 'status', 'grand_total', 'paid_at', 'created_at']);

        return [
            'type' => 'Transaksi',
            'icon' => 'receipt',
            'items' => $rows->map(function ($o) {
                $date = $o->paid_at ?? $o->created_at;
                $date = $date instanceof Carbon ? $date : Carbon::parse($date);
                return [
                    'type' => 'order',
                    'title' => $o->kode_order,
                    'subtitle' => ($o->user?->name ?? '—') . ' • Rp ' . number_format($o->grand_total, 0, ',', '.') . ' • ' . $date->translatedFormat('d M Y'),
                    'url' => url("/admin/orders/{$o->order_id}/edit"),
                    'badge' => self::orderStatusBadge($o->status),
                    'initial' => 'T',
                ];
            })->toArray(),
        ];
    }

    protected function searchDoctors(): array
    {
        $q = $this->query;
        $rows = Dokter::query()
            ->with('user')
            ->where(function ($w) use ($q) {
                $w->where('kode_dokter', 'like', "%{$q}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$q}%"));
            })
            ->orderBy('kode_dokter')
            ->limit($this->limit)
            ->get(['dokter_id', 'user_id', 'kode_dokter', 'status_praktik']);

        return [
            'type' => 'Dokter',
            'icon' => 'user-circle',
            'items' => $rows->map(fn ($d) => [
                'type' => 'doctor',
                'title' => $d->user?->name ?? '—',
                'subtitle' => $d->kode_dokter,
                'url' => url("/admin/dokters/{$d->dokter_id}/edit"),
                'badge' => $d->status_praktik === 'aktif' ? null : 'Nonaktif',
                'initial' => mb_strtoupper(mb_substr($d->user?->name ?? '?', 0, 1)),
            ])->toArray(),
        ];
    }

    protected function searchTherapists(): array
    {
        $q = $this->query;
        $rows = Terapis::query()
            ->with('user')
            ->where(function ($w) use ($q) {
                $w->where('kode_terapis', 'like', "%{$q}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$q}%"));
            })
            ->orderBy('kode_terapis')
            ->limit($this->limit)
            ->get(['terapis_id', 'user_id', 'kode_terapis', 'status_kerja']);

        return [
            'type' => 'Terapis',
            'icon' => 'user-group',
            'items' => $rows->map(fn ($t) => [
                'type' => 'therapist',
                'title' => $t->user?->name ?? '—',
                'subtitle' => $t->kode_terapis,
                'url' => url("/admin/terapis/{$t->terapis_id}/edit"),
                'badge' => $t->status_kerja === 'aktif' ? null : 'Nonaktif',
                'initial' => mb_strtoupper(mb_substr($t->user?->name ?? '?', 0, 1)),
            ])->toArray(),
        ];
    }

    protected function searchMedicalRecords(): array
    {
        $q = $this->query;
        if (!class_exists(RekamMedis::class)) {
            return ['type' => 'Rekam Medis', 'icon' => 'document-text', 'items' => []];
        }

        try {
            $rows = RekamMedis::query()
                ->with(['pasien'])
                ->where(function ($w) use ($q) {
                    $w->whereHas('pasien', fn ($pq) => $pq->where('nama', 'like', "%{$q}%"));
                    if (is_numeric($q)) {
                        $w->orWhere('rekam_medis_id', (int) $q);
                    }
                })
                ->orderByDesc('created_at')
                ->limit($this->limit)
                ->get();
        } catch (\Throwable $e) {
            return ['type' => 'Rekam Medis', 'icon' => 'document-text', 'items' => []];
        }

        return [
            'type' => 'Rekam Medis',
            'icon' => 'document-text',
            'items' => $rows->map(function ($r) {
                $created = $r->created_at instanceof Carbon ? $r->created_at : Carbon::parse($r->created_at);
                $kode = 'RM-' . str_pad($r->rekam_medis_id ?? $r->id ?? 0, 4, '0', STR_PAD_LEFT);
                return [
                    'type' => 'medical_record',
                    'title' => "{$kode} • " . ($r->pasien?->nama ?? '—'),
                    'subtitle' => $created->translatedFormat('d M Y'),
                    'url' => url("/admin/rekam-medis/" . ($r->rekam_medis_id ?? $r->id)),
                    'badge' => null,
                    'initial' => 'R',
                ];
            })->toArray(),
        ];
    }

    protected function searchPromotions(): array
    {
        $q = $this->query;
        $rows = Promotion::query()
            ->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('kode', 'like', "%{$q}%");
            })
            ->orderByDesc('created_at')
            ->limit($this->limit)
            ->get(['promotion_id', 'nama', 'kode', 'jenis', 'diskon_persen', 'diskon_nominal', 'is_active']);

        return [
            'type' => 'Promo',
            'icon' => 'ticket',
            'items' => $rows->map(function ($p) {
                if ($p->jenis === 'persen' && $p->diskon_persen) {
                    $diskon = $p->diskon_persen . '%';
                } elseif ($p->diskon_nominal) {
                    $diskon = 'Rp ' . number_format($p->diskon_nominal, 0, ',', '.');
                } else {
                    $diskon = '—';
                }
                return [
                    'type' => 'promo',
                    'title' => $p->nama,
                    'subtitle' => "Kode: {$p->kode} • Diskon {$diskon}",
                    'url' => url("/admin/promotions/{$p->promotion_id}/edit"),
                    'badge' => $p->is_active ? null : 'Nonaktif',
                    'initial' => '%',
                ];
            })->toArray(),
        ];
    }

    /** Static menu / halaman admin. */
    protected function searchMenu(): array
    {
        $q = mb_strtolower($this->query);
        $menus = [
            ['title' => 'Dashboard',                'desc' => 'Halaman utama panel admin',           'url' => url('/admin'),                                'keywords' => 'dashboard dasbor home overview ringkasan'],
            ['title' => 'Janji Temu',               'desc' => 'Daftar appointment pasien',           'url' => url('/admin/appointments'),                   'keywords' => 'appointment janji temu booking jadwal'],
            ['title' => 'Treatment',                'desc' => 'Master data treatment',                'url' => url('/admin/treatments'),                     'keywords' => 'treatment perawatan'],
            ['title' => 'Kategori Treatment',       'desc' => 'Master kategori treatment',            'url' => url('/admin/treatment-categories'),           'keywords' => 'kategori treatment category'],
            ['title' => 'Produk',                   'desc' => 'Master data produk',                   'url' => url('/admin/products'),                       'keywords' => 'produk product katalog'],
            ['title' => 'Kategori Produk',          'desc' => 'Master kategori produk',               'url' => url('/admin/product-categories'),             'keywords' => 'kategori produk category'],

            ['title' => 'Transaksi',                'desc' => 'Daftar order & pembayaran',            'url' => url('/admin/orders'),                         'keywords' => 'transaksi order pembayaran invoice'],
            ['title' => 'Pasien',                   'desc' => 'Master data pasien',                   'url' => url('/admin/pasiens'),                        'keywords' => 'pasien patient member pelanggan'],
            ['title' => 'Dokter',                   'desc' => 'Master data dokter',                   'url' => url('/admin/dokters'),                        'keywords' => 'dokter doctor'],
            ['title' => 'Terapis',                  'desc' => 'Master data terapis',                  'url' => url('/admin/terapis'),                        'keywords' => 'terapis therapist beautician'],
            ['title' => 'Rekam Medis',              'desc' => 'Catatan medis pasien',                 'url' => url('/admin/rekam-medis'),                    'keywords' => 'rekam medis medical record'],
            ['title' => 'Pra-Konsultasi',           'desc' => 'Form pra-konsultasi pasien',           'url' => url('/admin/patient-forms'),                  'keywords' => 'pra konsultasi patient form'],
            ['title' => 'Promo',                    'desc' => 'Voucher & diskon',                     'url' => url('/admin/promotions'),                     'keywords' => 'promo promotion voucher diskon kupon'],
            ['title' => 'Jadwal Praktik',           'desc' => 'Jadwal praktik dokter',                'url' => url('/admin/jadwal-praktiks'),                'keywords' => 'jadwal praktik schedule'],
            ['title' => 'Forum Tanya Jawab',        'desc' => 'Forum diskusi pasien',                 'url' => url('/admin/forum-questions'),                'keywords' => 'forum tanya jawab question'],
            ['title' => 'Laporan Keuangan',         'desc' => 'Ringkasan pendapatan',                 'url' => url('/admin/reports/finance'),                'keywords' => 'laporan finance keuangan revenue'],
            ['title' => 'Laporan Penjualan Produk', 'desc' => 'Penjualan produk per periode',         'url' => url('/admin/reports/product-sales'),          'keywords' => 'laporan produk penjualan sales'],
            ['title' => 'Laporan Treatment',        'desc' => 'Penjualan treatment per periode',      'url' => url('/admin/reports/treatment-sales'),        'keywords' => 'laporan treatment penjualan'],
            ['title' => 'Laporan Kinerja Dokter',   'desc' => 'Performa dokter & komisi',             'url' => url('/admin/reports/doctor-performance'),     'keywords' => 'laporan dokter kinerja komisi'],
            ['title' => 'Laporan Kinerja Terapis',  'desc' => 'Performa terapis & komisi',            'url' => url('/admin/reports/therapist-performance'),  'keywords' => 'laporan terapis kinerja komisi'],
            ['title' => 'Profil Saya',              'desc' => 'Pengaturan profil admin',              'url' => url('/admin/profile'),                        'keywords' => 'profil profile akun setting'],
        ];

        $matches = collect($menus)
            ->filter(fn ($m) =>
                str_contains(mb_strtolower($m['title']), $q) ||
                str_contains(mb_strtolower($m['desc']), $q) ||
                str_contains(mb_strtolower($m['keywords']), $q)
            )
            ->take($this->limit)
            ->values();

        return [
            'type' => 'Menu',
            'icon' => 'squares-2x2',
            'items' => $matches->map(fn ($m) => [
                'type' => 'menu',
                'title' => $m['title'],
                'subtitle' => $m['desc'],
                'url' => $m['url'],
                'badge' => null,
                'initial' => '#',
            ])->toArray(),
        ];
    }

    /** Map appointment status → badge text for display. */
    protected static function statusBadge(?string $status): ?string
    {
        return match ($status) {
            'disetujui' => 'Akan Datang',
            'menunggu_pembayaran' => 'Belum Bayar',
            'selesai' => 'Selesai',
            'batal', 'tidak_hadir' => 'Batal',
            default => null,
        };
    }

    protected static function orderStatusBadge(?string $status): ?string
    {
        return match ($status) {
            'sudah_dibayar' => 'Sudah Dibayar',
            'menunggu_pembayaran' => 'Menunggu',
            'dibatalkan', 'batal' => 'Batal',
            'expired' => 'Kadaluarsa',
            default => $status ? ucfirst($status) : null,
        };
    }
}
