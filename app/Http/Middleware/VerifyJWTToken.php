<?php
namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\Authenticate as JwtAuthenticate;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Log;

class VerifyJWTToken
{

    private $no_login_routes;

    public function __construct(){
        $this->no_login_routes = [
            "POST-user",
            "POST-login",
            "POST-company",
            "GET-company-types",
            "GET-verify-email",
            "POST-request-reset-password",
            "POST-reset-password",
            "POST-subscription",
            "POST-request-callback",
            "POST-user-query",
            "GET-autocomplete-area",
            'GET-client-types',
            'POST-client',
			'POST-complete-registration',
            "GET-search-loc",
            "GET-product-unavailable-dates-no-login",
            "POST-rfp-campaign-without-login",
            "GET-filter-users-excel-download",
            "GET-reset-password-link",
            "GET-filter-users-download1"
        ];
    }

    /** 
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            // get current method and route requested
            $path = substr($request->path(), 0, 3) == "api" ? explode('/', $request->path())[1] : explode('/', $request->path())[0];
            $route = $request->method() . "-" . $path;
            if(in_array($route, $this->no_login_routes)){
                // if user is requesting a route that doesn't need him to be logged in, give access.
                return $next($request);
            }
            else{
                // Authenticates user using tymon-jwt_auth Authenticate middleware
                // if user is authenticated, instead of forwarding the request to next middleware,
                // it returns the user found.
                // Log::info(print_r($user, true));die();
                $user = app(JwtAuthenticate::class)->handle($request, function($request){
                    $user = JWTAuth::toUser();
                    return $user;
                });
                // check if user has the permission to access the route he's requesting
                $user_roles = $user->roles;
                $user_permissions = [];
                foreach($user_roles as $user_role){
                    $user_permissions = array_unique(array_merge($user_permissions, $user_role->permissions()->pluck('name')->toArray()));
                }
                if(in_array($route, $user_permissions)){
                    return $next($request);
                }
                else{
                    return response("Forbidden", "403");
                }
            }
        }
        catch (JWTException $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['token_expired'], $e->getStatusCode());
            }
            else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['token_invalid'], $e->getStatusCode());
            }
            else{
                Log::info(print_r($e->getMessage(), true));
                return response()->json(['error'=>'Token is required']);
            }
        }
    }
}
