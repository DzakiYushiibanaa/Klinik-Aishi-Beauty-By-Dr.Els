<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Terapis — {{ $periodLabel }}</title>
    @include('pdf._partials.therapist-performance-report._styles')
</head>
<body>
    @include('pdf._partials.therapist-performance-report._header', ['periodLabel' => $periodLabel, 'now' => $now])

    @include('pdf._partials.therapist-performance-report._summary', ['summary' => $summary])
    
    @include('pdf._partials.therapist-performance-report._weekly', ['weeks' => $weeks])
    
    @include('pdf._partials.therapist-performance-report._detail', ['rows' => $rows, 'tableTotals' => $tableTotals])

    @include('pdf._partials.therapist-performance-report._footer')
</body>
</html>
