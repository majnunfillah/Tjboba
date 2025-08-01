<div class="table_expand" style="overflow-x: auto; overflow-y:auto; height: 680px; position:sticky; left:12px;">
  <table class="table table-bordered table-striped table-hover nowrap datatable-expand-aktiva">
      <thead>
          <tr>
              <th>Bulan</th>
              <th>Tahun</th>
              <th>Perolehan Bulan Lalu</th>
              <th>Penambahan Perolehan Bulan Ini</th>
              <th>Pengurangan Perolehan Bulan Ini</th>
              <th>Perolehan Akhir Bulan Ini</th>
              <th>Akumulasi Bulan Lalu</th>
              <th>Penambahan Akumulasi Bulan Ini</th>
              <th>Pengurangan Akumulasi Bulan Ini</th>
              <th>Akumulasi Akhir Bulan Ini</th>
              <th>Nilai Buku</th>
          </tr>
      </thead>
      <tbody>
          @foreach ($data as $item)
              <tr>
                  <td>{{ $item->Bulan }}</td>
                  <td>{{ $item->Tahun }}</td>
                  <td>{{ number_format($item->Awal, 2, ',', '.') }}</td>
                  <td>{{ number_format($item->MD, 2, ',', '.') }}</td>
                  <td>{{ number_format($item->MK, 2, ',', '.') }}</td>
                  <td>{{ number_format($item->Akhir, 2, ',', '.') }}</td>
                  <td>{{ number_format($item->AwalSusut, 2, ',', '.') }}</td>
                  <td>{{ number_format($item->SD, 2, ',', '.') }}</td>
                  <td>{{ number_format($item->SK, 2, ',', '.') }}</td>
                  <td>{{ number_format($item->AkhirSusut, 2, ',', '.') }}</td>
                  <td>{{ number_format($item->NilaiAK, 2, ',', '.') }}</td>
              </tr>
          @endforeach
      </tbody>
  </table>
</div>
