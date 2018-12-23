<?php

namespace WesLal\NovaSettingsTool;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use WesLal\NovaSettingsTool\Http\Middleware\Authorize;
use WesLal\NovaSettingsTool\ValueObjects\SettingRegister;

/**
 * Class ToolServiceProvider
 * @package WesLal\NovaSettingsTool
 */
class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfigs();
        $this->loadTranslations();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'settings');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            $this->addJSData();
            SettingRegister::init();
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/settings')
                ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (Nova::runsMigrations()) {
            $this->loadMigrationsFrom(__DIR__.'/Migrations');
        }

        $this->mergeConfigFrom(
            $this->getConfigsPath(),
            'settings'
        );
    }

    /**
     * Get local package configuration path.
     *
     * @return string
     */
    private function getConfigsPath()
    {
        return __DIR__.'/../config/settings.php';
    }

    /**
     * Publish configuration file.
     *
     * @return void
     */
    private function publishConfigs() {
        $this->publishes([
            $this->getConfigsPath() => config_path('settings.php'),
        ], 'settings');
    }

    /**
     * Load the translations.
     *
     * @return void
     */
    private function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'settings');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/settings'),
            ], 'settings');
        }
    }

    /**
     * Add the config and translation data to the client side Nova component (Laravel Nova does this not by default).
     * @return void
     */
    public function addJSData()
    {
        Nova::provideToScript([
            'settings_tool' => [
                'translations' => [
                    'settings_title'        => trans('settings::settings.settings_title'),
                    'save_settings'         => trans('settings::settings.save_settings'),
                    'save_success'          => trans('settings::settings.save_success'),
                    'save_error'            => trans('settings::settings.save_error'),
                    'load_error'            => trans('settings::settings.load_error'),
                    'module_not_migrated'   => trans('settings::settings.module_not_migrated'),
                    'setting_tab_suffix'    => trans('settings::settings.setting_tab_suffix')
                ],
                'config' => [
                    'show_title'            => config('settings.show_title'),
                    'show_suffix'           => config('settings.show_suffix'),
                    'show_icons'            => config('settings.show_icons')
                ]
            ]
        ]);
    }
}
