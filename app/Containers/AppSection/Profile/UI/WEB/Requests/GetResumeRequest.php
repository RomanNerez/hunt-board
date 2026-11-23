<?php

namespace App\Containers\AppSection\Profile\UI\WEB\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class GetResumeRequest extends ParentRequest
{
    /**
     * @var array<string>
     */
    protected array $decode = [];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return int
     */
    public function userId(): int
    {
        $id = $this->user()?->getAuthIdentifier();

        return is_numeric($id) ? (int) $id : 0;
    }
}
