<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model; // <-- Pastikan import ini ada

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Fitur ini akan memblokir (memberi error) jika ada relasi database
        // yang dipanggil tanpa eager loading (N+1 Problem).
        // Fitur ini otomatis mati jika web sudah rilis di Production.
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
