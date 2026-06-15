<?php

namespace App\Providers;

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

    public function boot(): void
    {
        if (app()->environment('local')) {
            \Illuminate\Support\Facades\Http::globalOptions([
                'verify' => false,
            ]);
        }

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $userId = \Illuminate\Support\Facades\Auth::id();
                
                $notifications = \App\Models\Notification::where('user_id', $userId)
                    ->where('status', 'unread')
                    ->orderByDesc('id')
                    ->take(3)
                    ->get();

                $notifyCount = \App\Models\Notification::where('user_id', $userId)
                    ->where('status', 'unread')
                    ->count();

                $notificationsEnabled = \Illuminate\Support\Facades\Auth::user()->notification;

                $view->with([
                    'notifications' => $notifications,
                    'notifyCount' => $notifyCount,
                    'notificationsEnabled' => $notificationsEnabled,
                ]);
            }
        });
    }
}
