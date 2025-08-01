<?php



namespace App\Http\Repository\Task;

interface MemorialInterface
{
    public function getAllMemorial();
    public function getMemorialByNoBukti($NoBukti);
    public function getMemorialDetailByNoBukti($NoBukti);
    public function getDetailMemorialByNoBukti($NoBukti, $Tanggal, $Urut);
    public function store($data);
    public function storeMemorial($data);
    public function update($data);
    public function updateMemorial($data);
    public function delete($NoBukti);
    public function deleteMemorial($NoBukti, $Urut);
    public function setOtorisasi($data);
    public function getNomorBukti($tipe);
    
    // Hutang Piutang Methods
    public function getDataHutang($kode, $lawan);
    public function pelunasanHutang($request);
    public function hapusPelunasan($request);
}