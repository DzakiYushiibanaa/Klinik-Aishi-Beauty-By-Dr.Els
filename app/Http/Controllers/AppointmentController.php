<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\PatientForm;
use App\Models\PraKonsultasiRekomendasiItem;
use App\Models\Treatment;
use App\Models\User;
use App\Models\Terapis; // Pastikan Model Terapis di-import
use App\Notifications\AppointmentCanceledNotification; // 🔥 TAMBAHAN: Import Notification
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Filament\Resources\Appointments\AppointmentResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    public function selectType()
    {
        return view('appointment.select-type');
    }

    public function konsultasi()
    {
        $user = Auth::user();
        
        // Pengecekan Kelengkapan Profil
        if (!$user->phone || !$user->tanggal_lahir) {
            return redirect('/dashboard')->with('error', 'Silakan lengkapi Nomor WhatsApp dan Tanggal Lahir Anda di tab Profil sebelum membuat janji temu.');
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
            'tanggal' => 'required|date',
            'jam' => 'required|string',
        ]);

        $user = Auth::user();

        // Buat record Appointment
        $appointment = Appointment::create([
            'user_id' => $user->id,
            'dokter_id' => $request->dokter_id,
            'dibuat_oleh_id' => $user->id,
            'jenis' => 'konsultasi',
            'sumber' => 'online',
            'tanggal_jam' => $request->tanggal . ' ' . str_replace('.', ':', substr($request->jam, 0, 5)) . ':00',
            'status' => 'diajukan',
            'catatan_pasien' => 'Konsultasi Dokter (' . ucfirst($request->jenis) . ')',
        ]);

        // Simpan data, kirim notifikasi, dll.
        // Bisa diintegrasikan ke WA/Email di sini (contoh: SendWAJob::dispatch($appointment))

        // Set session untuk Toast di halaman Dashboard nanti
        session()->flash('success', 'Janji Temu Berhasil Dibuat! Silakan cek WhatsApp/Email Anda.');

        // Kembalikan response JSON untuk ditangkap oleh AlpineJS
        return response()->json([
            'status' => 'success',
            'message' => 'Janji temu berhasil dibuat.',
            'data' => $appointment
        ]);
    }

    public function create(Request $request)
    {
        $linkedFormId = $request->query('patient_form_id');
        $selectedTreatmentId = $request->query('treatment_id');

        $autoTreatments = [];
        $autoDoctorId   = null;

        // Auto-fill Logic
        if ($linkedFormId) {
            $form = PatientForm::find($linkedFormId);
            if ($form) {
                $autoDoctorId = $form->dokter_id;
                $autoTreatments = PraKonsultasiRekomendasiItem::where('patient_form_id', $linkedFormId)
                    ->whereNotNull('treatment_id')
                    ->pluck('treatment_id')
                    ->toArray();
            }
        }

        if ($selectedTreatmentId) {
            $autoTreatments[] = $selectedTreatmentId;
            $autoTreatments = array_unique($autoTreatments);
        }

        // ============================================================
        // 1. LOAD TREATMENTS (FIX: Nama Kolom 'min_level')
        // ============================================================
        $treatments = Treatment::with('category')
            ->where('is_active', true)
            ->get()
            ->map(function($t) {
                // Sesuai schema Filament: kolomnya 'min_level'
                $t->min_level = $t->min_level ?? 1; 
                return $t;
            });

        // ============================================================
        // 2. LOAD THERAPISTS (FIX: Logic Jabatan 'Senior' via Helper)
        // ============================================================
        $therapists = User::role('terapis')
            ->get()
            ->map(function($user) {
                // Panggil Helper private function (sama kayak Filament)
                $level = $this->terapisLevelFromUserId($user->id);
                
                $displayLabel = ($level === 2) ? 'Senior Beautician' : 'Junior Beautician';

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'level' => $level, // 1=Junior, 2=Senior
                    'display_label' => $displayLabel, 
                    'initial' => substr($user->name, 0, 1),
                ];
            });

        // 3. LOAD DOCTORS
        $doctors = User::role('dokter')
            ->with('dokterProfile')
            ->whereHas('dokterProfile', function($q) {
                $q->where('status_praktik', 'aktif');
            })
            ->get();

        return view('appointment.create', [
            'treatments'      => $treatments,
            'doctors'         => $doctors,
            'therapists_json' => $therapists->toJson(), 
            'therapists'      => $therapists,
            'linkedFormId'    => $linkedFormId,
            'autoTreatments'  => $autoTreatments,
            'autoDoctorId'    => $autoDoctorId,
        ]);
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

            // Validasi Terapis (Cek Level Sesuai Filament Logic)
            'terapis_id' => [
                'nullable', 
                'exists:users,id',
                function ($attribute, $value, $fail) use ($request) {
                    if (!$value) return; // Skip if null (siapa saja)

                    // 1. Cek Role
                    $user = User::find($value);
                    if (!$user->hasRole('terapis')) {
                        return $fail('User bukan terapis.');
                    }

                    // 2. Cek Level Requirement
                    if ($request->jenis === 'treatment' && !empty($request->treatments)) {
                        
                        // Ambil MAX min_level dari DB untuk treatment yang dipilih
                        // Sesuai logic Filament: $max = max($max, $lvl);
                        $maxLevelRequired = Treatment::whereIn('treatment_id', $request->treatments)
                            ->max('min_level') ?? 1;

                        if ($maxLevelRequired > 1) {
                            // Cek Level Terapis User Ini
                            $levelTerapis = $this->terapisLevelFromUserId($value);

                            if ($levelTerapis < $maxLevelRequired) {
                                $fail('Salah satu treatment yang dipilih membutuhkan Terapis Senior.');
                            }
                        }
                    }
                },
            ],

            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'waktu'   => ['required'],
            'patient_form_id' => ['nullable'],
            'catatan_pasien' => ['nullable', 'string'],
        ]);

        $tanggalJam = Carbon::parse($request->tanggal . ' ' . $request->waktu);

        DB::transaction(function () use ($request, $tanggalJam) {
            $finalDoctorId = $request->jenis === 'konsultasi' ? $request->dokter_id : null;

            $appointment = Appointment::create([
                'user_id' => Auth::id(),
                'jenis'   => $request->jenis,
                'dokter_id' => $finalDoctorId,
                'terapis_id' => $request->terapis_id,
                'patient_form_id' => $request->patient_form_id,
                'tanggal_jam' => $tanggalJam,
                'status' => 'diajukan',
                'catatan_pasien' => $request->catatan_pasien,
                'sumber' => 'online',
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
        });

        return redirect('/dashboard')
            ->with('success', 'Janji temu berhasil diajukan! Menunggu konfirmasi admin.');
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

        // 3. Validasi Alasan
        $request->validate([
            'cancel_reason' => 'required|string|min:5|max:255',
        ]);
        
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

            return back()->with('success', 'Janji temu berhasil dibatalkan.');

        } catch (\Exception $e) {
            Log::error('Gagal membatalkan janji temu: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * LOGIC FILAMENT: Konversi jabatan terapis (varchar) -> level angka.
     */
    private function terapisLevelFromUserId(?int $userId): int
    {
        if (!$userId) return 1;

        // Query persis dari Filament Schema lu
        $jabatan = Terapis::query()
            ->where('user_id', $userId)
            ->value('jabatan');

        $j = strtolower(trim((string) $jabatan));

        if (str_contains($j, 'senior')) return 2;
        if (str_contains($j, 'junior')) return 1;

        return 1;
    }
}