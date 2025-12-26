<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\PatientForm;
use App\Models\PraKonsultasiRekomendasiItem;
use App\Models\Treatment;
use App\Models\User;
use App\Models\Terapis; // Pastikan Model Terapis di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Filament\Resources\Appointments\AppointmentResource;

class AppointmentController extends Controller
{
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