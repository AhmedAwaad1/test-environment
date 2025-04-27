<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
    public function terminate($request, $response)
    {
        $headers = [];
        foreach ($request->headers as $key => $header) {
            $headers[$key] = $header[0];
        }
        $logs = [
            'TIME'         => gmdate("F j, Y, g:i a"),
            'IP'           => $request->ip(),
            'URI'          => $request->getUri(),
            'METHOD'       => $request->getMethod(),
            'REQUEST_BODY' => json_encode($request->all()),
            'RESPONSE'     => $response->getContent(),
        ];
        $logs = array_merge($logs, $headers);
        Log::channel("apiDaily")->info(print_r($logs, true));
    }
}
