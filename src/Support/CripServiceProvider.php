<?php namespace Crip\Core\Support;

use Crip\Core\Contracts\ICripObject;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * Class CripServiceProvider
 * @package Crip\Core\Support
 */
abstract class CripServiceProvider extends ServiceProvider implements ICripObject
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * @var array
     */
    private $publish = [];

    /**
     * @var PackageBase
     */
    private $package;

    /**
     * @param PackageBase $package
     */
    protected function cripBoot(PackageBase $package)
    {
        $this->package = $package;

        // init package translations
        if ($package->enable_translations) {
            $this->loadTranslationsFrom($package->path . '/resources/lang', $package->name);
        }

        // init package views
        if ($package->enable_views) {
            $this->loadViewsFrom($package->path . '/resources/views', $package->name);
            $this->publish[$package->path . '/resources/views'] = base_path('resources/views/vendor/' . $package->name);
        }

        // init router (should be initialised after loadViewsFrom if is using views)
        if (!$this->app->routesAreCached() && $package->enable_routes) {
            require $package->path . '/App/Routes.php';
        }

        if ($package->publish_public) {
            $this->publish[$package->path . '/public'] = $package->public_path;
        }

        if ($package->publish_database) {
            $this->publish[$package->path . '/database/migrations'] = database_path('migrations');
        }

        if (count($this->publish)) {
            $this->publishes($this->publish);
        }
        if ($package->publish_config) {
            $this->publishes([
                $package->path . '/config/' . $package->config_name . '.php' => config_path($package->name . '.php'),
            ], 'config');
        }
    }

    /**
     * @param PackageBase $package
     */
    public function cripRegister(PackageBase $package)
    {
        // merge package configuration file with the application's copy.
        if ($package->publish_config) {
            $this->mergeConfigFrom(
                $package->path . '/config/' . $package->name . '.php', $package->name
            );
        }

        // Shortcut so developers don't need to add an Alias in app/config/app.php
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $this->aliasLoader($loader);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [$this->package->name];
    }

    abstract function aliasLoader(AliasLoader $loader);
}