<?php
/**
 * User: Luye
 * Date: 16/9/10
 * Time: 上午1:18
 */

namespace App\Service;
const CACHE_MINUTES = 1000;

use Illuminate\Support\Facades\Cache;

class MapService
{

    public function linearDistance($origin,$desitination) {
        $lat1 = floatval(explode(',',$origin)[0]);
        $lon1 = floatval(explode(',',$origin)[1]);

        $lat2 = floatval(explode(',',$desitination)[0]);
        $lon2 = floatval(explode(',',$desitination)[1]);

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return $miles * 1.609344;
    }


    public function apiKey()
    {
        return config('map.key');
    }

    public function geocoding($address){
        $output =  json_decode($this->request('https://maps.googleapis.com/maps/api/geocode/json',['address'=>$address]),true);


        if ($output['status']!=='OK') {
            throw new \ErrorException( 'Geocoding with '.$address.' failed.' );
        }

        return implode(',',$output['results'][0]['geometry']['location']);

    }

    public function nearby($location, $types, $radius=30000){


        $options = [
            'location'=>$location,
            'types'=>$types,
            'radius'=>$radius,
            'rankby'=>'distance'
        ];

        return $this->request('https://maps.googleapis.com/maps/api/place/radarsearch/json',$options);
    }

    public function distance($mode='driving', $origin, $destination){

        $options = [
            'mode'=>$mode,
            'origins'=>$origin,
            'destinations'=>$destination
        ];


        return $this->request('https://maps.googleapis.com/maps/api/distancematrix/json',$options);

    }

    public function detail($placeid){
        $options = [
            'placeid'=>$placeid
        ];
        return $this->request('https://maps.googleapis.com/maps/api/place/details/json',$options);

    }

    protected function request( $apiUrl, $query=[] ){

       return Cache::remember(md5('1'.$apiUrl.http_build_query($query)),
               CACHE_MINUTES,
               function() use ($apiUrl,$query)
               {
                    $query['key'] = $this->apiKey();
                    $ch = curl_init( $apiUrl.'?'.http_build_query($query) );

                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    $output = curl_exec($ch);


                    if( $output === false ){
                        throw new \ErrorException( curl_error($ch) );
                    }

                   if ($status=json_decode($output,true)['status']!='OK'){
                       throw new \ErrorException($status );

                   }

                    curl_close($ch);
                    return $output;
                });


    }


}