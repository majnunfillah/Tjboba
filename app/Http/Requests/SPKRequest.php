<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class SPKRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'NoBukti' => 'required|string|max:20',
            'Tanggal' => 'required|date',
            'NoUrut' => 'required|integer|min:1',
            'KodeBrg' => 'required|string|max:20',
            'QntJ' => 'required|numeric|min:0.01',
            'NoSatJ' => 'required|integer|in:1,2,3',
            'SatJ' => 'required|string|max:10',
            'IsiJ' => 'nullable|numeric|min:0.01',
            'NoBatch' => 'nullable|string|max:20',
            'TglExpired' => 'required|date|after_or_equal:Tanggal',
            'KodeBOM' => 'nullable|string|max:20',
            'IsClose' => 'nullable|boolean',
            'NoSO' => 'nullable|string|max:20',
            'UrutSO' => 'nullable|integer|min:0',
            'BiayaLain' => 'nullable|numeric|min:0',
            'TglSelesai' => 'required|date|after_or_equal:Tanggal',
            'QntCetak' => 'nullable|numeric|min:0',
            'JenisSpk' => 'nullable|integer|in:0,1,2',
            'TglJTSO' => 'required|date|after_or_equal:Tanggal'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'NoBukti.required' => 'Nomor Bukti SPK harus diisi',
            'NoBukti.max' => 'Nomor Bukti SPK maksimal 20 karakter',
            'Tanggal.required' => 'Tanggal SPK harus diisi',
            'Tanggal.date' => 'Format tanggal tidak valid',
            'NoUrut.required' => 'Nomor Urut harus diisi',
            'NoUrut.integer' => 'Nomor Urut harus berupa angka',
            'NoUrut.min' => 'Nomor Urut minimal 1',
            'KodeBrg.required' => 'Kode Barang harus diisi',
            'KodeBrg.max' => 'Kode Barang maksimal 20 karakter',
            'QntJ.required' => 'Quantity harus diisi',
            'QntJ.numeric' => 'Quantity harus berupa angka',
            'QntJ.min' => 'Quantity minimal 0.01',
            'NoSatJ.required' => 'Nomor Satuan harus diisi',
            'NoSatJ.integer' => 'Nomor Satuan harus berupa angka',
            'NoSatJ.in' => 'Nomor Satuan harus 1, 2, atau 3',
            'SatJ.required' => 'Satuan harus diisi',
            'SatJ.max' => 'Satuan maksimal 10 karakter',
            'IsiJ.numeric' => 'Isi harus berupa angka',
            'IsiJ.min' => 'Isi minimal 0.01',
            'NoBatch.max' => 'Nomor Batch maksimal 20 karakter',
            'TglExpired.required' => 'Tanggal Expired harus diisi',
            'TglExpired.date' => 'Format tanggal expired tidak valid',
            'TglExpired.after_or_equal' => 'Tanggal Expired harus sama dengan atau setelah tanggal SPK',
            'KodeBOM.max' => 'Kode BOM maksimal 20 karakter',
            'NoSO.max' => 'Nomor SO maksimal 20 karakter',
            'UrutSO.integer' => 'Urut SO harus berupa angka',
            'UrutSO.min' => 'Urut SO minimal 0',
            'BiayaLain.numeric' => 'Biaya Lain harus berupa angka',
            'BiayaLain.min' => 'Biaya Lain minimal 0',
            'TglSelesai.required' => 'Tanggal Selesai harus diisi',
            'TglSelesai.date' => 'Format tanggal selesai tidak valid',
            'TglSelesai.after_or_equal' => 'Tanggal Selesai harus sama dengan atau setelah tanggal SPK',
            'QntCetak.numeric' => 'Quantity Cetak harus berupa angka',
            'QntCetak.min' => 'Quantity Cetak minimal 0',
            'JenisSpk.integer' => 'Jenis SPK harus berupa angka',
            'JenisSpk.in' => 'Jenis SPK harus 0, 1, atau 2',
            'TglJTSO.required' => 'Tanggal JT SO harus diisi',
            'TglJTSO.date' => 'Format tanggal JT SO tidak valid',
            'TglJTSO.after_or_equal' => 'Tanggal JT SO harus sama dengan atau setelah tanggal SPK'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic
            if ($this->has('TglSelesai') && $this->has('TglExpired')) {
                $tglSelesai = Carbon::parse($this->TglSelesai);
                $tglExpired = Carbon::parse($this->TglExpired);
                
                if ($tglSelesai->gt($tglExpired)) {
                    $validator->errors()->add('TglSelesai', 'Tanggal Selesai tidak boleh lebih besar dari Tanggal Expired');
                }
            }
        });
    }
} 