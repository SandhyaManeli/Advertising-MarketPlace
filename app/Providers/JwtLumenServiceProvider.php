<?php

/*
 * This file is a fork of jwt-auth.
 *
 * changes the necessary code to access the jwt-auth middleware as a global
 * one instead of a route middleware.
 */

namespace App\Providers;

use Tymon\JWTAuth\Http\Parser\AuthHeaders;
use Tymon\JWTAuth\Http\Parser\InputSource;
use Tymon\JWTAuth\Http\Parser\QueryString;
use Tymon\JWTAuth\Http\Parser\LumenRouteParams;

class JwtLumenServiceProvider extends JwtAbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->app->configure('jwt');

        // $path = realpath(__DIR__.'/../../config/config.php');
        // $this->mergeConfigFrom($path, 'jwt');

        // $this->app->middleware($this->middlewareAliases);

        $this->extendAuthGuard();

        $this->app['tymon.jwt.parser']->setChain([
            new AuthHeaders,
            new QueryString,
            new InputSource,
            new LumenRouteParams,
        ]);
    }
}
