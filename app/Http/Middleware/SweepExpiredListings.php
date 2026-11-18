<?php

namespace App\Http\Middleware;

use App\Services\ListingExpiry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SweepExpiredListings
{
    public function handle(Request $request, Closure $next): Response
    {
        ListingExpiry::sweep();

        return $next($request);
    }
}
