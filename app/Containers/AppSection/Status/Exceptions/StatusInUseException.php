<?php

namespace App\Containers\AppSection\Status\Exceptions;

use App\Ship\Parents\Exceptions\HttpException as ParentHttpException;
use Symfony\Component\HttpFoundation\Response;

final class StatusInUseException extends ParentHttpException
{
    /**
     * @param \Throwable $previous
     *
     * @return self
     */
    public static function becauseReferenced(\Throwable $previous): self
    {
        return new self(Response::HTTP_CONFLICT, 'Статус використовується вакансіями.', $previous);
    }
}
