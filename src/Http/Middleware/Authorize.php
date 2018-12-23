<?php

namespace WesLal\NovaSettingsTool\Http\Middleware;

use Illuminate\Http\JsonResponse;
use WesLal\NovaSettingsTool\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Closure;

/**
 * Class Authorize
 * @package WesLal\NovaSettingsTool\Http\Middleware
 */
final class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response|JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return resolve(Settings::class)->authorize($request) ? $next($request) : abort(403);
    }
}
