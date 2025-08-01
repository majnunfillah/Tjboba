<x-base-modal modalId="{{ $modalId }}" isDestroy="true"
    modalWidth="{{ $modalWidth !== null ? $modalWidth : 'lg' }}" modalTitle="{{ $modalTitle }}">
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <div class="card-title">
                            <h4 class="mb-0 kodeCustomer"></h4>
                        </div>
                        <div class="card-title">
                            <h4 class="mb-0 NoBukti"></h4>
                        </div>
                    </div>
                    <hr style="width:100%" class="mt-0 mb-0">
                    <div class="d-flex justify-content-between">
                        <div class="card-title">
                            <h4 class="mb-0 namaCustomer"></h4>
                        </div>
                        <div class="card-title">
                            <h4 class="mb-0 Perkiraan"></h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="datatableHutang" class="table table-bordered table-striped table-hover nowrap w-100"
                        data-server="{{ route('accounting.bank-or-kas.get-data-hutang') }}">
                        <thead>
                            <tr>
                                <th class="text-center" colspan="5"> </th>
                                <th class="text-center" colspan="3">Rupiah</th>
                                <th class="text-center" colspan="2"> </th>
                                <th class="text-center" colspan="3">Valas</th>
                                <th></th>
                            </tr>
                            <tr>
                                <th>No. Faktur</th>
                                <th>No Retur</th>
                                <th>Tanggal</th>
                                <th>Jatuh Tempo</th>
                                <th>No. SO</th>
                                <th>Debet</th>
                                <th>Kredit</th>
                                <th>Saldo</th>
                                <th>Valas</th>
                                <th>Kurs</th>
                                <th>Debet</th>
                                <th>Kredit</th>
                                <th>Saldo</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="4"></th>
                                <th class="total-saldo">0</th>
                                <th class="total-kredit">0</th>
                                <th class="total-jumlah-saldo">0</th>
                                <th colspan="2"></th>
                                <th class="total-saldo-d">0</th>
                                <th class="total-kredit-d">0</th>
                                <th class="total-jumlah-saldo-d">0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-6 d-none" id="contentPelunasan">
                    <form action="" method="POST" id="formPelunasan">
                        @csrf
                        <div class="row">
                            {{-- <input type="hidden" name="NoBukti" value="">
                            <input type="hidden" name="NoMsk" value="">
                            <input type="hidden" name="kode" value="">
                        <input type="hidden" name="perkiraanHutang" value=""> --}}
                            <x-form-part col="sm-12" label="No Faktur" type="text" name="NoFaktur" readonly />
                            <x-form-part col="sm-6" label="Tanggal" type="date" name="Tanggal" readonly />
                            <x-form-part col="sm-6" label="Jatuh Tempo" type="date" name="JatuhTempo" readonly />
                            <x-form-part col="sm-6" label="Valas" type="text" name="Valas" value="IDR"
                                readonly />
                            <x-form-part col="sm-6" label="Kurs" type="text" name="Kurs" value="1.00"
                                readonly />
                            <x-form-part type="hidden" name="Debet" value="0" />
                            <x-form-part type="hidden" name="NoBukti" />
                            <x-form-part col="sm-12" label="Jumlah" type="mask-money" name="Debet_val"
                                value="0" />
                            <x-form-part col="sm-12" label="Catatan" type="text" name="Catatan" />
                            <button type="submit" class="col-sm-2 mr-2 btn btn-primary w-100">Simpan</button>
                            <button type="button" class="col-sm-2 btn btn-danger w-100 batal-hutang">Batal</button>
                        </div>
                    </form>
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <x-form-part type="hidden" name="DebetPelunasan" value="0" />
                        <x-form-part col="sm-12" label="Total" type="mask-money" name="DebetPelunasan_val" readonly />
                        <x-form-part type="hidden" name="KreditPelunasan" value="0" />
                        <x-form-part col="sm-12" label="Dibayarkan" type="mask-money" name="KreditPelunasan_val" readonly />
                        <x-form-part type="hidden" name="SisaPelunasan" value="0" />
                        <x-form-part col="sm-12" label="Sisa" type="mask-money" name="SisaPelunasan_val" readonly />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-base-modal>
