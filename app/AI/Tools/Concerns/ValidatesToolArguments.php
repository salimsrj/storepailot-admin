<?php

namespace App\AI\Tools\Concerns;

use App\Exceptions\InvalidToolArgumentsException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait ValidatesToolArguments
{
    /**
     * @param  array<string, mixed>  $arguments
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    protected function validatedArguments(string $tool, array $arguments, array $rules): array
    {
        if (array_diff_key($arguments, $rules) !== []) {
            throw InvalidToolArgumentsException::forTool($tool);
        }

        try {
            return Validator::validate($arguments, $rules);
        } catch (ValidationException) {
            throw InvalidToolArgumentsException::forTool($tool);
        }
    }
}
