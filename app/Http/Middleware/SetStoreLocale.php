<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetStoreLocale
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->is('admin*') && ! $request->is('livewire*') && ! $request->is('filament*')) {
            app()->setLocale('tr');
        }

        return $next($request);
    }
}
