<?php

namespace GemaDigital\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ScopedExistsRule implements ValidationRule
{
    public function __construct(
        protected string $model,
        protected ?string $field = null,
    ) {}

    /**
     * Create a new instance from a var_export export.
     *
     * @param  array<string, mixed>  $state
     */
    public static function __set_state(array $state): static
    {
        return new self(
            model: $state['model'],
            field: $state['field'] ?? null,
        );
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $model = app($this->model);
        $field = $this->field ?? $model->getKeyName();

        if (is_array($value) && $model->whereIn($field, $value)->count() !== count($value)) {
            $fail(__('validation.exists'));

            return;
        }

        if ($model->where($field, $value)->count() === 0) {
            $fail(__('validation.exists'));

            return;
        }
    }
}
