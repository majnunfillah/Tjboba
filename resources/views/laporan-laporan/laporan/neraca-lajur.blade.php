<div class="overflow-auto w-100">
    {!! $headerHtml !!}
    <table class="table table-bordered table-striped table-hover nowrap w-100" style="table-layout: fixed;">
        <thead>
            <tr>
                <th class="font-weight-normal align-middle text-center" rowspan="2">
                    No. ACC</th>
                <th class="font-weight-normal align-middle text-center" rowspan="2">
                    Keterangan</th>
                <th class="font-weight-normal align-middle text-center" colspan="2">
                    Saldo Awal</th>
                <th class="font-weight-normal align-middle text-center" colspan="2">Mutasi</th>
                <th class="font-weight-normal align-middle text-center" colspan="2">Koreksi</th>
                <th class="font-weight-normal align-middle text-center" colspan="2">Laba/Rugi</th>
                <th class="font-weight-normal align-middle text-center" colspan="2">Saldo Akhir</th>
            </tr>
            <tr>
                <th class="font-weight-normal align-middle text-center">
                    Debet</th>
                <th class="font-weight-normal align-middle text-center">
                    Kredit</th>
                <th class="font-weight-normal align-middle text-center">
                    Debet</th>
                <th class="font-weight-normal align-middle text-center">
                    Kredit</th>
                <th class="font-weight-normal align-middle text-center">
                    Debet</th>
                <th class="font-weight-normal align-middle text-center">
                    Kredit</th>
                <th class="font-weight-normal align-middle text-center">
                    Debet</th>
                <th class="font-weight-normal align-middle text-center">
                    Kredit</th>
                <th class="font-weight-normal align-middle text-center">
                    Debet</th>
                <th class="font-weight-normal align-middle text-center">
                    Kredit</th>
            </tr>
        </thead>
        <tbody>
            @php
                $saldoAwalDeb = 0;
                $saldoAwalKred = 0;
                $mutasiDeb = 0;
                $mutasiKred = 0;
                $koreksiDeb = 0;
                $koreksiKred = 0;
                $labaDeb = 0;
                $labaKred = 0;
                $saldoAkhirDeb = 0;
                $saldoAkhirKred = 0;
            @endphp
            @forelse ($data as $item)
                @php
                    $saldoAwalDeb += floatval($item->SaldoAwD);
                    $saldoAwalKred += floatval($item->SaldoAwk);
                    $mutasiDeb += floatval($item->MD);
                    $mutasiKred += floatval($item->MK);
                    $koreksiDeb += floatval($item->JPD);
                    $koreksiKred += floatval($item->JPK);
                    $labaDeb += floatval($item->RLD);
                    $labaKred += floatval($item->RLK);
                    $saldoAkhirDeb += floatval($item->SaldoAkD);
                    $saldoAkhirKred += floatval($item->SaldoAkK);
                @endphp
                <tr>
                    <td style="width: 10%;">{{ $item->Perkiraan }}</td>
                    <td style="width: 30%;">{{ $item->keterangan }}</td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->SaldoAwD), 2) }}
                    </td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->SaldoAwk), 2) }}
                    </td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->MD), 2) }}</td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->MK), 2) }}</td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->JPD), 2) }}</td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->JPK), 2) }}</td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->RLD), 2) }}</td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->RLK), 2) }}</td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->SaldoAkD), 2) }}
                    </td>
                    <td class="font-bold text-right align-middle">{{ number_format(floatval($item->SaldoAkK), 2) }}
                    </td>
                </tr>
            @empty
            @endforelse
            <tr>
                <td style="border: none"></td>
                <td style="border: none"></td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($saldoAwalDeb), 2) }}</td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($saldoAwalKred), 2) }}</td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($mutasiDeb), 2) }}</td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($mutasiKred), 2) }}</td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($koreksiDeb), 2) }}</td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($koreksiKred), 2) }}</td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($labaDeb), 2) }}</td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($labaKred), 2) }}</td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($saldoAkhirDeb), 2) }}</td>
                <td class="font-weight-bold text-right align-middle">
                    {{ number_format(floatval($saldoAkhirKred), 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>
