<?php

namespace App\Http\Middleware\FrontApi;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateUploadFile
{
    /**
     * Validate upload file
     *
     * @param  Request  $request
     * @param  Closure $next
     * 
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $files = $request->file('file');
        if (is_array($files)) {
            foreach ($files as $oneFile) {
                if (!$oneFile->isValid()) {
                    return response()->json(['success' => false, 'message' => $oneFile->getErrorMessage()], 400);
                }
            }
        } else {
            if (!$files || !$files->isValid()) {
                return response()->json(['success' => false, 'message' => $files->getErrorMessage()], 400);
            }
        }
        return $next($request);
    }
}
