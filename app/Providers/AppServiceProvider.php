<?php

namespace App\Providers;

use App\Models\StockAlert;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // @rp(15000) -> Rp 15.000
        Blade::directive('rp', function ($expression) {
            return "<?php echo 'Rp ' . number_format($expression, 0, ',', '.'); ?>";
        });

        // data lonceng notifikasi stok menipis di bagian atas semua halaman
        View::composer('partials.topbar', function ($view) {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();

            $bellAlerts = StockAlert::visibleTo($user)->open()
                ->with(['branch', 'product'])->orderBy('quantity')->take(5)->get();

            $bellUnread = StockAlert::visibleTo($user)->open()->whereNull('read_at')->count();
            $bellTotal = StockAlert::visibleTo($user)->open()->count();

            $view->with(compact('bellAlerts', 'bellUnread', 'bellTotal'));
        });
    }
}
