<?php

namespace GemaDigital\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Translation\PotentiallyTranslatedString;

final class ScopedUniqueRule implements ValidationRule
{
    public function __construct(
        protected string $model,
        protected ?string $field = null,
        protected ?int $ignoreId = null,
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
            ignoreId: $state['ignoreId'] ?? null,
        );
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        $model = app($this->model);
        $field = $this->field ?? $model->getKeyName();

        /** @var Builder */
        $query = is_array($value)
            ? $model->whereIn($field, $value)
            : $model->where($field, $value);

        if ($this->ignoreId) {
            is_array($value)
                ? $model->whereNotIn($model->getKeyName(), $this->ignoreId)
                : $query->whereNot($model->getKeyName(), $this->ignoreId);

        }

        if ($query->count() > 0) {
            $fail('validation.unique');

            return;
        }
    }
}
