<?php

namespace App\Containers\AppSection\Stack\Tasks\Concerns;

use App\Containers\AppSection\Stack\Models\Stack;
use Illuminate\Validation\ValidationException;

trait ValidatesStackName
{
    /**
     * @param mixed $value
     * @param int|null $ignoredId
     *
     * @return string
     *
     * @throws ValidationException
     */
    private function validatedStackName(mixed $value, int|null $ignoredId = null): string
    {
        $name = is_string($value) ? trim($value) : '';

        if ('' === $name) {
            throw ValidationException::withMessages(['name' => __('validation.required', ['attribute' => 'назва'])]);
        }

        if (mb_strlen($name) > Stack::NAME_MAX_LENGTH) {
            throw ValidationException::withMessages(['name' => __('validation.max.string', ['attribute' => 'назва', 'max' => Stack::NAME_MAX_LENGTH])]);
        }

        if (Stack::nameIsTaken($name, $ignoredId)) {
            throw ValidationException::withMessages(['name' => __('validation.unique', ['attribute' => 'назва'])]);
        }

        return $name;
    }
}
