<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SkipNgrokWarning
{

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
         $response = $next($request);
        
        // Agregar el encabezado necesario para saltar la advertencia
        $response->headers->set('ngrok-skip-browser-warning', 'true');
        
        return $response;
    }
}
