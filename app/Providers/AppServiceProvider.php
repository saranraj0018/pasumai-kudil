<?php

namespace App\Providers;

use App\Models\Ability;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        if (!Schema::hasTable('abilities')) {
            return;
        }
        Gate::before(function ($user, $ability) {
            if ($user && $user->role_id == 1) {
                return true;
            }
           return null;
        });

        Ability::all()->each(function ($ab) {
            Gate::define($ab->ability, function ($user) use ($ab) {
                return $user->hasAbility($ab->ability);
            });
        });
    }
}
