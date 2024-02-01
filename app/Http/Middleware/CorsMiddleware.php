<?php 

namespace App\Http\Middleware;

class CorsMiddleware {
  /**
  * Handle an incoming request.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \Closure  $next
  * @return mixed
  */
  public function handle($request, \Closure $next)
  {
    //Intercepts OPTIONS requests
    if($request->isMethod('OPTIONS')) {
      $response = response('', 200);
    } else {
      // Pass the request to the next middleware
      $response = $next($request);
    }

    $response->headers->set('Access-Control-Allow-Origin' , '*');
    $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
    $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');

    // Adds headers to the response
    // $response->header('Access-Control-Allow-Origin', '*');
    // $response->header('Access-Control-Allow-Methods', 'HEAD, GET, POST, PUT, PATCH, DELETE');
    // $response->header('Access-Control-Allow-Headers', $request->header('Access-Control-Request-Headers'));

    // Sends it
    return $response;
	}
}