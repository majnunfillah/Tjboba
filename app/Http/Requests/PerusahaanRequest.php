<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerusahaanRequest extends FormRequest
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
            "NAMA" => ['required', 'string', 'max:40'],
            "ALAMAT1" => ['required', 'string', 'max:100'],
            "ALAMAT2" => ['nullable', 'string', 'max:100'],
            "KOTA" => ['required', 'string', 'max:40'],
            "email" => ['nullable', 'email', 'string', 'max:100'],
            "Telpon" => ['required', 'string', 'max:30'],
            "Fax" => ['required', 'string', 'max:30'],
            "NAMAPKP" => ['required', 'string', 'max:40'],
            "ALAMATPKP1" => ['required', 'string', 'max:100'],
            "ALAMATPKP2" => ['nullable', 'string', 'max:100'],
            "NPWP" => ['required', 'string', 'max:40'],
            "KOTAPKP" => ['required', 'string', 'max:40'],
            "TGLPENGUKUHAN" => ['nullable', 'date'],
            "NAMAPKP1" => ['required', 'string', 'max:40'],
            "ALAMATPKP21" => ['required', 'string', 'max:100'],
            "ALAMATPKP22" => ['nullable', 'string', 'max:100'],
            "NPWP1" => ['required', 'string', 'max:40'],
            "KOTAPKP1" => ['required', 'string', 'max:40'],
            "TGLPENGUKUHAN1" => ['nullable', 'date'],
            "Direksi" => ['required', 'string', 'max:50'],
            "Jabatan" => ['required', 'string', 'max:50'],
            "TTD_PATH" => ['nullable', 'image', 'mimes:bmp'],
            "LOGO_PATH" => ['nullable', 'image', 'mimes:bmp']
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
            'ALAMAT2' => '',
            'ALAMATPKP1' => '',
            'ALAMATPKP2' => '',
            'ALAMATPKP21' => '',
            'ALAMATPKP22' => '',
        ];
    }
}
