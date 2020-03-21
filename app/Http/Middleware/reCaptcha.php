<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;  

class reCaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!env('RECAPTCHA_ENABLED')) {
            return $next($request);
        }
        $response = (new Client)->post('https://www.google.com/recaptcha/api/siteverify', [
        'form_params' => [
            'secret'   => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->token,
            'remoteip' => $request->ip()
        ],
        ]);
        $response = json_decode((string)$response->getBody(), true);
        if ($response['success']) {
          return $next($request);
        }
        return response(['message'=>'Captcha is invalid'],Response::HTTP_BAD_REQUEST);
      
    }
}
