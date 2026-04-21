<?php
class Kernel extends HttpKernel
{
protected $routeMiddleware = [
    // ...
    'empresa' => \App\Http\Middleware\RestrictDomain::class,
];
}
