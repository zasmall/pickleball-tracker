<?php

namespace App\Http\Requests;

use App\Models\Player;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Player|null $player */
        $player = $this->route('player');

        return $player === null || $this->user()?->can('update', $player) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Player|null $player */
        $player = $this->route('player');

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('players')
                    ->where('user_id', $this->user()?->id)
                    ->ignore($player?->id),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'You already have a player with that name.',
        ];
    }
}
