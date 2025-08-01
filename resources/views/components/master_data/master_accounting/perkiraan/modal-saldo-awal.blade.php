<x-base-modal modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="Saldo Awal">

    <div class="row">
        <div class="col-sm-12">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-body">
                    <table id="datatableSaldo" class="table table-bordered table-striped table-hover nowrap w-100" data-server="{{ route('master-data.master-accounting.get-saldo-awal', ['perkiraan' => count($res) > 0 ? ' ' : $res[0]->Perkiraan]) }}">
                        <thead>
                            <tr>
                                <th>Devisi</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Devisi</th>
                                <th>Perkiraan</th>
                                <th>Keterangan</th>
                                <th>Awal Debet</th>
                                <th>Awal Debet Valas</th>
                                <th>Awal Kredit</th>
                                <th>Awal Kredit Valas</th>
                                <th>Valas</th>
                                <th>Kurs</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($res as $item)
                                <tr class="rowSaldo">
                                    <td class="NamaDevisi">{{ $item->NamaDevisi }}</td>
                                    <td class="Bulan">{{ $item->Bulan }}</td>
                                    <td class="Tahun">{{ $item->Tahun }}</td>
                                    <td class="Devisi">{{ $item->Devisi }}</td>
                                    <td class="Perkiraan">{{ $item->Perkiraan }}</td>
                                    <td class="Keterangan">{{ $item->Keterangan }}</td>
                                    <td class="AwalDRp text-right">{{ number_format($item->AwalDRp, 2,',', '.') }}</td>
                                    <td class="AwalD text-right">{{ number_format($item->AwalD, 2,',', '.') }}</td>
                                    <td class="AwalKRp text-right">{{ number_format($item->AwalKRp, 2,',', '.') }}</td>
                                    <td class="AwalK text-right">{{ number_format($item->AwalK, 2,',', '.') }}</td>
                                    <td class="valas">{{ $item->valas }}</td>
                                    <td class="kurs">{{ number_format($item->kurs, 2,',', '.') }}</td>
                                    <td>
                                        <div class='d-flex justify-content-end'>
                                            <button class='btn btn-warning btn-sm mr-1 btnSetSaldoAwal'><i class='fa fa-pencil mr-1'></i>Set</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card -->
            </div>
        </div>

        <div class="col-sm-12" id="contentSetSaldo">
            <form action="" method="POST" id="formSetSaldo">
                @csrf
                <input type="hidden" name="Bulan">
                <input type="hidden" name="Tahun">
                <input type="hidden" name="Devisi">
                <div class="row">
                    <x-form-part col="sm-6" label="Valas" type="text" name="valas" />
                    <x-form-part col="sm-6" label="Kurs" type="text" name="kurs" />
                    <x-form-part col="sm-6" label="Nilai Awal" type="text" name="AwalDRp" />
                    <x-form-part col="sm-6" label="Nilai Awal Valas" type="text" name="AwalD"/>
                    <input type="hidden" name="kurs_val" />
                    <input type="hidden" name="AwalDRp_val" />
                    <input type="hidden" name="AwalD_val" />
                    <button class="col-sm-2 btn btn-primary w-100">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-base-modal>
