<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class SPKMesinRequest extends FormRequest
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
            'KODEMSN' => 'required|string|max:20',
            'Tanggal' => 'required|date',
            'JAMAWAL' => 'nullable|date_format:H:i',
            'JAMAKHIR' => 'nullable|date_format:H:i|after:JAMAWAL',
            'QNTSPK' => 'required|numeric|min:0.01',
            'Keterangan' => 'nullable|string|max:200',
            'TarifMesin' => 'nullable|numeric|min:0',
            'JamTenaker' => 'nullable|numeric|min:0',
            'JmlTenaker' => 'nullable|integer|min:0',
            'TarifTenaker' => 'nullable|numeric|min:0'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'KODEMSN.required' => 'Kode Mesin harus diisi',
            'KODEMSN.max' => 'Kode Mesin maksimal 20 karakter',
            'Tanggal.required' => 'Tanggal harus diisi',
            'Tanggal.date' => 'Format tanggal tidak valid',
            'JAMAWAL.date_format' => 'Format jam awal harus HH:mm',
            'JAMAKHIR.date_format' => 'Format jam akhir harus HH:mm',
            'JAMAKHIR.after' => 'Jam akhir harus setelah jam awal',
            'QNTSPK.required' => 'Quantity SPK harus diisi',
            'QNTSPK.numeric' => 'Quantity SPK harus berupa angka',
            'QNTSPK.min' => 'Quantity SPK minimal 0.01',
            'Keterangan.max' => 'Keterangan maksimal 200 karakter',
            'TarifMesin.numeric' => 'Tarif Mesin harus berupa angka',
            'TarifMesin.min' => 'Tarif Mesin minimal 0',
            'JamTenaker.numeric' => 'Jam Tenaker harus berupa angka',
            'JamTenaker.min' => 'Jam Tenaker minimal 0',
            'JmlTenaker.integer' => 'Jumlah Tenaker harus berupa angka bulat',
            'JmlTenaker.min' => 'Jumlah Tenaker minimal 0',
            'TarifTenaker.numeric' => 'Tarif Tenaker harus berupa angka',
            'TarifTenaker.min' => 'Tarif Tenaker minimal 0'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional custom validation logic
            if ($this->has('JAMAWAL') && $this->has('JAMAKHIR')) {
                $jamAwal = Carbon::parse($this->JAMAWAL);
                $jamAkhir = Carbon::parse($this->JAMAKHIR);
                
                if ($jamAwal->gte($jamAkhir)) {
                    $validator->errors()->add('JAMAKHIR', 'Jam akhir harus setelah jam awal');
                }
            }
        });
    }
} 