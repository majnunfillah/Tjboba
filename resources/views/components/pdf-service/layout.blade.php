<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Laporan' }}</title>
    <link rel="stylesheet" href="{{ public_path('assets/css/adminlte.min.css') }}" media="all">
</head>

<body>
    <main>
        {!! $bodyHtml ?? '' !!}
    </main>
    <footer>
        <p>Tanggal Cetak {{ date('d-m-Y H:i:s') }}, Dicetak Oleh: {{ auth()->user()->USERID }}</p>
    </footer>
</body>

</html>
