<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;  

class GeocoderController extends Controller
{

     /**
     * GeocoderController constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->http = new $client([
            'base_uri' => 'https://geocode-maps.yandex.ru/',
        ]);
    }
    
    protected function Geosearch(Request $request)
    {
        
        $response = $this->http->request('GET','1.x/',[
            'query'=>[
                'apikey'=>env('API_KEY'),
                'geocode'=>$request->val,
                'll'=>$request->ll['lng'] . ',' .$request->ll['lat'],
                'spn'=>'3.552069,2.400552',
                'lang'=>'ru_RU',
                'format'=>'json'
            ]
        ]);
        return response($response->getBody(),$response->getStatusCode());
    }
}
