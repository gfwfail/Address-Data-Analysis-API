<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use MapSerivce;

class MapController extends Controller
{

    function getPoliceAndFireStationCount($address){
        $location = MapSerivce::geocoding($address);

        $result =  json_decode(MapSerivce::nearby($location,'police',30000),true);
        $police_station_count = count($result['results']);

        $result =  json_decode(MapSerivce::nearby($location,'fire_station',30000),true);
        $fire_station_count = count($result['results']);


        return compact(
            'fire_station_count',
            'police_station_count'
        );


    }

    function getHospitals($address){
        $location = MapSerivce::geocoding($address);

        $hospital =  json_decode(MapSerivce::nearby($location,'hospital',10000),true);

        $hospital_details = [];
        //hospital detail
        foreach($hospital['results'] as $place){

            $api_data= json_decode( MapSerivce::detail( $place['place_id'] ),true);
            $hospital_detail=[];
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

            $bus_time =  json_decode( MapSerivce::distance('transit',$location,
                implode(',',$place['geometry']['location']) ),true);
            if  ($bus_time['rows'][0]['elements'][0]['status'] == "OK") {

                $hospital_detail['bus_time']=$bus_time['rows'][0]['elements'][0]['duration']['value'];



            }
            $hospital_detail['name'] = $api_data['result']['name'];
            $hospital_detail['location'] =    implode(',',$place['geometry']['location']);

                try {
                    $hospital_detail['formatted_address'] = $api_data['result']['formatted_address'];
                    $hospital_detail['formatted_phone_number'] = $api_data['result']['formatted_phone_number'];
                } catch (\Exception $e){

                }

            $hospital_details[]=$hospital_detail;

        }


        return compact(
            'hospital_details'
        );


    }

    function getTransits($address){
        set_time_limit(10000000);

        $location = MapSerivce::geocoding($address);
        $transits =  json_decode(MapSerivce::nearby($location,'train_station|subway_station|light_rail_station',3000),true);

        $transit_details = [];

        foreach($transits['results'] as $transit) {
            $api_data= json_decode( MapSerivce::detail( $transit['place_id'] ),true);
           // dd($api_data);
            $transit_detail = [];
            $transit_detail['api'] = $api_data;
            $transit_detail['linear_distance']= MapSerivce::linearDistance($location,implode(',',$transit['geometry']['location']));
            $transit_detail['types']=implode('|',$api_data['result']['types']);

            if ($transit_detail['linear_distance']<=2){
                $walking_time = json_decode(  MapSerivce::distance('walking',$location,
                    implode(',',$transit['geometry']['location']) ),true)['rows'][0]['elements'][0]['duration']['value'];

                $transit_detail['walkable']= $walking_time;
            }

            if(!in_array('light_rail_station',$api_data['result']['types']) || in_array('bus_station',$api_data['result']['types'])){
                $driving_time = json_decode(  MapSerivce::distance('driving',$location,
                    implode(',',$transit['geometry']['location']) ),true)['rows'][0]['elements'][0]['duration']['value'];

                $transit_detail['driving_time']= $driving_time;
            }


            $transit_detail['name']=$api_data['result']['name'];
            $transit_details[] = $transit_detail;


        }

        return compact(
            'transit_details'
        );

    }

    function all(){










        $hospital_count = count($hospital['results']);
        dd($hospital_details);

        //dd($hospital);
        return compact(
            'fire_station_count',
            'police_station_count'
        );

    }
}
