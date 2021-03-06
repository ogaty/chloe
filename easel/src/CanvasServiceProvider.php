<?php

namespace Easel;

use App\Models\Settings;
use App\Helpers\RouteHelper;
use App\Helpers\SetupHelper;
use App\Helpers\CanvasHelper;
use App\Helpers\ConfigHelper;
use App\Console\Commands\Index;
use App\Console\Commands\Theme;
use App\Console\Commands\Update;
use App\Console\Commands\Install;
use App\Console\Commands\Version;
use Maatwebsite\Excel\Facades\Excel;
use App\Console\Commands\Uninstall;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\ScoutServiceProvider;
use Easel\Http\Middleware\CheckIfAdmin;
use App\Console\Commands\Publish\Views;
use App\Console\Commands\Publish\Assets;
use App\Console\Commands\Publish\Config;
use App\Http\Middleware\EnsureInstalled;
use Maatwebsite\Excel\ExcelServiceProvider;
use Easel\Http\Middleware\EnsureNotInstalled;
use App\Console\Commands\Publish\Migrations;
use Easel\Extensions\ExtensionsServiceProvider;
use TeamTNT\Scout\TNTSearchScoutServiceProvider;
//use App\Http\Middleware\CheckForMaintenanceMode;
use Larapack\ConfigWriter\Repository as ConfigWriter;
use Proengsoft\JsValidation\Facades\JsValidatorFacade;
use Proengsoft\JsValidation\JsValidationServiceProvider;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use TalvBansal\MediaManager\Providers\MediaManagerServiceProvider;

class CanvasServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * List of commands.
     *
     * @var array
     */
    protected $commands = [
        Index::class,
        Views::class,
        Theme::class,
        Update::class,
        Config::class,
        Assets::class,
        Install::class,
        Version::class,
        Uninstall::class,
    ];

    /**
     * Public asset files.
     */
    private function handleAssets()
    {
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/canvas'),
        ], 'public');
    }

    /**
     * Configuration files.
     */
    private function handleConfigs()
    {
        $configPath = __DIR__.'/../config/blog.php';

        // Allow publishing the config file, with tag: config
        $this->publishes([$configPath => config_path('blog.php')], 'config');

        // Merge config files...
        // Allows any modifications from the published config file to be seamlessly merged with default config file
        $this->mergeConfigFrom($configPath, 'blog');
    }

    /**
     * Translation files.
     */
    private function handleTranslations()
    {
        // Load translations...
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'canvas');
    }

    /**
     * View files.
     */
    private function handleViews()
    {
        // Load the views...
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'canvas');

        // Allow publishing view files, with tag: views
        $this->publishes([
            __DIR__.'/../resources/views/auth' => base_path('resources/views/vendor/canvas/auth'),
            __DIR__.'/../resources/views/backend' => base_path('resources/views/vendor/canvas/backend'),
            __DIR__.'/../resources/views/errors' => base_path('resources/views/vendor/canvas/errors'),
            __DIR__.'/../resources/views/frontend' => base_path('resources/views/vendor/canvas/frontend'),
            __DIR__.'/../resources/views/shared' => base_path('resources/views/vendor/canvas/shared'),
        ], 'views');
    }

    /**
     * Migration files.
     */
    private function handleMigrations()
    {
        // Load the migrations...
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Route files.
     */
    private function handleRoutes()
    {
    }

    /**
     * Command files.
     */
    private function handleCommands()
    {
        // Register the commands...
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Register factory files.
     *
     * @param  string  $path
     * @return void
     */
    protected function registerEloquentFactoriesFrom($path)
    {
        $this->app->make(EloquentFactory::class)->load($path);
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->handleConfigs();
        $this->handleMigrations();
        $this->handleViews();
        $this->handleTranslations();
        $this->handleRoutes();
        $this->handleCommands();
        $this->handleAssets();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $loader = AliasLoader::getInstance();
        $router = $this->app['router'];

        // Register factories...
        $this->registerEloquentFactoriesFrom(__DIR__.'/../database/factories');

        // Register service providers...
        $this->app->register(JsValidationServiceProvider::class);
        $this->app->register(ScoutServiceProvider::class);
        $this->app->register(ExcelServiceProvider::class);
        $this->app->register(MediaManagerServiceProvider::class);
        $this->app->register(TNTSearchScoutServiceProvider::class);
        $this->app->register(ExtensionsServiceProvider::class);

        // Register facades...
        $loader->alias('JsValidator', JsValidatorFacade::class);
        $loader->alias('ConfigWriter', ConfigWriter::class);
        $loader->alias('Excel', Excel::class);
        $loader->alias('Settings', Settings::class);
        $loader->alias('CanvasHelper', CanvasHelper::class);
        $loader->alias('CanvasConfig', ConfigHelper::class);
        $loader->alias('CanvasRoute', RouteHelper::class);
        $loader->alias('CanvasSetup', SetupHelper::class);

        // Register middleware...
        //$router->middleware('checkIfAdmin', CheckIfAdmin::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
