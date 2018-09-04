<?php

namespace App\Providers;

use Blade;
use Auth;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Validator;
use App\Providers\LDAP\LDAPProvider;
use App\Providers\LDAP\Connection\AnonymousCredentials;
use App\Providers\LDAP\LDAPEloquentUserProvider;

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
        Auth::provider('ldapEloquent', function (Application $app, $config) {
            $ldapProvider = $app->make(LDAPProvider::class)
                ->setEndpoint($config['endpoint'])
                ->setBaseDN($config['base_dn'])
                ->setLookupUser($app->make(AnonymousCredentials::class));

            return $app->make(LDAPEloquentUserProvider::class)->setModel($config['model'])->setLdapProvider($ldapProvider)->setCreateUser(true);
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
