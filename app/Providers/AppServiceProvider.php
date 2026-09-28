<?php

namespace App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\ViewErrorBag;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $loader = AliasLoader::getInstance();

        // 1. Register Models globally and in Services/Repositories namespaces
        $modelPath = app_path('Models');
        if (is_dir($modelPath)) {
            foreach (glob($modelPath.'/*.php') as $file) {
                $alias = basename($file, '.php');
                $modelClass = 'App\\Models\\'.$alias;

                if (class_exists($modelClass)) {
                    $loader->alias($alias, $modelClass);
                    $loader->alias('App\\Services\\'.$alias, $modelClass);
                    $loader->alias('App\\Repositories\\'.$alias, $modelClass);
                }
            }
        }

        // 2. Register Repositories globally and in Services namespace
        $repoPath = app_path('Repositories');
        if (is_dir($repoPath)) {
            foreach (glob($repoPath.'/*.php') as $file) {
                $alias = basename($file, '.php');
                $repoClass = 'App\\Repositories\\'.$alias;

                if (class_exists($repoClass)) {
                    $loader->alias($alias, $repoClass);
                    $loader->alias('App\\Services\\'.$alias, $repoClass);
                }
            }
        }

        // 3. Register Services globally
        $servicePath = app_path('Services');
        if (is_dir($servicePath)) {
            foreach (glob($servicePath.'/*.php') as $file) {
                $alias = basename($file, '.php');
                $serviceClass = 'App\\Services\\'.$alias;

                if (class_exists($serviceClass)) {
                    $loader->alias($alias, $serviceClass);
                }
            }
        }

        // Intercept Laravel view error bags and convert to array for custom MVC view compatibility
        view()->composer('*', function ($view) {
            $viewData = $view->getData();
            $laravelErrors = $viewData['errors'] ?? view()->shared('errors');

            if ($laravelErrors instanceof ViewErrorBag) {
                $errorsArray = [];
                foreach ($laravelErrors->getBags() as $bag) {
                    foreach ($bag->messages() as $key => $messages) {
                        $errorsArray[$key] = $messages[0] ?? '';
                    }
                }
                if ($laravelErrors->any() && empty($errorsArray['general'])) {
                    $errorsArray['general'] = $laravelErrors->first();
                }
                $view->with('errors', $errorsArray);
            } elseif (! isset($viewData['errors'])) {
                $view->with('errors', []);
            }

            // Wrap $data array in SafeArray for template key safety
            $data = $viewData['data'] ?? $viewData;
            if (is_array($data)) {
                $view->with('data', new \SafeArray($data));
            }

            // Fallbacks for auth templates
            if (! array_key_exists('unverified', $viewData)) {
                $view->with('unverified', false);
            }
            if (! array_key_exists('identifier', $viewData)) {
                $view->with('identifier', '');
            }
        });
    }
}
