<?php

namespace MuHasan\LaravelWinpay;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use MuHasan\LaravelWinpay\Winpay;
use Laravel\Lumen\Application as LumenApplication;

/**
 * Class WinpayServiceProvider
 * @package MuHasan\LaravelWinpay\Providers
 */
class WinpayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/config/laravel-winpay.php' => config_path('laravel-winpay.php'),
            ]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('laravel-winpay');
        }
    }

    public function register()
    {
        $this->app->bind('winpay', function ($app) {
            return new Winpay(
                config('laravel-winpay.winpay_host'),
                config('laravel-winpay.winpay_pk1'),
                config('laravel-winpay.winpay_pk2'),
                config('laravel-winpay.winpay_mk'),
                config('laravel-winpay.winpay_listener'),
            );
        });
    }
}