<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SPKDetailRequest extends FormRequest
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
            'KodeBrg' => 'required|string|max:20',
            'Qnt' => 'required|numeric|min:0.01',
            'NoSat' => 'required|integer|in:1,2,3',
            'Satuan' => 'required|string|max:10',
            'Isi' => 'nullable|numeric|min:0.01',
            'KodeBOMDet' => 'nullable|string|max:100'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'KodeBrg.required' => 'Kode Barang harus diisi',
            'KodeBrg.max' => 'Kode Barang maksimal 20 karakter',
            'Qnt.required' => 'Quantity harus diisi',
            'Qnt.numeric' => 'Quantity harus berupa angka',
            'Qnt.min' => 'Quantity minimal 0.01',
            'NoSat.required' => 'Nomor Satuan harus diisi',
            'NoSat.integer' => 'Nomor Satuan harus berupa angka',
            'NoSat.in' => 'Nomor Satuan harus 1, 2, atau 3',
            'Satuan.required' => 'Satuan harus diisi',
            'Satuan.max' => 'Satuan maksimal 10 karakter',
            'Isi.numeric' => 'Isi harus berupa angka',
            'Isi.min' => 'Isi minimal 0.01',
            'KodeBOMDet.max' => 'Keterangan maksimal 100 karakter'
        ];
    }
} 