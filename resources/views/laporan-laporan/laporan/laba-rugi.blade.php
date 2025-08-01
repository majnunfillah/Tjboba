<div class="overflow-auto w-100">
    {!! $headerHtml !!}
    <table class="table table-sm table-bordered nowrap w-100" style="table-layout: fixed;">
        <thead>
            <tr>
                <th class="font-weight-normal align-middle text-center" style="width: 110px;" rowspan="2">
                    Perkiraan</th>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;" rowspan="2">
                    Keterangan</th>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;">
                    Bulan Lalu</th>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;">
                    Bulan ini</th>
                <th class="font-weight-normal align-middle text-center" style="width: 200px;">
                    S/d Bulan ini</th>
            </tr>
            <tr>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;">
                    Rp.</th>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;">
                    Rp.</th>
                <th class="font-weight-normal align-middle text-center" style="width: 250px;">
                    Rp.</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $key => $item)
                @if ($item->jumlah != '')
                    <tr>
                        <td style="border-bottom: none;" class="text-left align-middle">
                        </td>
                        <td style="border-bottom: none;" class="text-left align-middle">&nbsp;&nbsp;&nbsp;&nbsp;{{ $item->keterangan }}</td>
                        <td style="border-bottom: none;" class="text-right align-middle">{{ number_format(floatval($item->TotalA), 2) }}</td>
                        <td style="border-bottom: none;" class="text-right align-middle">{{ number_format(floatval($item->TotalB), 2) }}</td>
                        <td style="border-bottom: none;" class="text-right align-middle">{{ number_format(floatval($item->TotalC), 2) }}</td>
                    </tr>
                @else
                    <tr style="border-top: none; border-bottom: none;">
                        <td style="border-top: none; border-bottom: none;" class="text-left align-middle">
                            {{ $item->perkiraan }}
                        </td>
                        <td style="border-top: none; border-bottom: none;" class="text-left align-middle">{{ $item->keterangan }}</td>
                        <td style="border-top: none; border-bottom: none;" class="text-right align-middle">{{ number_format(floatval($item->TotalA), 2) }}</td>
                        <td style="border-top: none; border-bottom: none;" class="text-right align-middle">{{ number_format(floatval($item->TotalB), 2) }}</td>
                        <td style="border-top: none; border-bottom: none;" class="text-right align-middle">{{ number_format(floatval($item->TotalC), 2) }}</td>
                    </tr>
                @endif
            @empty
            @endforelse
        </tbody>
    </table>
</div>
