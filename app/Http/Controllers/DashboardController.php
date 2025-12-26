<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PatientForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // UBAH DISINI: Sesuaikan dengan nama fungsi di User.php -> 'pasienProfile'
        $user->load('pasienProfile'); 

        // 1. Ambil Riwayat Janji Temu
        $appointments = Appointment::with(['dokter', 'terapis'])
            ->where('user_id', $user->id)
            ->latest('tanggal_jam')
            ->get();

        // 2. Ambil Riwayat Pra Konsul
        $praKonsuls = PatientForm::where('user_id', $user->id)
            ->latest()
            ->get();

        // 3. Ambil Riwayat Pesanan (Kosongin dlu)
        $orders = []; 

        return view('dashboard', [
            'user'         => $user,
            'appointments' => $appointments,
            'praKonsuls'   => $praKonsuls,
            'orders'       => $orders,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi
        $request->validate([
            // Tabel Users
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            
            // Tabel Pasien
            'alamat'        => 'nullable|string|max:500',
            'kota'          => 'nullable|string|max:100',
            'provinsi'      => 'nullable|string|max:100',
            'kode_pos'      => 'nullable|string|max:10',
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        DB::transaction(function () use ($request, $user) {
            // 2. Update Data Akun (Tabel Users)
            $user->update([
                'name'          => $request->name,
                'phone'         => $request->phone,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
            ]);

            // 3. Handle Upload Foto
            // UBAH DISINI: Panggil relasi 'pasienProfile'
            $fotoPath = $user->pasienProfile?->foto_profil_path; 
            
            if ($request->hasFile('foto')) {
                if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                    Storage::disk('public')->delete($fotoPath);
                }
                $fotoPath = $request->file('foto')->store('pasien-photos', 'public');
            }

            // 4. Update/Create Data Detail (Tabel Pasien)
            // UBAH DISINI: Panggil relasi 'pasienProfile()'
            $user->pasienProfile()->updateOrCreate(
                ['user_id' => $user->id], 
                [
                    'alamat'           => $request->alamat,
                    'kota'             => $request->kota,
                    'provinsi'         => $request->provinsi,
                    'kode_pos'         => $request->kode_pos,
                    'foto_profil_path' => $fotoPath,
                ]
            );
        });

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}