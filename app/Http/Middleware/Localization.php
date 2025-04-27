<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $lang = ['ar', 'en'];

        if (request()->hasHeader('accept-language')){
            $local = (in_array(request()->header('accept-language'), $lang)) ?
                request()->header('accept_language') : 'en';
        }else{
            $local = 'en';
        }
        app()->setLocale($local);
        return $next($request);
    }

}
