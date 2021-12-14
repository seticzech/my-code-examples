<?php

namespace App\Facades\Doctrine\Filters;

use App\Base\Doctrine\Filters\SoftDeleteableFilter;
use Illuminate\Support\Facades\Facade;


/**
 * @method static void disable()
 * @method static void disableForEntity(string $class)
 * @method static void disableForEntities(array $classes)
 * @method static void enable()
 * @method static void enableForEntity(string $class)
 * @method static void enableForEntities(array $classes)
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
        return SoftDeleteableFilter::class;
    }

}
