<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BomProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Clean any output buffer that might contain BOM
        if (ob_get_level()) {
            ob_clean();
        }

        // Get the response
        $response = $next($request);

        // Skip processing for StreamedResponse (file downloads)
        if ($response instanceof StreamedResponse) {
            return $response;
        }

        // Only process HTML responses
        if ($response instanceof Response && str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            $content = $response->getContent();

            // Remove BOM from the beginning of content if present
            $bom = pack('H*', 'EFBBBF');
            $content = preg_replace("/^$bom/", '', $content);

            // Set clean content
            $response->setContent($content);

            // Ensure correct content type header
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }
}
