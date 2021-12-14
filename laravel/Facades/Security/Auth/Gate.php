<?php

namespace App\Facades\Security\Auth;

use App\Domain\Entities\User;
use App\Security\Auth\Gate as GateAccessor;
use Illuminate\Support\Facades\Gate as GateFacade;


/**
 * @method static string|null getAccessType(string $ability, string $class)
 * @method static User|null getUser()
 */
class Gate extends GateFacade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return GateAccessor::class;
    }

}
