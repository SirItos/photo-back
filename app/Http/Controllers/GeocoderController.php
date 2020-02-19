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
            // https://geocode-maps.yandex.ru/
            'base_uri' => 'https://maps.googleapis.com/',
        ]);
    }
    
    protected function Geosearch(Request $request)
    {
        $query = [
             'key'=>env('GOOGLE_KEY'),
             'address'=>$request->address,
             'latlng'=>$request->latlng,
             'region'=>'ru',
             'language'=>'ru'
        ];
        
        $response = $this->http->request('GET','maps/api/geocode/json',[
            'query'=>$query
        ]);
        return response($response->getBody(),$response->getStatusCode());
    }

   protected function ipLocation(Request $request)
   {
       
       $ipLocal = new Client([
           'base_uri'=>'http://api.ipstack.com/'
       ]);
       $result = $ipLocal->get($request->ip(),['query'=>[
                'access_key'=>env('IP_LOCATE_KEY'),
                'format'=>1
                ]
            ]
       );
       return $result->getBody();

   }

    
}
