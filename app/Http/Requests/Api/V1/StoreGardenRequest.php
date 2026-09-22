<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreGardenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'location_type' => ['nullable', 'string', 'max:50'],
            'area_m2' => ['nullable', 'numeric', 'min:0'],
            'length_m' => ['nullable', 'numeric', 'min:0'],
            'width_m' => ['nullable', 'numeric', 'min:0'],
            'sunlight_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'shade_level' => ['nullable', 'string', 'max:50'],
            'water_source' => ['nullable', 'string', 'max:50'],
            'drainage_level' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,archived'],
            'cultivation_method_ids' => ['nullable', 'array'],
            'cultivation_method_ids.*' => ['uuid', 'exists:cultivation_methods,id'],
            'cultivation_method_codes' => ['nullable', 'array'],
            'cultivation_method_codes.*' => ['string', 'exists:cultivation_methods,code'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kebun wajib diisi.',
        ];
    }
}
