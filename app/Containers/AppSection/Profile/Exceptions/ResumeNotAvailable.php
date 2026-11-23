<?php

namespace App\Containers\AppSection\Profile\Exceptions;

use App\Ship\Parents\Exceptions\HttpException as ParentHttpException;
use Symfony\Component\HttpFoundation\Response;

final class ResumeNotAvailable extends ParentHttpException
{
    /**
     * @return self
     */
    public static function becauseProfileIsMissing(): self
    {
        return new self(Response::HTTP_NOT_FOUND, 'Щоб отримати резюме, заповніть загальні відомості профілю.');
    }

    /**
     * @param \Throwable $previous
     *
     * @return self
     */
    public static function becausePdfServiceFailed(\Throwable $previous): self
    {
        return new self(Response::HTTP_SERVICE_UNAVAILABLE, 'Сервіс генерації PDF недоступний. Спробуйте пізніше.', $previous);
    }
}
