@extends('layouts.app', ['title' => 'SPK Form'])

@section('body')
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Form SPK (Surat Perintah Kerja)</h3>
                </div>
                <div class="card-body">
                    <form id="formSPK" method="POST" action="{{ route('produksi.spk.store') }}">
                @csrf
                <div class="row">
                            <!-- HEADER SECTION -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="TipeTransHd">Transaksi</label>
                                    <select class="form-control" name="TipeTransHd" id="TipeTransHd">
                                        <option value="SPK">SPK</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                        <div class="form-group">
                                    <label for="PerkiraanHd">No Urut</label>
                                    <input type="text" class="form-control" name="PerkiraanHd" id="PerkiraanHd" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                    <label for="NoUrut">-</label>
                                    <input type="text" class="form-control" name="NoUrut" id="NoUrut" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                    <label for="NoBukti">No Bukti</label>
                                    <input type="text" class="form-control" name="NoBukti" id="NoBukti" readonly>
                                </div>
                                        </div>
                                    </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                        <div class="form-group">
                                    <label for="Tanggal">Tanggal</label>
                                    <input type="date" class="form-control" name="Tanggal" id="Tanggal"
                                           value="{{ date('Y-m-d', strtotime(($periode->TAHUN ?? date('Y')) . '-' . ($periode->BULAN ?? '01') . '-01')) }}">
                                </div>
                                        </div>
                            <div class="col-md-9">
                                        <div class="form-group">
                                    <label for="Note">Keterangan</label>
                                    <input type="text" class="form-control" name="Note" id="Note">
                        </div>
                    </div>
                </div>

                        <!-- SPK SPECIFIC FIELDS -->
                <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="NoSO">No SO</label>
                                    <select class="form-control select2" name="NoSO" id="NoSO">
                                        <option value="">Pilih Sales Order</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="UrutSO">Urut SO</label>
                                    <input type="number" class="form-control" name="UrutSO" id="UrutSO" min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="KodeBOM">Kode BOM</label>
                                    <select class="form-control select2" name="KodeBOM" id="KodeBOM">
                                        <option value="">Pilih BOM</option>
                                    </select>
                                </div>
                            </div>
</div>

                        <!-- BARANG JADI -->
                    <div class="row">
                            <div class="col-md-4">
                            <div class="form-group">
                                    <label for="KodeBrg">Kode Barang Jadi</label>
                                    <select class="form-control select2" name="KodeBrg" id="KodeBrg">
                                        <option value="">Pilih Barang</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="NamaBrg">Nama Barang Jadi</label>
                                    <input type="text" class="form-control" name="NamaBrg" id="NamaBrg" readonly>
                        </div>
                            </div>
                        </div>

                        <!-- QUANTITY & SATUAN -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                    <label for="Qnt">Quantity</label>
                                    <input type="text" class="form-control money" name="Qnt" id="Qnt">
                                </div>
                            </div>
                            <div class="col-md-2">
                            <div class="form-group">
                                    <label for="NoSat">No Sat</label>
                                    <select class="form-control" name="NoSat" id="NoSat">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                    <label for="Satuan">Satuan</label>
                                    <input type="text" class="form-control" name="Satuan" id="Satuan" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="Isi">Isi</label>
                                    <input type="text" class="form-control money" name="Isi" id="Isi" readonly>
                        </div>
                            </div>
                        </div>

                        <!-- BATCH & EXPIRED -->
                    <div class="row">
                            <div class="col-md-4">
                            <div class="form-group">
                                    <label for="NoBatch">No Batch</label>
                                    <input type="text" class="form-control" name="NoBatch" id="NoBatch">
                                </div>
                            </div>
                            <div class="col-md-4">
                            <div class="form-group">
                                    <label for="TglExpired">Tgl Expired</label>
                                    <input type="date" class="form-control" name="TglExpired" id="TglExpired">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="TglSelesai">Tgl Selesai</label>
                                    <input type="date" class="form-control" name="TglSelesai" id="TglSelesai">
                        </div>
                            </div>
                        </div>

                        <!-- ADDITIONAL INFO -->
                    <div class="row">
                            <div class="col-md-3">
                            <div class="form-group">
                                    <label for="BiayaLain">Biaya Lain</label>
                                    <input type="text" class="form-control money" name="BiayaLain" id="BiayaLain">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="QntCetak">Qnt Cetak</label>
                                    <input type="text" class="form-control money" name="QntCetak" id="QntCetak">
                        </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="JenisSpk">Jenis SPK</label>
                                    <select class="form-control" name="JenisSpk" id="JenisSpk">
                                        <option value="0">Normal</option>
                                        <option value="1">Urgent</option>
                                        <option value="2">Rush</option>
                                    </select>
                        </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="IsClose">Status</label>
                                    <select class="form-control" name="IsClose" id="IsClose">
                                        <option value="0">Open</option>
                                        <option value="1">Close</option>
                                    </select>
                    </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Simpan SPK</button>
                                <a href="{{ route('produksi.spk.index') }}" class="btn btn-secondary">Kembali</a>
                            </div>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js-plugins')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery-maskmoney/jquery.maskMoney.js') }}"></script>
@endpush

@push('js')
<script src="{{ asset('assets/js/spk-form.js') }}"></script>
@endpush