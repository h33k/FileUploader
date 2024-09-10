<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GuestSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('guest_id')) {
            // генерация уникального идентификатора для гостя
            $guestId = Str::uuid();
            $request->session()->put('guest_id', $guestId);
        }

        // проверка наличия cookie и установка его при отсутствии
        if (!cookie('guest_id')) {
            $cookie = cookie('guest_id', $request->session()->get('guest_id'), 60 * 24 * 30); // cookie на 30 дней
            return $next($request)->cookie($cookie);
        }

        return $next($request);
    }
}
