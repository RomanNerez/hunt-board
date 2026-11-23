<?php

namespace App\Containers\AppSection\Stack\Tests\Fixtures;

use App\Containers\AppSection\Stack\Models\Concerns\HasStacks;
use App\Containers\AppSection\Stack\Models\Contracts\Stackable;
use App\Ship\Parents\Models\Model as ParentModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @property int $id
 */
final class StackOwner extends ParentModel implements Stackable
{
    use HasStacks;

    /**
     * @var string|null
     */
    protected $table = 'stack_owners';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return void
     */
    public static function createTable(): void
    {
        Schema::create('stack_owners', static function (Blueprint $table): void {
            $table->id();
        });
    }
}
