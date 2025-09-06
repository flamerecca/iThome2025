<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category')?->id ?? $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'min:1', 'max:50', 'unique:categories,name,'.$id],
            'slug' => ['nullable', 'string', 'min:1', 'max:50', 'unique:categories,slug,'.$id],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
