<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DBFLPASSRequest extends FormRequest
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
            "keynik" => ['nullable', 'string', 'max:20'],
            "USERID" => ['required', 'string', 'max:25'],
            "FullName" => ['required', 'string', 'max:50'],
            "kodeBag" => ['required', 'string',],
            "KodeJab" => ['required', 'string',],
            "STATUS" => ['nullable', 'numeric', 'in:0,1'],
            "TINGKAT" => ['nullable', 'numeric', 'in:0,1,2'],
            "KodeKasir" => ['nullable', 'string','3'],
            "UID" => ['nullable', 'string', 'max:8'],
            "UID2" => ['required_unless:UID,null', 'max:8', 'same:UID'],
        ];
    }

    public function validated()
    {
        $data = parent::validated();
        if(method_exists($this, 'defaults')) {
            foreach($this->defaults() as $key => $value) {
                if(array_key_exists($key,$data)){
                    if($data[$key] === null) {
                        $data[$key] = $value;
                    }
                }else{
                    $data += [$key => $value];
                }
            }
        }
        return $data;
    }

    protected function defaults(){
        return [
            'HOSTID' => '',
            'IPAddres' => '',
            'Kodegdg' => null,
            'keynik' => null,
        ];
    }
}
