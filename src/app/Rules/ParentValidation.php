<?php

namespace GemaDigital\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ParentValidation implements ValidationRule
{
    public function __construct(
        protected ?Model $model = null,
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->model === null) {
            return;
        }

        if ($this->isDescendantOf($value)) {
            $fail('validation.parent_recursive')->translate([
                'model' => class_basename((string) $this->model),
            ]);
        }
    }

    /**
     * Check if this model is descendant of the given parent model or a recursive parent.
     */
    public function isDescendantOf(mixed $value): bool
    {
        if ($this->model === null) {
            return false;
        }

        if ($this->model->getKey() == $value) {
            return true;
        }

        $tableName = $this->model->getTable();
        $result = DB::select(
            "WITH RECURSIVE descendants AS (
                SELECT id, parent_id FROM $tableName WHERE parent_id = ?
                UNION ALL
                SELECT b.id, b.parent_id FROM $tableName b INNER JOIN descendants d ON b.parent_id = d.id
            )
            SELECT id FROM descendants WHERE id = ?;", [$this->model->getKey(), (int) $value]
        );

        return count($result) > 0;
    }
}
