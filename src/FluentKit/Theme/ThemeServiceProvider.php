<?php
namespace FluentKit\Theme;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class AssetServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

    public function register()
    {

		//register theme view finder
        $this->app->bindShared('view.finder', function ($app) {
            $paths = $app['config']['view.paths'];
            return new FileViewFinder($app['files'], $paths);
        });
        $this->app->bindShared('fluentkit.theme', function ($app) {
            return new ThemeManager($app);
        });
        $this->app->bindShared('fluentkit.theme.finder', function ($app) {
            return new ThemeFinder($app);
        });

    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

    	//register facades
    	$loader = AliasLoader::getInstance();

		//fluent aliases
        $loader->alias('Theme', 'FluentKit\Facades\Theme');

        $app = $this->app;

        //register theme
        $app['fluentkit.theme']->setTheme('theme1');

        // The theme is only booted when the first view is being composed.
        // This would prevent multiple theme being booted in the same
        // request.
        $app['events']->listen('composing: *', function () use ($app) {
            $app['fluentkit.theme']->boot();
        });

    }

    public function provides(){
    	return array('fluentkit.theme', 'fluentkit.theme.finder');
    }

}