<?php

namespace App\Containers\AppSection\Resume\Exceptions;

use App\Ship\Parents\Exceptions\HttpException as ParentHttpException;
use Symfony\Component\HttpFoundation\Response;

final class ResumePdfUnavailable extends ParentHttpException
{
    /**
     * @return self
     */
    public static function becauseResumeIsMissing(): self
    {
        return new self(Response::HTTP_NOT_FOUND, 'Резюме не знайдено.');
    }

    /**
     * @param \Throwable $previous
     *
     * @return self
     */
    public static function becausePdfServiceFailed(\Throwable $previous): self
    {
        return new self(Response::HTTP_SERVICE_UNAVAILABLE, 'Генерація PDF тимчасово недоступна. Спробуйте пізніше.', $previous);
    }
}
