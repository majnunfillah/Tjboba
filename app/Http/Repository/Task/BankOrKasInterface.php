<?php
namespace App\Http\Repository\Task;

use Illuminate\Http\Request;

interface BankOrKasInterface
{
  public function getAllBankOrKas();
  public function getKasBankByNoBukti($NoBukti);
  public function getKasBankDetailByNoBukti($NoBukti);
  public function setOtorisasi($request);
  public function getNomorBukti($tipe);
  public function store($request);
  public function update($request);
  public function delete($NoBukti);
  public function storeKasBank($request);
  public function updateKasBank($request);
  public function deleteKasBank($NoBukti, $Urut);
  public function getDetailKasBankByNoBukti($NoBukti, $Tanggal, $Urut);
  public function getDataHutang($kode, $lawan);
  public function pelunasanHutang($request);
  public function hapusPelunasan($request);
}
