<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Vendors\LuminaVendors;
use App\Services\Vendors\Cp3000Vendor;
use App\Services\VendorResolver;
use App\Services\Commands\CommandSenderResolver;
use App\Services\Commands\LuminaCommandSender;
use App\Services\Commands\Cp3000CommandSender;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
        $this->app->singleton(VendorResolver::class, function () {
            return new VendorResolver([
                new LuminaVendors(),
                new Cp3000Vendor(),
            ]);
        });
         $this->app->singleton(CommandSenderResolver::class, function () {
            return new CommandSenderResolver([
                new LuminaCommandSender(),
                new Cp3000CommandSender(),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
