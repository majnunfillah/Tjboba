class TutupBukuService
{
    public function prosesSemua($bulan, $tahun)
    {
        $this->prosesAktiva($bulan, $tahun);
        $this->hitungUlangAktiva($bulan, $tahun);
        $this->hitungUlangAktivaFiskal($bulan, $tahun);
        $this->prosesAktivaFiskal($bulan, $tahun);
        $this->hitungUlangNeraca($bulan, $tahun);
        $this->prosesHppDanLabaRugi($bulan, $tahun);
        $this->prosesPerDevisi($bulan, $tahun);
    }

    public function prosesAktiva($bulan, $tahun)
    {
        // Logika Proses Aktiva
    }
    
    // Tambahkan metode lainnya seperti hitungUlangAktiva, prosesAktivaFiskal, dll.
}