<x-base-modal modalId="{{ $modalId }}" isDestroy="true" modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="Saldo Awal">

    <div class="row">
        <div class="col-sm-12">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-body">
                    <table id="datatableBudget" class="table table-bordered table-striped table-hover nowrap w-100" data-server="{{ route('master-data.master-accounting.get-saldo-awal', ['perkiraan' => count($res) > 0 ? $res[0]->Perkiraan : '%20']) }}">
                        <thead>
                            <tr>
                                <th>Perkiaan</th>
                                <th>Devisi</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Debet</th>
                                <th>Kredit</th>
                                <th>Budget</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($res as $item)
                                <tr class="rowBudget">
                                    <td class="Perkiraan">{{ $item->Perkiraan }}</td>
                                    <td class="Devisi">{{ $item->Devisi }}</td>
                                    <td class="Bulan">{{ $item->Bulan }}</td>
                                    <td class="Tahun">{{ $item->Tahun }}</td>
                                    <td class="Debet text-right">{{ number_format($item->Md, 2, ',', '.') }}</td>
                                    <td class="Kredit text-right">{{ number_format($item->Mk, 2, ',', '.') }}</td>
                                    <td class="Budget text-right">{{ number_format($item->Budget, 2, ',', '.') }}</td>
                                    <td>
                                        <div class='d-flex justify-content-end'>
                                            <button class='btn btn-warning btn-sm mr-1 btnSetBudget'><i class='fa fa-pencil mr-1'></i>Set</button>
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

        <div class="col-sm-12" id="contentSetBudget">
            <form action="" method="POST" id="formBudget">
                @csrf
                <div class="row">
                    <x-form-part col="sm-3" label="Devisi" type="text" name="Devisi" readonly/>
                    <x-form-part col="sm-5" class="mask-money" label="Budget" type="text" name="Budget" />
                    <x-form-part col="sm-2" label="Bulan" type="number" name="Bulan" />
                    <x-form-part col="sm-2" label="Tahun" type="number" name="Tahun" />
                    <input type="hidden" name="Budget_val" />
                    <button class="col-sm-2 btn btn-primary w-100">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-base-modal>
