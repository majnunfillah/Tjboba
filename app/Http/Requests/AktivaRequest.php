<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AktivaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "NoMuka" => ['required_if:_method,POST', 'string'],
            "Devisi" => ['required_if:_method,POST', 'numeric'],
            "NoBelakang" => ['required', 'numeric'],
            "Perkiraan" => ['required', 'string', 'max:30'],
            "TipeAktiva" => ['required', 'numeric'],
            "Tanggal" => ['required', 'date'],
            "Keterangan" => ['required', 'string', 'max:500'],
            "Quantity" => ['required', 'numeric'],
            "Persen" => ['required', 'numeric'],
            "Tipe" => ['required', 'string'],
            "Akumulasi" => ['required', 'string'],
            "Biaya" => ['required', 'string'],
            "PersenBiaya1" => ['required', 'numeric'],
            "Biaya2" => ['nullable', 'string'],
            "PersenBiaya2" => ['nullable', 'string'],
            "Biaya3" => ['nullable', 'string'],
            "persenbiaya3" => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'NoMuka.required_if' => 'No Muka harus diisi',
            'Devisi.required_if' => 'Devisi harus diisi',
            'NoBelakang.required' => 'No Belakang harus diisi',
            'Perkiraan.required' => 'Perkiraan harus diisi',
            'TipeAktiva.required' => 'Tipe Aktiva harus diisi',
            'Tanggal.required' => 'Tanggal harus diisi',
            'Keterangan.required' => 'Keterangan harus diisi',
            'Quantity.required' => 'Quantity harus diisi',
            'Persen.required' => 'Persen harus diisi',
            'Tipe.required' => 'Tipe harus diisi',
            'Akumulasi.required' => 'Akumulasi harus diisi',
            'Biaya.required' => 'Biaya harus diisi',
            'PersenBiaya1.required' => 'Persen Biaya 1 harus diisi',
        ];
    }

    public function validated()
    {
        $data = parent::validated();
        if(method_exists($this, 'defaults')) {
            foreach($this->defaults() as $key => $value) {
                if($data[$key] === null) {
                    $data[$key] = $value;
                }
            }
        }
        return $data;
    }

    protected function defaults(){
        return [
            'Biaya2' => '',
            'PersenBiaya2' => 0,
            'Biaya3' => '',
            'persenbiaya3' => 0,
        ];
    }
}
