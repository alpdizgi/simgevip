<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Admin paneli ve kritik işlemleri engelleme
        if ($request->is('admin*') || $request->is('livewire*') || $request->is('link-storage')) {
            return $next($request);
        }

        try {
            $setting = \App\Models\Setting::first();
            
            if ($setting && $setting->maintenance_mode) {
                return response()->view('errors.503', [
                    'message' => $setting->maintenance_message,
                    'settings' => $setting
                ], 503);
            }
        } catch (\Exception $e) {
            // Veritabanı henüz kurulu değilse devam et
        }

        return $next($request);
    }
}
