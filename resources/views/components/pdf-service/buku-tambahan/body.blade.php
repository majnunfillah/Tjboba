<table class="table table-sm table-stripped table-bordered w-100" style="table-layout: fixed;">
    <thead>
        <tr>
            <th class="font-weight-normal align-middle text-center" style="font-size: 24px; width: 135px;">
                Tanggal</th>
            <th class="font-weight-normal align-middle text-center" style="font-size: 24px; width: 280px;">
                No. Bukti</th>
            <th class="font-weight-normal align-middle text-center" style="font-size: 24px; width: 350px;">
                Keterangan</th>
            <th class="font-weight-normal align-middle text-center" style="font-size: 24px; width: 100px;">
                Lawan</th>
            <th class="font-weight-normal align-middle text-center" style="font-size: 24px; width: 200px;">
                Debet</th>
            <th class="font-weight-normal align-middle text-center" style="font-size: 24px; width: 200px;">
                Kredit</th>
            <th class="font-weight-normal align-middle text-center" style="font-size: 24px; width: 200px;">
                Saldo</th>
        </tr>
    </thead>
    <tbody>
        @php
            $saldo = 0;
            $totalDebet = 0;
            $totalKredit = 0;
            $noAcc = null;
        @endphp
        @forelse ($data as $key => $item)
            @if ($key != 0 && $noAcc != $item->NoACC)
                <tr>
                    <td style="font-size: 24px;" class="text-right align-middle" colspan="4">
                        Sub Total
                    </td>
                    <td style="font-size: 24px;" class="text-right align-middle">
                        {{ number_format(floatval($totalDebet)) }}
                    </td>
                    <td style="font-size: 24px;" class="text-right align-middle">
                        {{ number_format(floatval($totalKredit)) }}
                    </td>
                    <td style="font-size: 24px;" class="text-right align-middle">
                    </td>
                </tr>
            @endif

            @if ($noAcc != $item->NoACC)
                <tr>
                    <td style="font-size: 24px;" class="text-left align-middle" colspan="7">
                        {{ $item->NoACC }} {{ $item->Nama }}
                    </td>
                </tr>
            @endif

            @php
                if ($noAcc != $item->NoACC) {
                    $saldo = floatval($item->SaldoAkhir);
                    $totalDebet = 0;
                    $totalKredit = 0;
                    $noAcc = $item->NoACC;
                } else {
                    $saldo += floatval($item->SaldoAkhir);
                    $totalDebet += floatval($item->Debet);
                    $totalKredit += floatval($item->Kredit);
                }
            @endphp

            <tr>
                <td style="font-size: 24px;" class="text-left align-middle text-break">
                    {{ \Carbon\Carbon::parse($item->Tanggal)->format('d/m/Y') }}
                </td>
                <td style="font-size: 24px;" class="text-left align-middle text-break">{{ $item->Nobukti }}</td>
                <td style="font-size: 24px;" class="text-left align-middle text-break">{{ $item->Note }}</td>
                <td style="font-size: 24px;" class="text-left align-middle text-break">{{ $item->Lawan }}</td>
                <td style="font-size: 24px;" class="text-right align-middle text-break">{{ number_format(floatval($item->Debet), 2) }}</td>
                <td style="font-size: 24px;" class="text-right align-middle text-break">{{ number_format(floatval($item->Kredit), 2) }}</td>
                <td style="font-size: 24px;" class="text-right align-middle text-break">{{ number_format(floatval($saldo), 2) }}</td>
            </tr>
        @empty
        @endforelse
    </tbody>
</table>
