<?php
namespace engine\route;

use engine\bootable\IBootable;

abstract class RouteBoot implements IBootable
{
    /**
    * @return IRoute::class[] array of IRoute classes 
    */
    abstract protected static function getIRoutelasses(): array;

    public static function boot(){

       foreach (static::getIRoutelasses() as $class) {
               $class::boot();

        }

    }

}
