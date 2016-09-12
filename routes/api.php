<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/map', function () {
    $location = MapSerivce::geocoding('30 lauder drive,bundoora,melbourne,australia');

    $result =  json_decode(MapSerivce::nearby($location,'police',3000),true);
    $police_station_count = count($result['results']);

    $result =  json_decode(MapSerivce::nearby($location,'fire_station',3000),true);
    $fire_station_count = count($result['results']);

    $hospital =  json_decode(MapSerivce::nearby($location,'hospital',3000),true);

    $hospital_details = [];
    //hospital detail
            foreach($hospital['results'] as $place){
                $hospital_detail= json_decode( MapSerivce::detail( $place['place_id'] ),true);

                $hospital_detail['linear_distance']= MapSerivce::linearDistance($location,implode(',',$place['geometry']['location']));

                if ( $hospital_detail['linear_distance']<=2 ){
                   $walking_time=json_decode(  MapSerivce::distance('walking',$location,
                                                                        implode(',',$place['geometry']['location']) ),true);
                   // dd($walking_time);
                    if ( (array_key_exists('rows',$walking_time)) && ($walking_time['rows'][0]['elements'][0]['duration']['value']<=900)){
                        $hospital_detail['walkable']=$walking_time['rows'][0]['elements'][0]['duration']['value'];
                    }

                }

                if ( $hospital_detail['linear_distance']<=15 ){
                    $bicycling_time=json_decode(  MapSerivce::distance('bicycling',$location,
                        implode(',',$place['geometry']['location']) ),true);
                    // dd($walking_time);
                    if ( (array_key_exists('rows',$bicycling_time)) && ($bicycling_time['rows'][0]['elements'][0]['duration']['value']<=1800)){
                        $hospital_detail['bicycleable']=$bicycling_time['rows'][0]['elements'][0]['duration']['value'];
                    }

                }

                $hospital_detail['driving_time']= json_decode( MapSerivce::distance('driving',$location,
                    implode(',',$place['geometry']['location']) ),true)['rows'][0]['elements'][0]['duration']['value'];

                $hospital_detail['bus_time']= json_decode( MapSerivce::distance('transit',$location,
                    implode(',',$place['geometry']['location']) ),true)['rows'][0]['elements'][0]['duration']['value'];



                array_push($hospital_details,$hospital_detail);

            }


    $trams =  json_decode(MapSerivce::nearby($location,'light_rail_station',1500),true);
    $tram_details = [];

        foreach($trams['results'] as $tram) {
              $tram_detail= json_decode( MapSerivce::detail( $tram['place_id'] ),true);
            $walking_time = json_decode(  MapSerivce::distance('walking',$location,
                implode(',',$tram['geometry']['location']) ),true)['rows'][0]['elements'][0]['duration']['value'];

            $tram_detail['walking_time']= $walking_time;
            $tram_details[] = $tram_detail;


        }

    dd($tram_details);


    // dd($hospital_details);

    $hospital_count = count($hospital['results']);

    //dd($hospital);
    return compact(
        'fire_station_count',
        'police_station_count',
        'hospital_count'

    );



});

