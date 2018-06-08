# fibonacci-web
Vendor fibonacci


To use the middlewares you will have to register them in app/Http/Kernel.php under the $routeMiddleware property:

protected $routeMiddleware = [
	...
	'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
	'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
];


Add the following code to the render method within app/Exceptions/Handler.php

if ($exception instanceof Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json(['error'=>'token_expired'], $exception->getStatusCode());
        } else if ($exception instanceof Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json(['token_invalid'], $exception->getStatusCode());
        } else if($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException ){
            return response()->json(['error'=>'token_not_found'], $exception->getStatusCode());
        }
}
