<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantingCycleRequest extends FormRequest
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
            'garden_id' => ['sometimes', 'uuid'],
            'commodity_id' => ['sometimes', 'uuid', 'exists:commodities,id'],
            'variety_id' => ['nullable', 'uuid', 'exists:commodity_varieties,id'],
            'template_id' => ['nullable', 'uuid', 'exists:cultivation_templates,id'],
            'cultivation_method_id' => ['nullable', 'uuid', 'exists:cultivation_methods,id'],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'start_date' => ['nullable', 'date'],
            'target_harvest_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'quantity_unit' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'cover_image_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
