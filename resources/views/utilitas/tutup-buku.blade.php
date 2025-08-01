@extends('layouts.app')

@section('title', 'Tutup Buku')

@section('body')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-book mr-2"></i>
                        Proses Tutup Buku
                    </h3>
                </div>
                <div class="card-body">
                    <form id="formTutupBuku" action="{{ route('utilitas.tutup-buku.proses') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bulan">Bulan</label>
                                    <select name="bulan" id="bulan" class="form-control" required>
                                        <option value="">Pilih Bulan</option>
                                        @foreach (range(1, 12) as $bulan)
                                            <option value="{{ $bulan }}" {{ ($periode->BULAN ?? date('n')) == $bulan ? 'selected' : '' }}>
                                                {{ str_pad($bulan, 2, '0', STR_PAD_LEFT) }} - {{ ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$bulan] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(!($periode->BULAN ?? false))
                                        <script>
                                            // Set default bulan if not set
                                            document.addEventListener('DOMContentLoaded', function() {
                                                document.getElementById('bulan').value = '{{ date('n') }}';
                                            });
                                        </script>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun">Tahun</label>
                                    <input type="number" name="tahun" id="tahun" class="form-control" 
                                           value="{{ $periode->TAHUN ?? date('Y') }}" min="2000" max="2099" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="jenis_proses">Jenis Proses</label>
                            <select name="jenis_proses" id="jenis_proses" class="form-control" required>
                                <option value="">Pilih Jenis Proses</option>
                                <option value="0">Semua</option>
                                <option value="1" selected>Proses Aktiva</option>
                                <option value="2">Hitung Ulang Neraca</option>
                                <option value="3">Hitung Ulang Aktiva</option>
                                <option value="4">HPP dan Rugi Laba</option>
                                <option value="5">Proses Dashboard</option>
                                <option value="6">Proses Aktiva Fiskal</option>
                                <option value="7">Hitung Ulang Aktiva Fiskal</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status Proses</label>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar">
                                    0%
                                </div>
                            </div>
                            <small class="text-muted" id="statusProses">Status: Menunggu proses...</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="btnProses">
                                <i class="fas fa-play mr-2"></i>
                                Proses
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                <i class="fas fa-times mr-2"></i>
                                Keluar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="{{ asset('assets/js/utilitas/tutup-buku.js') }}"></script>
@endpush
