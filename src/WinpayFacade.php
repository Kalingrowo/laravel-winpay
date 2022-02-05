<?php

namespace MuHasan\LaravelWinpay;

use Illuminate\Support\Facades\Facade;

/**
 * Class WinpayFacade
 * @package MuHasan\LaravelWinpay\Facades
 */
class WinpayFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'winpay';
    }
}