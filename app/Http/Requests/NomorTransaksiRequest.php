<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NomorTransaksiRequest extends FormRequest
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
            "BKM" => ['required', 'string', 'max:5'],
            "NOBKM" => ['nullable', 'string', 'max:25'],
            "BKK" => ['required', 'string', 'max:5'],
            "NOBKK" => ['nullable', 'string', 'max:25'],
            "BBK" => ['required', 'string', 'max:5'],
            "NOBBK" => ['nullable', 'string', 'max:25'],
            "BMM" => ['required', 'string', 'max:5'],
            "NOBMM" => ['nullable', 'string', 'max:25'],
            "BJK" => ['required', 'string', 'max:5'],
            "NOBJK" => ['nullable', 'string', 'max:25'],
            "SO" => ['required', 'string', 'max:5'],
            "NOSO" => ['nullable', 'string', 'max:25'],
            "SPP" => ['required', 'string', 'max:5'],
            "NOSPP" => ['nullable', 'string', 'max:25'],
            "SPB" => ['required', 'string', 'max:5'],
            "NOSPB" => ['nullable', 'string', 'max:25'],
            "INVC" => ['required', 'string', 'max:5'],
            "NoINVC" => ['nullable', 'string', 'max:25'],
            "RPJ" => ['required', 'string', 'max:5'],
            "NORPJ" => ['nullable', 'string', 'max:25'],
            "ALIAS" => ['required', 'string', 'max:5'],
            "INICAB" => ['required', 'string', 'max:4'],
            "PEMISAH" => ['required', 'numeric', 'digits:1'],
            "FORMAT1" => ['required', 'numeric', 'digits:1'],
            "FORMAT2" => ['required', 'numeric', 'digits:1'],
            "FORMAT3" => ['required', 'numeric', 'digits:1'],
            "FORMAT4" => ['required', 'numeric', 'digits:1'],
            "Reset" => ['required', 'integer', 'digits:1'],
            "Contoh" => ['required', 'string', 'max:20'],
            "NOSERI" => ['nullable', 'string', 'max:50'],
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
            'NOSERI' => '',
        ];
    }
}