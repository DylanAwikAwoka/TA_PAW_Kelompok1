<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pemesan' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'jumlah' => [
                'required',
                'integer',
                'min:1',
                'max:'.config('tiketwar.batas_pembelian_per_pesanan'),
            ],
            'ticket_type_id' => ['required', 'exists:ticket_types,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'jumlah.max' => 'Maksimal :max tiket per pesanan.',
        ];
    }
}
