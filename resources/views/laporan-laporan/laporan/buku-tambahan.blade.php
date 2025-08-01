<div class="overflow-auto w-100">
    {!! $headerHtml !!}
    <table class="table table-bordered table-striped table-hover nowrap w-100" style="table-layout: fixed;">
        <thead>
            <tr>
                <th class="font-weight-normal align-middle text-center" style="width: 110px;">
                    Tanggal</th>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;">
                    No. Bukti</th>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;">
                    Keterangan</th>
                <th class="font-weight-normal align-middle text-center" style="width: 80px;">
                    Lawan</th>
                <th class="font-weight-normal align-middle text-center" style="width: 200px;">
                    Debet</th>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;">
                    Kredit</th>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;">
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
                        <td class="text-right align-middle" colspan="4">
                            Sub Total
                        </td>
                        <td class="text-right align-middle">
                            {{ number_format(floatval($totalDebet)) }}
                        </td>
                        <td class="text-right align-middle">
                            {{ number_format(floatval($totalKredit)) }}
                        </td>
                        <td class="text-right align-middle">
                        </td>
                    </tr>
                @endif

                @if ($noAcc != $item->NoACC)
                    <tr>
                        <td class="text-left align-middle" colspan="7">
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
                    <td class="text-left align-middle" >
                        {{ \Carbon\Carbon::parse($item->Tanggal)->format('d/m/Y') }}
                    </td>
                    <td class="text-left align-middle">{{ $item->Nobukti }}</td>
                    <td class="text-left align-middle">{{ $item->Note }}</td>
                    <td class="text-left align-middle">{{ $item->Lawan }}</td>
                    <td class="text-right align-middle">{{ number_format(floatval($item->Debet), 2) }}</td>
                    <td class="text-right align-middle">{{ number_format(floatval($item->Kredit), 2) }}</td>
                    <td class="text-right align-middle">{{ number_format(floatval($saldo), 2) }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
