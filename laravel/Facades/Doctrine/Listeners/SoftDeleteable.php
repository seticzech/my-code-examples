<?php

namespace App\Facades\Doctrine\Listeners;

use App\Base\Doctrine\Listeners\SoftDeleteableListener;
use Illuminate\Support\Facades\Facade;


/**
 * @method static void disable()
 * @method static void enable()
 * @method static bool isEnabled()
 */
class SoftDeleteable extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SoftDeleteableListener::class;
    }

}
