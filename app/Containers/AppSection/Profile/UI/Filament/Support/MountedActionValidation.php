<?php

namespace App\Containers\AppSection\Profile\UI\Filament\Support;

use Filament\Actions\Action;
use Illuminate\Validation\ValidationException;

final class MountedActionValidation
{
    /**
     * @template TResult
     *
     * @param Action $action
     * @param \Closure(): TResult $callback
     *
     * @return TResult
     *
     * @throws ValidationException
     */
    public static function run(Action $action, \Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (ValidationException $exception) {
            $prefix = "mountedActions.{$action->getNestingIndex()}.data.";
            $messages = [];

            foreach ($exception->errors() as $key => $errors) {
                $messages[$prefix . $key] = $errors;
            }

            throw ValidationException::withMessages($messages);
        }
    }
}
