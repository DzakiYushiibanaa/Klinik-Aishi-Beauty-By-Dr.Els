<!DOCTYPE html>
<html>
<head>
    <title>Nota Pesanan Anda</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1A1A1A; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #E8E0D8; border-radius: 10px;">
        <h2 style="color: #CFAF53;">Halo, {{ $order->user->name }}</h2>
        <p>Terima kasih telah mempercayakan perjalanan kecantikan Anda bersama <strong>Aishi Beauty Klinik</strong>.</p>
        <p>Bersama email ini, kami melampirkan Nota Pesanan (Invoice) untuk transaksi Anda dengan kode: <strong>{{ $order->kode_order }}</strong>.</p>
        <p>Jika ada pertanyaan lebih lanjut mengenai pesanan atau jadwal perawatan Anda, silakan hubungi admin kami melalui pesan WhatsApp.</p>
        <br>
        <p>Salam hangat,<br><strong>Tim Aishi Beauty</strong></p>
    </div>
</body>
</html>
