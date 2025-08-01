<div style="display: flex; justify-content: left;">
    <div style="text-align: left;">
        <h5 style="font-weight: normal; font-size: 18px; margin: 0;">BUKU TAMBAHAN</h5>
        <h5 style="font-weight: normal; font-size: 18px; margin: 0;">Perkiraan : {{ $perkiraanAwal->Perkiraan }} ({{ $perkiraanAwal->Keterangan }}) s/d {{ $perkiraanAkhir->Perkiraan }} ({{ $perkiraanAkhir->Keterangan }})</h5>
        <h5 style="font-weight: normal; font-size: 18px; margin: 0;">Periode : {{ \Carbon\Carbon::parse($tglawal)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tglakhir)->format('d/m/Y') }}</h5>
    </div>
</div>
