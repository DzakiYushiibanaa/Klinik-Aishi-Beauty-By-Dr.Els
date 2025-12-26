<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Menandai 1 notifikasi sudah dibaca, lalu redirect ke link tujuannya.
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        
        // Tandai sudah dibaca (isi kolom read_at)
        $notification->markAsRead();

        // Ambil link tujuan dari data notifikasi (kalau ada), kalau gak ada ke dashboard
        $targetUrl = $notification->data['link'] ?? url('/dashboard');

        return redirect($targetUrl);
    }

    /**
     * Menandai SEMUA notifikasi sudah dibaca.
     */
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}