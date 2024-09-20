<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecodeInstructionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
//        dd($this->instructions);
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cooking_time' => 'sometimes|nullable|integer|min:1',
            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
            'instructions' => 'sometimes|nullable|array',
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.measurement' => 'required|string',
        ];
    }

    /**
     * Modify the input data before validation.
     */
    protected function prepareForValidation(): void
    {
        $instructions = json_decode($this->input('instructions'), true);

        $filteredInput = $this->only([
            'title',
            'description',
            'photo',
            'cooking_time',
            'user_id',
            'category_id',
            'instructions',
            'ingredients'
        ]);

        $this->merge(array_merge($filteredInput, [
            'instructions' => $instructions
        ]));
    }
}
