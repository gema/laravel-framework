<?php

namespace GemaDigital\Http\Requests\API;

use GemaDigital\Http\Requests\API\Traits\GateCheckTrait;
use Illuminate\Foundation\Http\FormRequest;

class GateCheckRequest extends FormRequest
{
    use GateCheckTrait;

    /**
     * Define the body parameters for Scribe documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [];
    }
}
