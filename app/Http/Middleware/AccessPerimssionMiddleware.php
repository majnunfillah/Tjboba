<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AccessPerimssionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $codeAccess, $codeMenu)
    {
        // dd(!$request->user()->canAccess($codeAccess, $codeMenu));
        if(!$request->user()->canAccess($codeAccess, $codeMenu)){
            return abort(403, 'Anda tidak memiliki akses ke menu ini');
        }

        return $next($request);
    }
}
