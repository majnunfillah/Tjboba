<table class="table table-sm table-stripped table-bordered w-100" style="table-layout: fixed;">
    <thead>
        <tr>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 100px;" rowspan="2">No. ACC</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 250px;" rowspan="2">Keterangan</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 250px;" colspan="2">Saldo Awal
            </th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 250px;" colspan="2">Mutasi</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 250px;" colspan="2">Koreksi</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 250px;" colspan="2">Laba/Rugi
            </th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 250px;" colspan="2">Saldo Akhir
            </th>
        </tr>
        <tr>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Debet</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Kredit</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Debet</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Kredit</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Debet</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Kredit</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Debet</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Kredit</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Debet</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px;">Kredit</th>
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
        @foreach ($data as $item)
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
            <tr style="page-break-after: always">
                <td class="text-left align-middle font-weight-normal" style="font-size: 16px;">{{ $item->Perkiraan }}
                </td>
                <td class="text-left align-middle font-weight-normal" style="font-size: 16px;">{{ $item->keterangan }}
                </td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->SaldoAwD), 2) }}</td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->SaldoAwk), 2) }}</td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->MD), 2) }}</td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->MK), 2) }}</td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->JPD), 2) }}</td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->JPK), 2) }}</td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->RLD), 2) }}</td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->RLK), 2) }}</td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->SaldoAkD), 2) }}</td>
                <td class="text-right align-middle font-weight-normal" style="font-size: 16px;">
                    {{ number_format(floatval($item->SaldoAkK), 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="border: none"></td>
            <td style="border: none"></td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($saldoAwalKred), 2) }}</td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($saldoAwalDeb), 2) }}</td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($mutasiDeb), 2) }}</td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($mutasiKred), 2) }}</td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($koreksiDeb), 2) }}</td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($koreksiKred), 2) }}</td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($labaDeb), 2) }}</td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($labaKred), 2) }}</td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($saldoAkhirDeb), 2) }}</td>
            <td class="text-right align-middle font-weight-bold" style="font-size: 16px;">
                {{ number_format(floatval($saldoAkhirKred), 2) }}</td>
        </tr>
    </tbody>
</table>