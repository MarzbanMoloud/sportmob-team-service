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
        $lang = $request->route('lang');

        if(!in_array($lang , $this->allowedLanguages())){
            throw new \Exception('Lang not support.');
        }
        app()->setLocale($lang);
        return $next($request);
    }

    /**
     * @return array
     */
    private function allowedLanguages()
    {
        return explode(",", env("ALLOWED_LANGUAGES"));
    }
}
