<?php

namespace Modules\Virkdata\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanySearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'query.required' => 'Angiv venligst firmanavn eller CVR-nummer',
        ];
    }

    /**
     * Fortæl at query kommer fra route parameterne
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'query' => $this->route('query'),
        ]);
    }
}
