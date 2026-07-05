<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PatientForm;
use App\Models\PraKonsultasiRekomendasiItem;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class AppointmentController extends Controller
{
    public function selectType()
    {
        return view('appointment.select-type');
    }

    public function bookingTreatment(Request $request)
    {
        $user = Auth::user();

        if (!$user->phone || !$user->pasienProfile?->tanggal_lahir) {
            // Simpan URL tujuan ke session (persisten lintas request) supaya
            // setelah profil dilengkapi user bisa langsung kembali ke flow ini.
            session(['profile_required_redirect' => $request->fullUrl()]);

            // Tidak pakai flash 'error' karena UI sudah ditangani oleh modal
            // <x-profile-required-modal /> di dashboard. Toast error akan
            // duplikat dengan modal dan terkesan ribut.
            return redirect('/dashboard')
                ->with('active_tab', 'profile')
                ->with('show_profile_required_modal', true);
        }

        $treatments = Treatment::with('category')
            ->where('is_active', true)
            ->get();

        $categories = \App\Models\TreatmentCategory::where('is_active', true)
            ->has('treatments')
            ->orderBy('nama')
            ->get();

        $treatmentsJson = $treatments->map(function ($t) {
            return [
                'id' => $t->treatment_id,
                'category_id' => $t->treatment_category_id,
                'category_nama' => $t->category->nama ?? 'Uncategorized',
                'nama' => $t->nama,
                'deskripsi' => $t->deskripsi,
                'harga' => $t->harga,
                'durasi' => $t->durasi_menit,
                'foto' => $t->foto_path ? \Storage::url($t->foto_path) : '',
            ];
        })->values();

        return view('appointment.booking-treatment', [
            'treatments' => $treatments,
            'categories' => $categories,
            'user' => $user,
            'midtransClientKey' => config('midtrans.client_key'),
            'treatmentsJson' => $treatmentsJson,
        ]);
    }

    /**
     * API: Cek ketersediaan slot treatment pada tanggal tertentu
     */
    public function checkSlotAvailability(Request $request)
    {
        $request->validate(['tanggal' => 'required|date']);
        
        $slots = [
            '08:00 - 10:00' => '08:00',
            '10:30 - 12:30' => '10:30',
            '13:00 - 15:00' => '13:00',
            '15:30 - 17:30' => '15:30',
        ];
        
        $maxPerSlot = 2;
        $result = [];
        
        foreach ($slots as $label => $jamMulai) {
            $tanggalJam = Carbon::parse($request->tanggal . ' ' . $jamMulai);
            
            $booked = Appointment::where('tanggal_jam', $tanggalJam)
                ->where('jenis', 'treatment')
                ->whereNotIn('status', ['batal', 'tidak_hadir'])
                ->count();
            
            $result[] = [
                'value' => $label,
                'booked' => $booked,
                'max' => $maxPerSlot,
                'sisa' => max(0, $maxPerSlot - $booked),
                'penuh' => $booked >= $maxPerSlot,
            ];
        }
        
        return response()->json($result);
    }

    public function submitBookingTreatment(Request $request)
    {
        $request->validate([
            'treatments'      => 'required|array|min:1',
            'treatments.*.id' => 'required|exists:treatments,treatment_id',
            'tanggal'         => 'required|date|after_or_equal:today',
            'jam'             => 'required|string',
        ]);

        $user       = Auth::user();
        $jamMulai   = explode(' - ', $request->jam)[0];
        $tanggalJam = Carbon::parse($request->tanggal . ' ' . $jamMulai);

        // ─────────────────────────────────────────
        // 1. Validasi kapasitas slot (max 2 pasien)
        // ─────────────────────────────────────────
        $jumlahPasienDiSlot = Appointment::where('tanggal_jam', $tanggalJam)
            ->where('jenis', 'treatment')
            ->whereNotIn('status', ['batal', 'tidak_hadir'])
            ->count();

        if ($jumlahPasienDiSlot >= 2) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Maaf, slot jadwal ini sudah penuh (maks. 2 pasien). Silakan pilih jadwal lain.',
            ], 422);
        }

        // ─────────────────────────────────────────
        // 2. DB Transaction: Buat Appointment + Order + Order Items
        // ─────────────────────────────────────────
        $result = null;
        try {
            $result = DB::transaction(function () use ($user, $request, $tanggalJam) {

                // Cek ulang slot dalam transaksi (mencegah race condition)
                $jumlah = Appointment::where('tanggal_jam', $tanggalJam)
                    ->where('jenis', 'treatment')
                    ->whereNotIn('status', ['batal', 'tidak_hadir'])
                    ->lockForUpdate()
                    ->count();

                if ($jumlah >= 2) {
                    throw new \Exception('SLOT_PENUH');
                }

                // A. Buat Appointment
                $appointment = Appointment::create([
                    'user_id'            => $user->id,
                    'jenis'              => 'treatment',

                    'dibuat_oleh_id'     => $user->id,
                    'tanggal_jam'        => $tanggalJam,
                    'status'             => 'menunggu_pembayaran',
                    'payment_expired_at' => now()->addMinutes(15),
                    'catatan_pasien'     => 'Booking Treatment Online | Jam: ' . $request->jam,
                ]);

                // B. Buat Appointment Items & hitung total
                $totalHarga  = 0;
                $itemDetails = [];

                foreach ($request->treatments as $treatmentData) {
                    $treatment = Treatment::find($treatmentData['id']);
                    if ($treatment) {
                        AppointmentItem::create([
                            'appointment_id' => $appointment->appointment_id,
                            'item_type'      => 'treatment',
                            'treatment_id'   => $treatment->treatment_id,
                            'qty'            => 1,
                        ]);
                        $totalHarga += $treatment->harga;
                        $itemDetails[] = [
                            'id'       => 'TRT-' . $treatment->treatment_id,
                            'price'    => (int) $treatment->harga,
                            'quantity' => 1,
                            'name'     => substr($treatment->nama, 0, 50),
                        ];
                    }
                }

                // C. Buat Order
                $kodeOrder       = \App\Support\OrderCodeGenerator::next();
                $midtransOrderId = $kodeOrder . '-' . time();
                
                $order = Order::create([
                    'user_id'           => $user->id,
                    'kode_order'        => $kodeOrder,
                    'order_type'        => 'treatment',
                    'sumber'            => 'online',
                    'dibuat_oleh_id'    => $user->id,
                    'status'            => 'menunggu_pembayaran',
                    'midtrans_order_id' => $midtransOrderId,
                    'subtotal'          => $totalHarga,
                    'grand_total'       => $totalHarga,
                    'expired_at'        => now()->addMinutes(15),
                ]);

                // D. Buat Order Items (link ke appointment)
                foreach ($request->treatments as $treatmentData) {
                    $treatment = Treatment::find($treatmentData['id']);
                    if ($treatment) {
                        OrderItem::create([
                            'order_id'       => $order->order_id,
                            'appointment_id' => $appointment->appointment_id,
                            'treatment_id'   => $treatment->treatment_id,
                            'item_type'      => 'treatment',
                            'description'    => $treatment->nama,
                            'qty'            => 1,
                            'harga_satuan'   => $treatment->harga,
                            'subtotal'       => $treatment->harga,
                        ]);
                    }
                }

                return compact('appointment', 'order', 'itemDetails', 'totalHarga', 'midtransOrderId');
            });

        } catch (\Exception $e) {
            if ($e->getMessage() === 'SLOT_PENUH') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Maaf, slot jadwal ini baru saja penuh. Silakan pilih jadwal lain.',
                ], 422);
            }
            Log::error('Booking Treatment DB Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan booking. Silakan coba lagi.',
            ], 500);
        }

        // ─────────────────────────────────────────
        // 3. Generate Midtrans Snap Token (di luar transaksi)
        // ─────────────────────────────────────────
        try {
            MidtransConfig::$serverKey    = config('midtrans.server_key');
            MidtransConfig::$isProduction = config('midtrans.is_production');
            MidtransConfig::$isSanitized  = config('midtrans.is_sanitized');
            MidtransConfig::$is3ds        = config('midtrans.is_3ds');

            $params = [
                'transaction_details' => [
                    'order_id'     => $result['midtransOrderId'],
                    'gross_amount' => (int) $result['totalHarga'],
                ],
                'item_details'    => $result['itemDetails'],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email'      => $user->email,
                    'phone'      => $user->phone ?? '',
                ],
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s O'),
                    'unit'       => 'minutes',
                    'duration'   => 15,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            // Simpan snap_token ke order
            $result['order']->update(['snap_token' => $snapToken]);

            return response()->json([
                'status'         => 'success',
                'snap_token'     => $snapToken,
                'appointment_id' => $result['appointment']->appointment_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return response()->json([
                'status'         => 'success_no_payment',
                'message'        => 'Booking tersimpan, namun sistem pembayaran sedang gangguan. Silakan hubungi klinik.',
                'appointment_id' => $result['appointment']->appointment_id,
            ]);
        }
    }

    public function konsultasi(Request $request)
    {
        $user = Auth::user();
        
        // Pengecekan Kelengkapan Profil
        if (!$user->phone || !$user->pasienProfile?->tanggal_lahir) {
            session(['profile_required_redirect' => $request->fullUrl()]);

            // Tidak pakai flash 'error' — UI sudah ditangani oleh modal global.
            return redirect('/dashboard')
                ->with('active_tab', 'profile')
                ->with('show_profile_required_modal', true);
        }

        // Dummy data for Doctors (since we need them for Step 2)
        $doctors = User::role('dokter')
            ->with('dokterProfile')
            ->whereHas('dokterProfile', function($q) {
                $q->where('status_praktik', 'aktif');
            })
            ->get();

        // Get all active schedule
        $jadwals = \App\Models\JadwalPraktik::where('is_active', true)->get();

        return view('appointment.konsultasi', [
            'doctors' => $doctors,
            'jadwals' => $jadwals,
            'user' => $user
        ]);
    }

    public function submitKonsultasi(Request $request)
    {
        $request->validate([
            'jenis' => 'required|string',
            'dokter_id' => 'required|exists:users,id',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam' => 'required|string',
        ]);

        $user = Auth::user();

        $tanggalJam = $request->tanggal . ' ' . str_replace('.', ':', substr($request->jam, 0, 5)) . ':00';

        // ─────────────────────────────────────────
        // 1. Cek apakah slot ini sudah diambil dokter+jam yg sama
        //    (1 dokter pada tanggal_jam yang sama hanya untuk 1 pasien)
        // ─────────────────────────────────────────
        $alreadyTaken = Appointment::where('dokter_id', $request->dokter_id)
            ->where('tanggal_jam', $tanggalJam)
            ->where('jenis', 'konsultasi')
            ->whereNotIn('status', ['batal', 'tidak_hadir'])
            ->exists();

        if ($alreadyTaken) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Slot ini baru saja diambil pasien lain. Silakan pilih jam lain.',
            ], 422);
        }

        try {
            $appointment = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $request, $tanggalJam) {

                // Lock baris untuk mencegah race condition
                $taken = Appointment::where('dokter_id', $request->dokter_id)
                    ->where('tanggal_jam', $tanggalJam)
                    ->where('jenis', 'konsultasi')
                    ->whereNotIn('status', ['batal', 'tidak_hadir'])
                    ->lockForUpdate()
                    ->exists();

                if ($taken) {
                    throw new \Exception('SLOT_TAKEN');
                }

                $appointment = Appointment::create([
                    'user_id'        => $user->id,
                    'dokter_id'      => $request->dokter_id,
                    'dibuat_oleh_id' => $user->id,
                    'jenis'          => 'konsultasi',

                    'tanggal_jam'    => $tanggalJam,
                    'status'         => 'disetujui',
                    'catatan_pasien' => 'Konsultasi Dokter (' . ucfirst($request->jenis) . ')',
                ]);

                $konsultasiNama = strtolower($request->jenis) === 'awal' ? 'Konsultasi Awal' : 'Konsultasi Lanjutan';
                $consultation   = \App\Models\Consultation::where('nama', $konsultasiNama)->first();

                if ($consultation) {
                    \App\Models\AppointmentItem::create([
                        'appointment_id'  => $appointment->appointment_id,
                        'item_type'       => 'consultation',
                        'consultation_id' => $consultation->consultation_id,
                        'qty'             => 1,
                    ]);
                }

                return $appointment;
            });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'SLOT_TAKEN') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Slot ini baru saja diambil pasien lain. Silakan pilih jam lain.',
                ], 422);
            }

            \Illuminate\Support\Facades\Log::error('submitKonsultasi: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat janji temu. Silakan coba lagi.',
            ], 500);
        }

        session()->flash('success', 'Janji Temu Berhasil Dibuat! Silakan cek WhatsApp/Email Anda.');
        session()->flash('active_tab', 'appointment');

        // 🔔 WhatsApp notification (best-effort, tidak menghentikan flow).
        try {
            $appointmentForWa = Appointment::with(['items.consultation', 'dokter', 'pasien'])
                ->find($appointment->appointment_id);
            if ($appointmentForWa) {
                app(FonnteService::class)->sendAppointmentCreated($appointmentForWa);
            }
        } catch (\Throwable $e) {
            Log::warning('Fonnte WA send gagal (konsultasi created): '.$e->getMessage());
        }

        return response()->json([
            'status'         => 'success',
            'message'        => 'Janji temu berhasil dibuat.',
            'appointment_id' => $appointment->appointment_id,
            'data'           => $appointment,
        ]);
    }

    /**
     * Cek slot konsultasi yang sudah ter-booking untuk dokter & tanggal tertentu.
     * Dipakai oleh wizard konsultasi blade untuk men-disable slot yang sudah penuh.
     */
    public function checkKonsultasiSlots(Request $request)
    {
        $request->validate([
            'dokter_id' => 'required|exists:users,id',
            'tanggal'   => 'required|date',
        ]);

        $bookedTimes = Appointment::where('dokter_id', $request->dokter_id)
            ->where('jenis', 'konsultasi')
            ->whereDate('tanggal_jam', $request->tanggal)
            ->whereNotIn('status', ['batal', 'tidak_hadir'])
            ->pluck('tanggal_jam')
            ->map(fn ($dt) => \Carbon\Carbon::parse($dt)->format('H:i'))
            ->values();

        return response()->json([
            'booked' => $bookedTimes,
        ]);
    }

    public function create(Request $request)
    {
        // Form booking treatment lama (create.blade.php) sudah deprecated.
        // Sekarang semua entry point (treatment list, footer, banner, deep link
        // dari pra-konsultasi) diarahkan ke wizard 4-langkah booking-treatment.
        // Param treatment_id dan bundle_id tetap ikut supaya
        // wizard bisa pre-select kalau dibutuhkan ke depan.
        $params = $request->only(['treatment_id', 'bundle_id']);
        return redirect()->route('appointment.booking-treatment', array_filter($params));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => ['required', 'in:konsultasi,treatment'],
            
            // Validasi Dokter (Wajib jika Konsultasi)
            'dokter_id' => [
                'required_if:jenis,konsultasi', 
                'nullable', 
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value && !User::find($value)->hasRole('dokter')) {
                        $fail('User yang dipilih bukan dokter.');
                    }
                },
            ],

            // Validasi Treatment (Wajib jika Treatment)
            'treatments' => ['required_if:jenis,treatment', 'array'],
            'treatments.*' => ['exists:treatments,treatment_id'],

            // Validasi Terapis: hanya cek role.
            // Pembatasan handler kini diatur di level kategori treatment
            // via TreatmentCategory::allowedHandlerTypes(), bukan jabatan.
            'terapis_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    $user = User::find($value);
                    if (!$user || !$user->hasRole('terapis')) {
                        return $fail('User bukan terapis.');
                    }
                },
            ],

            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'waktu'   => ['required'],

            'catatan_pasien' => ['nullable', 'string'],
        ]);

        $tanggalJam = Carbon::parse($request->tanggal . ' ' . $request->waktu);

        $appointment = DB::transaction(function () use ($request, $tanggalJam) {
            $finalDoctorId = $request->jenis === 'konsultasi' ? $request->dokter_id : null;

            $appointment = Appointment::create([
                'user_id' => Auth::id(),
                'jenis'   => $request->jenis,
                'dokter_id' => $finalDoctorId,
                'terapis_id' => $request->terapis_id,

                'tanggal_jam' => $tanggalJam,
                'status' => 'disetujui',
                'catatan_pasien' => $request->catatan_pasien,

                'dibuat_oleh_id' => Auth::id(),
            ]);

            if (!empty($request->treatments)) {
                foreach ($request->treatments as $treatmentId) {
                    AppointmentItem::create([
                        'appointment_id' => $appointment->appointment_id,
                        'item_type' => 'treatment',
                        'treatment_id' => $treatmentId,
                        'qty' => 1,
                    ]);
                }
            }

            return $appointment;
        });

        // 🔔 WhatsApp notification (best-effort).
        try {
            $appointmentForWa = Appointment::with(['items.treatment', 'items.consultation', 'dokter', 'terapis', 'pasien'])
                ->find($appointment->appointment_id);
            if ($appointmentForWa) {
                app(FonnteService::class)->sendAppointmentCreated($appointmentForWa);
            }
        } catch (\Throwable $e) {
            Log::warning('Fonnte WA send gagal (appointment store): '.$e->getMessage());
        }

        return redirect('/dashboard?tab=appointment&highlight=' . $appointment->appointment_id)
            ->with('success', 'Janji temu berhasil dibuat.');
    }

    /**
     * 🔥 UPDATED: Pembatalan Janji Temu + Kirim Notifikasi
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        // 1. Validasi Pemilik
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak membatalkan janji temu ini.');
        }

        // 2. Cek Status
        if (!in_array($appointment->status, ['diajukan', 'disetujui'])) {
            return back()->with('error', 'Janji temu ini tidak dapat dibatalkan lagi.');
        }

        // 3. Validasi Alasan (pesan custom + flash ke session untuk toast)
        $validator = Validator::make($request->all(), [
            'cancel_reason' => 'required|string|min:5|max:255',
        ], [
            'cancel_reason.required' => 'Mohon isi alasan pembatalan terlebih dahulu.',
            'cancel_reason.min'      => 'Alasan pembatalan minimal 5 karakter.',
            'cancel_reason.max'      => 'Alasan pembatalan maksimal 255 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $validator->errors()->first('cancel_reason'));
        }
        
        try {
            DB::transaction(function () use ($appointment, $request) {
                // A. Update Database Appointment
                $appointment->update([
                    'status'        => 'batal',
                    'canceled_by'   => 'pasien',
                    'cancel_reason' => $request->cancel_reason,
                ]);

                // B. KUMPULKAN PENERIMA NOTIFIKASI 👥
                $recipients = collect();

                // 1. Masukkan DOKTER (Jika ada & jenis konsultasi)
                if ($appointment->jenis === 'konsultasi' && $appointment->dokter) {
                    $recipients->push($appointment->dokter);
                }

                // 2. Masukkan TERAPIS (Jika ada & jenis treatment)
                if ($appointment->jenis === 'treatment' && $appointment->terapis) {
                    $recipients->push($appointment->terapis);
                }

                // 3. Masukkan ADMIN & SUPER ADMIN
                $admins = User::whereHas('roles', function ($q) {
                    $q->whereIn('name', ['super_admin', 'admin', 'superuser']);
                })->get();
                
                // Gabung & Hapus Duplikat ID
                $recipients = $recipients->merge($admins)->unique('id');

                // C. LOOPING INSERT MANUAL KE TABEL NOTIFICATIONS 🔔
                // Persis seperti cara di DashboardController
                foreach ($recipients as $user) {
                    try {
                        $jenisLayanan = ucfirst($appointment->jenis);
                        $tanggal = $appointment->tanggal_jam->format('d M, H:i');
                        $pasienName = $appointment->pasien->name;
                        
                        DB::table('notifications')->insert([
                            'id' => Str::uuid()->toString(),
                            'type' => 'Filament\\Notifications\\DatabaseNotification', // Wajib Tipe Ini
                            'notifiable_type' => get_class($user), // App\Models\User
                            'notifiable_id' => $user->id,
                            'data' => json_encode([
                                'format' => 'filament',
                                'title' => 'Janji Temu Dibatalkan 📅',
                                'body' => "**{$pasienName}** membatalkan {$jenisLayanan} pada tanggal **{$tanggal}**.\nAlasan: \"{$request->cancel_reason}\"",
                                'status' => 'warning', // Warna Kuning/Oranye
                                'color' => 'warning',
                                'icon' => 'heroicon-o-calendar',
                                'iconColor' => 'warning',
                                'duration' => 'persistent',
                                'actions' => [
                                    [
                                        'name' => 'Lihat',
                                        'color' => 'gray',
                                        'url' => "/admin/appointments/{$appointment->appointment_id}/edit",
                                        'openInNewTab' => false,
                                    ]
                                ],
                                'view' => 'filament-notifications::notification',
                            ]),
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        Log::info("Manual notification inserted for user: {$user->id} (Appointment Cancel)");

                    } catch (\Exception $e) {
                        Log::error("Failed to insert notification for user {$user->id}: " . $e->getMessage());
                    }
                }
            });

            // 🔔 WhatsApp notification (best-effort, jangan ganggu UX).
            try {
                $appointmentForWa = Appointment::with(['items.treatment', 'items.consultation', 'dokter', 'terapis', 'pasien'])
                    ->find($appointment->appointment_id);
                if ($appointmentForWa) {
                    app(FonnteService::class)->sendAppointmentCancelledByPatient($appointmentForWa);
                }
            } catch (\Throwable $e) {
                Log::warning('Fonnte WA send gagal (cancel by patient): '.$e->getMessage());
            }

            return back()->with('success', 'Janji temu berhasil dibatalkan.');

        } catch (\Exception $e) {
            Log::error('Gagal membatalkan janji temu: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        // 1. Validasi Pemilik
        if ($appointment->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengubah janji temu ini.');
        }

        // 2. Cek Status
        if (!in_array($appointment->status, ['diajukan', 'disetujui'])) {
            return back()->with('error', 'Janji temu ini tidak dapat diubah lagi.');
        }

        // 3. Validasi H-3 jam
        $batasWaktu = \Carbon\Carbon::parse($appointment->tanggal_jam)->subHours(3);
        if (now()->greaterThanOrEqualTo($batasWaktu)) {
            return back()->with('error', 'Batas waktu perubahan jadwal sudah lewat (maksimal 3 jam sebelumnya). Silakan hubungi admin via WhatsApp.');
        }

        // 4. Validasi Input
        $request->validate([
            'new_tanggal' => 'required|date|after_or_equal:today',
            'new_jam' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($appointment, $request) {
                $newTanggalJam = $request->new_tanggal . ' ' . str_replace('.', ':', substr($request->new_jam, 0, 5)) . ':00';

                // A. Update Database Appointment
                $appointment->update([
                    'tanggal_jam' => $newTanggalJam,
                    'status'      => 'disetujui', // tetap disetujui
                ]);

                // B. Notifikasi ke Admin
                $recipients = \App\Models\User::role(['admin', 'super_admin'])->get();
                foreach ($recipients as $user) {
                    try {
                        DB::table('notifications')->insert([
                            'id' => \Illuminate\Support\Str::uuid(),
                            'type' => 'Filament\Notifications\DatabaseNotification',
                            'notifiable_type' => \App\Models\User::class,
                            'notifiable_id' => $user->id,
                            'data' => json_encode([
                                'title' => 'Perubahan Jadwal',
                                'body' => "Pasien {$appointment->user->name} mengubah jadwal untuk {$appointment->catatan_pasien}.",
                                'icon' => 'heroicon-o-calendar',
                                'iconColor' => 'warning',
                                'duration' => 'persistent',
                                'actions' => [
                                    [
                                        'name' => 'Lihat',
                                        'color' => 'gray',
                                        'url' => "/admin/appointments/{$appointment->appointment_id}/edit",
                                        'openInNewTab' => false,
                                    ]
                                ],
                                'view' => 'filament-notifications::notification',
                            ]),
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to insert reschedule notification: " . $e->getMessage());
                    }
                }
            });

            // 🔔 WhatsApp notification: konfirmasi pengajuan ubah jadwal.
            try {
                $appointmentForWa = Appointment::with(['items.treatment', 'items.consultation', 'dokter', 'terapis', 'pasien'])
                    ->find($appointment->appointment_id);
                if ($appointmentForWa) {
                    app(FonnteService::class)->sendAppointmentRescheduled($appointmentForWa);
                }
            } catch (\Throwable $e) {
                Log::warning('Fonnte WA send gagal (reschedule): '.$e->getMessage());
            }

            return back()->with('success', 'Perubahan jadwal telah disetujui.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal merubah janji temu: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
