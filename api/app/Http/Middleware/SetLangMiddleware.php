<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


/**
 * Class SetLangMiddleware
 * @package App\Http\Middleware
 */
class SetLangMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale($request->route('lang'));
        return $next($request);
    }
}
