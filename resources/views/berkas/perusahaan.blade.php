@extends('layouts.app', ['title' => 'Perusahaan'])
@push('css')
    <link rel="stylesheet" href="{{ asset('assets/plugins/dropify/css/dropify.min.css') }}">
@endpush
@section('body')
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0 d-block d-sm-flex">
                    <ul class="nav nav-tabs w-100" id="custom-tabs-four-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="perusahhaan-tab" data-toggle="pill" href="#perusahhaan" role="tab" aria-controls="perusahhaan" aria-selected="false">Perusahaan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="nomorTransaksi-tab" data-toggle="pill" href="#nomorTransaksi" role="tab" aria-controls="nomorTransaksi" aria-selected="false">Set Nomor Transaksi</a>
                        </li>
                    </ul>
                    <button class="btn btn-primary d-none d-sm-block btnSave"><i class="fa-fa-save mr-2"></i>Simpan</button>
                    <button class="btn btn-primary d-block d-sm-none btn-block btnSave"><i class="fa-fa-save mr-2"></i>Simpan</button>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="customTab">
                        <div class="tab-pane fade active show" id="perusahhaan" role="tabpanel" aria-labelledby="perusahhaan-tab">
                            <form action="{{ route('berkas.perusahaan.update', ['id' => $perusahaan->KODEUSAHA, 'type' => 'perusahaan']) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <x-form-part col="md-12" label="Nama Perusahaan" type="text" name="NAMA" id="NAMA" placeholder="Masukkan Nama Perusahaan" value="{{ old('NAMA') ?? $perusahaan->NAMA }}" />
                                            <x-form-part col="md-12" label="Alamat" type="text" name="ALAMAT1" id="ALAMAT1" placeholder="Masukkan Alamat Perusahaan" value="{{ old('ALAMAT1') ?? $perusahaan->ALAMAT1 }}" />
                                            <x-form-part col="md-12" type="text" name="ALAMAT2" id="ALAMAT2" placeholder="Masukkan Alamat Perusahaan" value="{{ old('ALAMAT2') ?? $perusahaan->ALAMAT2 }}" />
                                            <x-form-part col="md-12" label="Kota" type="text" name="KOTA" id="KOTA" placeholder="Masukkan Kota" value="{{ old('KOTA') ?? $perusahaan->KOTA }}" />
                                            <x-form-part col="md-12" label="E-Mail" type="email" name="email" id="email" placeholder="Masukkan Email" value="{{ old('email') ?? $perusahaan->email }}" />
                                            <x-form-part col="md-6" label="Telp." type="number-text" name="Telpon" id="Telpon" placeholder="Masukkan Nomor Telp" value="{{ old('Telpon') ?? $perusahaan->Telpon }}" />
                                            <x-form-part col="md-6" label="Fax" type="number-text" name="Fax" id="Fax" placeholder="Masukkan Fax" value="{{ old('Fax') ?? $perusahaan->Fax }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card card-primary card-outline card-outline-tabs">
                                            <div class="card-header p-0 border-bottom-0">
                                                <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" id="NPWP1-tab" data-toggle="pill" href="#NPWP1" role="tab" aria-controls="NPWP1" aria-selected="false">NPWP1</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" id="NPWP2-tab" data-toggle="pill" href="#NPWP2" role="tab" aria-controls="NPWP2" aria-selected="false">NPWP2</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="card-body">
                                                <div class="tab-content" id="customTab">
                                                    <div class="tab-pane fade active show" id="NPWP1" role="tabpanel" aria-labelledby="NPWP1-tab">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="row">
                                                                    <x-form-part col="md-12" label="Nama PKP" type="text" name="NAMAPKP" id="NAMAPKP" placeholder="Masukkan Nama PKP" value="{{ old('NAMAPKP') ?? $perusahaan->NAMAPKP }}" />
                                                                    <x-form-part col="md-12" label="Alamat PKP" type="text" name="ALAMATPKP1" id="ALAMATPKP1" placeholder="Masukkan Alamat PKP" value="{{ old('ALAMATPKP1') ?? $perusahaan->ALAMATPKP1 }}" />
                                                                    <x-form-part col="md-12" type="text" name="ALAMATPKP2" id="ALAMATPKP2" placeholder="Masukkan Alamat PKP" value="{{ old('ALAMATPKP2') ?? $perusahaan->ALAMATPKP2 }}" />
                                                                    <x-form-part col="md-12" label="NPWP" type="number-text" name="NPWP" id="NPWP" placeholder="Masukkan NPWP" value="{{ old('NPWP') ?? $perusahaan->NPWP }}" />
                                                                    <x-form-part col="md-6" label="Kota PKP" type="text" name="KOTAPKP" id="KOTAPKP" placeholder="Masukkan Kota PKP" value="{{ old('KOTAPKP') ?? $perusahaan->KOTAPKP }}" />
                                                                    <x-form-part col="md-6" label="Tanggal Pengukuhan" type="date" name="TGLPENGUKUHAN" id="TGLPENGUKUHAN" placeholder="dd/mm/yyyy"
                                                                        value="{{ date('Y-m-d', strtotime(old('TGLPENGUKUHAN'))) ?? date('Y-m-d', strtotime($perusahaan->TGLPENGUKUHAN)) }}" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="NPWP2" role="tabpanel" aria-labelledby="NPWP2-tab">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="row">
                                                                    <x-form-part col="md-12" label="Nama PKP" type="text" name="NAMAPKP1" id="NAMAPKP1" placeholder="Masukkan Nama PKP" value="{{ old('NAMAPKP1') ?? $perusahaan->NAMAPKP1 }}" />
                                                                    <x-form-part col="md-12" label="Alamat PKP" type="text" name="ALAMATPKP21" id="ALAMATPKP21" placeholder="Masukkan Alamat PKP" value="{{ old('ALAMATPKP21') ?? $perusahaan->ALAMATPKP21 }}" />
                                                                    <x-form-part col="md-12" type="text" name="ALAMATPKP22" id="ALAMATPKP22" placeholder="Masukkan Alamat PKP" value="{{ old('ALAMATPKP22') ?? $perusahaan->ALAMATPKP22 }}" />
                                                                    <x-form-part col="md-12" label="NPWP" type="number-text" name="NPWP1" id="NPWP1" placeholder="Masukkan NPWP1" value="{{ old('NPWP1') ?? $perusahaan->NPWP1 }}" />
                                                                    <x-form-part col="md-6" label="Kota PKP" type="text" name="KOTAPKP1" id="KOTAPKP1" placeholder="Masukkan Kota PKP" value="{{ old('KOTAPKP1') ?? $perusahaan->KOTAPKP1 }}" />
                                                                    <x-form-part col="md-6" label="Tanggal Pengukuhan" type="date" name="TGLPENGUKUHAN1" id="TGLPENGUKUHAN1" placeholder="dd/mm/yyyy"
                                                                        value="{{ date('Y-m-d', strtotime(old('TGLPENGUKUHAN1'))) ?? date('Y-m-d', strtotime($perusahaan->TGLPENGUKUHAN1)) }}" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="row">
                                                                    <x-form-part col="md-6" label="Penandatanan FPJ" type="text" name="Direksi" id="Direksi" placeholder="Masukkan Penandatangan FPJ" value="{{ old('Direksi') ?? $perusahaan->Direksi }}" />
                                                                    <x-form-part col="md-6" label="Jabatan" type="text" name="Jabatan" id="Jabatan" placeholder="Masukkan Jabatan" value="{{ old('Jabatan') ?? $perusahaan->Jabatan }}" />
                                                                    <x-form-part col="md-6" label="TTD" type="dropify" name="TTD_PATH" id="TTD_PATH" data-default-file="{{ $perusahaan->TTDPATH }}" data-max-file-size="1M" data-allowed-file-extensions="bmp" accept=".bmp" />
                                                                    <x-form-part col="md-6" label="Logo" type="dropify" name="LOGO_PATH" id="LOGO_PATH" data-default-file="{{ $perusahaan->LOGOPATH }}" data-max-file-size="1M" data-allowed-file-extensions="bmp" accept=".bmp" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.card -->
                                        </div>
                                        <!-- /.card -->
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="nomorTransaksi" role="tabpanel" aria-labelledby="nomorTransaksi-tab">
                            <form action="{{ route('berkas.perusahaan.update', ['id' => $perusahaan->KODEUSAHA, 'type' => 'nomor']) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Kode Transaksi</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <x-form-part col="sm-4" label="Kas Masuk" type="text" name="BKM" placeholder="Masukkan BKM" value="{{ old('BKM') ?? $nomor->BKM }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NOBKM" placeholder="NO BKM" value="{{ old('NOBKM') ?? $nomor->NOBKM }}" />
                                                    <x-form-part col="sm-4" label="Kas Keluar" type="text" name="BKK" placeholder="Masukkan BKK" value="{{ old('BKK') ?? $nomor->BKK }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NOBKK" placeholder="NO BKK" value="{{ old('NOBKK') ?? $nomor->NOBKK }}" />
                                                    <x-form-part col="sm-4" label="Bank Masuk" type="text" name="BKM" placeholder="Masukkan BKM" value="{{ old('BKM') ?? $nomor->BKM }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NOBKM" placeholder="NO BKM" value="{{ old('NOBKM') ?? $nomor->NOBKM }}" />
                                                    <x-form-part col="sm-4" label="Bank Keluar" type="text" name="BBK" placeholder="Masukkan BBK" value="{{ old('BBK') ?? $nomor->BBK }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NOBBK" placeholder="NO BBK" value="{{ old('NOBBK') ?? $nomor->NOBBK }}" />
                                                    <x-form-part col="sm-4" label="Bukti Memorial" type="text" name="BMM" placeholder="Masukkan BMM" value="{{ old('BMM') ?? $nomor->BMM }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NOBMM" placeholder="NO BMM" value="{{ old('NOBMM') ?? $nomor->NOBMM }}" />
                                                    <x-form-part col="sm-4" label="Jurnal Koreksi" type="text" name="BJK" placeholder="Masukkan BJK" value="{{ old('BJK') ?? $nomor->BJK }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NOBJK" placeholder="NO BJK" value="{{ old('NOBJK') ?? $nomor->NOBJK }}" />
                                                    <hr>
                                                    <x-form-part col="sm-4" label="SO" type="text" name="SO" placeholder="Masukkan SO" value="{{ old('SO') ?? $nomor->SO }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NOSO" placeholder="NO SO" value="{{ old('NOSO') ?? $nomor->NOSO }}" />
                                                    <x-form-part col="sm-4" label="Perintah Pengiriman" type="text" name="SPP" placeholder="Masukkan SPP" value="{{ old('SPP') ?? $nomor->SPP }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NOSPP" placeholder="NO SPP" value="{{ old('NOSPP') ?? $nomor->NOSPP }}" />
                                                    <x-form-part col="sm-4" label="Pengiriman Barang" type="text" name="SPB" placeholder="Masukkan SPB" value="{{ old('SPB') ?? $nomor->SPB }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NOSPB" placeholder="NO SPB" value="{{ old('NOSPB') ?? $nomor->NOSPB }}" />
                                                    <x-form-part col="sm-4" label="Invoice Penjualan" type="text" name="INVC" placeholder="Masukkan INVC" value="{{ old('INVC') ?? $nomor->INVC }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NoINVC" placeholder="NO INVC" value="{{ old('NoINVC') ?? $nomor->NoINVC }}" />
                                                    <x-form-part col="sm-4" label="Retur Penjualan" type="text" name="RPJ" placeholder="Masukkan RPJ" value="{{ old('RPJ') ?? $nomor->RPJ }}" />
                                                    <x-form-part col="sm-2" class="mt-2" label="" type="text" name="NORPJ" placeholder="NO RPJ" value="{{ old('NORPJ') ?? $nomor->NORPJ }}" />
                                                    <x-form-part col="sm-4" label="Inisial Perusahaan" type="text" name="ALIAS" placeholder="Masukkan ALIAS" value="{{ old('ALIAS') ?? $nomor->ALIAS }}" />
                                                    <x-form-part col="sm-2" label="Tag" type="text" name="INICAB" placeholder="Tag" value="{{ old('INICAB') ?? $nomor->INICAB }}" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Konfigurasi</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <x-form-part col="md-12" label="Pemisah" type="select2" name="PEMISAH" id="PEMISAH">
                                                        <option value="0" {{ $nomor->PEMISAH == 0 ? 'selected' : '' }}>:</option>
                                                        <option value="1" {{ $nomor->PEMISAH == 1 ? 'selected' : '' }}>-</option>
                                                        <option value="2" {{ $nomor->PEMISAH == 2 ? 'selected' : '' }}>/</option>
                                                        <option value="3" {{ $nomor->PEMISAH == 3 ? 'selected' : '' }}> </option>
                                                    </x-form-part>
                                                    <hr>
                                                    <h4>Format Nomor Transaksi</h4>
                                                    @for ($i = 1; $i <= 4; $i++)
                                                        <x-form-part col="md-12" type="select2" name="FORMAT{{ $i }}" id="FORMAT{{ $i }}">
                                                            <option value="0" {{ $nomor->{'FORMAT' . $i} == 0 ? 'selected' : '' }}>Inisial Perusahhaan</option>
                                                            <option value="1" {{ $nomor->{'FORMAT' . $i} == 1 ? 'selected' : '' }}>Kode Transaksi</option>
                                                            <option value="2" {{ $nomor->{'FORMAT' . $i} == 2 ? 'selected' : '' }}>MMYY</option>
                                                            <option value="3" {{ $nomor->{'FORMAT' . $i} == 3 ? 'selected' : '' }}>MMYYYY</option>
                                                            <option value="4" {{ $nomor->{'FORMAT' . $i} == 4 ? 'selected' : '' }}>Nomor Urut</option>
                                                            <option value="5" {{ $nomor->{'FORMAT' . $i} == 5 ? 'selected' : '' }}>YYMM</option>
                                                            <option value="6" {{ $nomor->{'FORMAT' . $i} == 6 ? 'selected' : '' }}>YYYYMM</option>
                                                        </x-form-part>
                                                    @endfor
                                                    <x-form-part col="md-12" label="Reset" type="select2" name="Reset" id="Reset">
                                                        <option value="0" {{ $nomor->Reset == 0 ? 'selected' : '' }}>Bulan</option>
                                                        <option value="1" {{ $nomor->Reset == 1 ? 'selected' : '' }}>Tahun</option>
                                                    </x-form-part>
                                                    <x-form-part col="md-12" label="Contoh" type="text" name="Contoh" id="Contoh" placeholder="Masukkan Contoh" value="{{ old('Contoh') ?? $nomor->Contoh }}" readonly />
                                                    <x-form-part col="md-12" label="No Seri Faktur Pajak" type="text" name="NOSERI" id="NOSERI" placeholder="Masukkan NOSERI" value="{{ old('NOSERI') ?? $nomor->NOSERI }}" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
@endsection
@push('js')
    <script src="{{ asset('assets/plugins/dropify/js/dropify.min.js') }}"></script>
    <script src="{{ asset('assets/js/berkas/perusahaan.js') }}" type="module"></script>
@endpush
