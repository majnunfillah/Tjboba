<table class="table table-sm table-stripped table-bordered w-100" style="table-layout: fixed;">
    <thead>
        <tr>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 50px;">No</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 150px;">Perkiraan</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 300px;">Keterangan</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 150px;">Kelompok</th>
            <th class="text-center align-middle font-weight-normal" style="font-size: 16px; width: 100px;">Tipe</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $item)
            <tr style="page-break-after: always">
                <td class="text-center align-middle font-weight-normal" style="font-size: 16px;">{{ $key + 1 }}</td>
                <td class="text-left align-middle font-weight-normal" style="font-size: 16px;">{{ $item->Perkiraan }}</td>
                <td class="text-left align-middle font-weight-normal" style="font-size: 16px;">{{ $item->Keterangan }}</td>
                <td class="text-left align-middle font-weight-normal" style="font-size: 16px;">{{ $item->Kelompok }}</td>
                <td class="text-left align-middle font-weight-normal" style="font-size: 16px;">{{ $item->Tipe }}</td>
            </tr>
        @endforeach
    </tbody>
</table>