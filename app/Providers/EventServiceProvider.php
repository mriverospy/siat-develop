<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\UserEventLoginListener::class,
        ],
        \Illuminate\Auth\Events\Logout::class => [
            \App\Listeners\UserEventLogoutListener::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {

        // $events->listen('auth.attempt', 'App\Listeners\UserEventListener@onAttemptLogin');
        // $events->listen(\Illuminate\Auth\Events\Login::class, 'App\Listeners\UserEventListener@onUserLogin');
        // $events->listen(\Illuminate\Auth\Events\Logout::class, 'App\Listeners\UserEventListener@onUserLogout');

        // Fired on each authentication attempt...
        // $events->listen('auth.attempt', function ($credentials, $remember, $login) {
            //
        // });

        // Fired on successful logins...
        // $events->listen(\Illuminate\Auth\Events\Login::class, function ($user, $remember) {
            //
        // });

        // Fired on logouts...
        // $events->listen(\Illuminate\Auth\Events\Logout::class, function ($user) {
            //
        // });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
