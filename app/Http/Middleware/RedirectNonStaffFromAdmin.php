<?php

namespace App\Http\Middleware;

use App\Support\AuthRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cegah user dengan role pasien (atau tanpa role staf) mengakses panel admin.
 *
 * Walau User::canAccessPanel() sudah menolak, Filament default-nya akan
 * menampilkan halaman 403 yang kurang ramah. Middleware ini meng-intercept
 * lebih awal dan redirect:
 *   - Pasien yang login → /dashboard (+ flash error toast)
 *   - User tanpa role apapun → /dashboard
 *   - Staf yang sudah dinonaktifkan (dokter.status_praktik != aktif,
 *     terapis.status_kerja != aktif) → logout + redirect ke /login
 *     dengan pesan jelas.
 *
 * Dipasang di AdminPanelProvider->authMiddleware([...]) SETELAH
 * Filament\Http\Middleware\Authenticate supaya kita yakin user sudah login.
 */
class RedirectNonStaffFromAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Belum login → biarkan Filament Authenticate yang handle (redirect login).
        if (!$user) {
            return $next($request);
        }

        // Bukan staf sama sekali → kick out ke dashboard pasien.
        if (! AuthRedirect::isStaff($user)) {
            return redirect('/dashboard')
                ->with('error', 'Halaman admin hanya dapat diakses oleh staf klinik.');
        }

        // Staf tapi statusnya nonaktif → logout + paksa kembali ke login.
        if (! AuthRedirect::isStaffActive($user)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun Anda sedang dinonaktifkan. Hubungi admin klinik untuk informasi lebih lanjut.');
        }

        return $next($request);
    }
}
