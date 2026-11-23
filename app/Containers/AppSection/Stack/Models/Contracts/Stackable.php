<?php

namespace App\Containers\AppSection\Stack\Models\Contracts;

use App\Containers\AppSection\Stack\Models\Stack;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface Stackable
{
    /**
     * @return MorphToMany<Stack, covariant Model>
     */
    public function stacks(): MorphToMany;
}
