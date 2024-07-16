<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Upload;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateUploadStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);

        $response = $next($request);

        // Verifica se o job foi executado com sucesso
        if ($response->getStatusCode() == 200) {
            $uuid = $request->input('uuid');
            $upload = Upload::where('uuid', $uuid)->first();
            if ($upload) {
                $upload->status = 'completed';
                $upload->save();
            }
        }

        return $response;
    }
}
