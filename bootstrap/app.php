<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades(true, [
    // Zizaco\Entrust\EntrustFacade::class => 'Entrust',
    Tymon\JWTAuth\Facades\JWTAuth::class => 'JWTAuth',
    Tymon\JWTAuth\Facades\JWTFactory::class => 'JWTFactory',
    Barryvdh\DomPDF\Facade::class => 'PDF',
    Pusher\Pusher::class =>'Pusher'
]);

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Filesystem\Factory::class,
    function ($app) {
        return new Illuminate\Filesystem\FilesystemManager($app);
    }
);


/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    App\Http\Middleware\CorsMiddleware::class,
    'auth' => App\Http\Middleware\Authenticate::class,
    'jwt.auth' => \App\Http\Middleware\VerifyJWTToken::class
]);

$app->routeMiddleware([
    // 'auth' => App\Http\Middleware\Authenticate::class,
    // 'role' => \Zizaco\Entrust\Middleware\EntrustRole::class,
    // 'permission' => \Zizaco\Entrust\Middleware\EntrustPermission::class,
    // 'ability' => \Zizaco\Entrust\Middleware\EntrustAbility::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(Zizaco\Entrust\EntrustServiceProvider::class);
// $app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(App\Providers\JwtLumenServiceProvider::class);
$app->register(\Barryvdh\DomPDF\ServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(App\Providers\BroadcastServiceProvider::class);



/*==============================
Mongo Service Provider for Lumen
==============================*/
$app->register(Moloquent\MongodbServiceProvider::class);
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__.'/../routes/web.php';
});

/*
|--------------------------------------------------------------------------
| Load Configs
|--------------------------------------------------------------------------
|
| We'll add all the custom config files here.
|
*/
// $app->configure('mail');
$app->configure('dompdf');

return $app;
