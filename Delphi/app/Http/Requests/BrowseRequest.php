<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrowseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'kode_brows' => 'required|string',
            'filter' => 'nullable|string|max:255',
            'is_data' => 'nullable|string',
            'no_kira' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'kode_brows.required' => 'Jenis data harus dipilih',
            'filter.max' => 'Filter tidak boleh lebih dari 255 karakter',
        ];
    }
} 