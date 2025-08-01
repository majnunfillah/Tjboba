<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerkiraanRequest extends FormRequest
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
            "Perkiraan" => ['required', 'string', 'max:25'],
            "Keterangan" => ['required', 'string', 'max:8000'],
            "Kelompok" => ['required', 'numeric'],
            "Tipe" => ['required', 'numeric'],
            "Valas" => ['required', 'string', 'max:10'],
            "DK" => ['required', 'numeric'],
            "Simbol" => ['nullable', 'string', 'max:3'],
            "KodeAK" => ['nullable', 'string', 'max:15'],
            "KodeSAK" => ['nullable', 'string', 'max:15'],
        ];
    }

    public function validated()
    {
        $data = parent::validated();
        if (method_exists($this, 'defaults')) {
            foreach ($this->defaults() as $key => $value) {
                if(!array_key_exists($key, $data)){
                    $data += [$key =>  $value];
                }else if ($data[$key] === null) {
                    $data[$key] = $value;
                }
            }
        }
        return $data;
    }

    protected function defaults()
    {
        return [
            'Neraca' => '',
            'FlagCashFlow' => '',
            'Simbol' => ''
        ];
    }
}
