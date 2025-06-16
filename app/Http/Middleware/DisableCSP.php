<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class DisableCSP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('DisableCSP middleware executado para: ' . $request->url());
        
        $response = $next($request);
        
        // Log headers antes da modificação
        $originalCSP = $response->headers->get('Content-Security-Policy');
        if ($originalCSP) {
            Log::info('CSP original encontrado: ' . $originalCSP);
        }
        
        // Remove qualquer header CSP
        $response->headers->remove('Content-Security-Policy');
        $response->headers->remove('X-Content-Security-Policy');
        $response->headers->remove('X-WebKit-CSP');
        
        // Para desenvolvimento, permitir todas as conexões
        if (config('app.env') === 'local') {
            $newCSP = "default-src 'self' 'unsafe-inline' 'unsafe-eval' data: ws: wss: http: https:; " .
                     "script-src 'self' 'unsafe-inline' 'unsafe-eval' http: https:; " .
                     "style-src 'self' 'unsafe-inline' http: https:; " .
                     "connect-src 'self' ws: wss: http: https:; " .
                     "img-src 'self' data: http: https:; " .
                     "font-src 'self' http: https:;";
            
            $response->headers->set('Content-Security-Policy', $newCSP);
            Log::info('Novo CSP aplicado: ' . $newCSP);
        }
        
        return $response;
    }
}
