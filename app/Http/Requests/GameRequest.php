<?php

namespace App\Http\Requests;

use App\Enums\GameFormat;
use App\Models\Game;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Game|null $game */
        $game = $this->route('game');

        return $game === null || $this->user()?->can('update', $game) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $format = GameFormat::tryFrom((string) $this->input('format'));
        $size = $format?->playersPerTeam() ?? 1;
        $ownPlayer = Rule::exists('players', 'id')->where('user_id', $this->user()?->id);

        return [
            'format' => ['required', Rule::enum(GameFormat::class)],
            'played_on' => ['required', 'date', 'before_or_equal:today'],
            'location' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'team_a' => ['required', 'array', "size:{$size}"],
            'team_a.*' => ['required', 'integer', 'distinct', $ownPlayer],
            'team_b' => ['required', 'array', "size:{$size}"],
            'team_b.*' => ['required', 'integer', 'distinct', $ownPlayer],
            'team_a_score' => ['required', 'integer', 'min:0', 'max:99'],
            'team_b_score' => ['required', 'integer', 'min:0', 'max:99', 'different:team_a_score'],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $teamA = (array) $this->input('team_a', []);
                $teamB = (array) $this->input('team_b', []);

                if (array_intersect($teamA, $teamB) !== []) {
                    $validator->errors()->add('team_b', 'A player cannot be on both teams.');
                }
            },
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
            'team_a.size' => 'Pick :size player(s) for Team A.',
            'team_b.size' => 'Pick :size player(s) for Team B.',
            'team_a.*.distinct' => 'Each player can only be picked once.',
            'team_b.*.distinct' => 'Each player can only be picked once.',
            'team_b_score.different' => 'Games cannot end in a tie.',
            'played_on.before_or_equal' => 'The date cannot be in the future.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'played_on' => 'date',
            'team_a' => 'Team A',
            'team_b' => 'Team B',
            'team_a_score' => 'Team A score',
            'team_b_score' => 'Team B score',
        ];
    }
}
