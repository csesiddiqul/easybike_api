<?php

namespace App\Facades;

use App\Services\HandleResponseService;
use Illuminate\Support\Facades\Facade;

class HandleResponseFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return HandleResponseService::class;
    }
}
