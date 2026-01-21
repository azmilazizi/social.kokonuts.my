<?php
namespace Modules\AppChannelThreadsUnofficial\Facades;

use Illuminate\Support\Facades\Facade;

class ThreadsUnofficial extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Modules\\AppChannelThreadsUnofficial\\Services\\ThreadsUnofficialService';
    }
}
