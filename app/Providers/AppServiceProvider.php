<?php

namespace App\Providers;

use Blade;
use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @noinspection PhpUnusedParameterInspection */
        Validator::extend('codename', function ($attribute, $value, $parameters, $validator) {
            return (preg_match('/^[a-z][a-z0-9_]+$/i', $value));
        });
        Blade::directive('datetime', function ($expression) {
            return "<?php echo (new App\\Http\\DateMaker())->datetime{$expression}; ?>";
        });
        Blade::directive('link', function ($expression) {
            return "<?php echo '<a href=\"'.((new App\\Http\\LinkMaker())->url{$expression}).'\">'.htmlspecialchars((new App\\Http\\TitleMaker())->title{$expression}).'</a>'; ?>";
        });
        Blade::directive('url', function ($expression) {
            return "<?php echo (new App\\Http\\LinkMaker())->url{$expression}; ?>";
        });
        Blade::directive('title', function ($expression) {
            return "<?php echo htmlspecialchars((new App\\Http\\TitleMaker())->title{$expression}); ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
