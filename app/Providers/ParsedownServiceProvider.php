<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ParsedownServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Parsedown into the container
        $this->app->singleton('parsedown', function () {
            require_once base_path('vendor/parsedown/parsedown/Parsedown.php');
            return new \Parsedown();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Blade directive
        \Blade::directive('markdown', function ($expression) {
            return "<?php echo (new \\App\\Helpers\\MarkdownHelper())->parse({$expression}); ?>";
        });
    }
}
