<div class="overflow-auto w-100">
    {!! $headerHtml !!}
    <table class="table table-bordered table-striped table-hover nowrap w-100" style="table-layout: fixed;">
        <thead>
            <tr>
                <th class="font-weight-normal align-middle text-center">No</th>
                <th class="font-weight-normal align-middle text-center">Perkiraan</th>
                <th class="font-weight-normal align-middle text-center">Keterangan</th>
                <th class="font-weight-normal align-middle text-center">Kelompok</th>
                <th class="font-weight-normal align-middle text-center">Tipe</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $key => $item)
                <tr>
                    <td style="width: 5%;" class="text-center">{{ $key + 1 }}</td>
                    <td style="width: 15%;">{{ $item->Perkiraan }}</td>
                    <td style="width: 40%;">{{ $item->Keterangan }}</td>
                    <td style="width: 20%;">{{ $item->Kelompok }}</td>
                    <td style="width: 20%;">{{ $item->Tipe }}</td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>