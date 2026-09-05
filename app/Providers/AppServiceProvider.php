<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Vendors\LuminaVendors;
use App\Services\Vendors\Cp3000Vendor;
use App\Services\VendorResolver;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
