<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Treatment;
use App\Models\Product;
use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class DummyTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $pasien    = User::where('email', 'pasien@aishi.test')->first();
        $dokter    = User::where('email', 'dokter@aishi.test')->first();
        $admin     = User::where('email', 'admin@aishi.test')->first();
        $treatment = Treatment::where('nama', 'Deep Facial Reguler')->first();
        $product   = Product::where('nama', 'Facial Wash AHA')->first();
        $now       = Carbon::now();

        if ($pasien && $dokter && $treatment && $admin) {
            $appointment1 = Appointment::updateOrCreate(
                [
                    'user_id'     => $pasien->id,
                    'tanggal_jam' => $now->copy()->addDays(2)->format('Y-m-d 10:00:00'),
                ],
                [
                    'dokter_id'      => $dokter->id,
                    'dibuat_oleh_id' => $pasien->id,
                    'jenis'          => 'treatment',
                    'status'         => 'disetujui',
                ]
            );

            AppointmentItem::updateOrCreate(
                [
                    'appointment_id' => $appointment1->appointment_id,
                    'item_type'      => 'treatment',
                ],
                [
                    'treatment_id' => $treatment->treatment_id,
                    'qty'          => 1,
                ]
            );
        }

        if ($pasien && $product && $admin) {
            $order1 = Order::updateOrCreate(
                [
                    'kode_order' => 'ORD-SEED-' . $now->format('Ymd') . '-0001',
                ],
                [
                    'user_id'           => $pasien->id,
                    'order_type'        => 'produk',
                    'sumber'            => 'online',
                    'dibuat_oleh_id'    => $pasien->id,
                    'status'            => 'draft',
                    'subtotal'          => $product->harga,
                    'grand_total'       => $product->harga,
                    'total_weight_gram' => $product->berat_gram,
                ]
            );

            OrderItem::updateOrCreate(
                [
                    'order_id'   => $order1->order_id,
                    'item_type'  => 'product',
                    'product_id' => $product->product_id,
                ],
                [
                    'description'  => $product->nama,
                    'qty'          => 1,
                    'harga_satuan' => $product->harga,
                    'subtotal'     => $product->harga,
                ]
            );
        }
    }
}