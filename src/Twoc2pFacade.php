<?php

namespace Laraditz\Twoc2p;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Laraditz\Twoc2p\Skeleton\SkeletonClass
 */
class Twoc2pFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Twoc2p';
    }
}
