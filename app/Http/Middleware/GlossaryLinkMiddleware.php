<?php

namespace App\Http\Middleware;

use App\Services\GlossaryService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GlossaryLinkMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof Response) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');

        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();

        if (empty($html)) {
            return $response;
        }

        $linked = app(GlossaryService::class)->linkHtml($html);
        $response->setContent($linked);

        return $response;
    }
}
