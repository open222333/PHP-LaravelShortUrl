<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin\Models\ShorturlSetting;
use Exception;

class ApiController extends Controller
{
    public function replacedomain_for_yourls(Request $request){
        $process = false;

        $ori = $request->old_domain;
        $new = $request->new_domain;

        try{
            $settings = ShorturlSetting :: Where("android_url", "LIKE", "%{$ori}%")
            ->orWhere("ios_url", "LIKE", "%{$ori}%")
            ->orWhere("other_url", "LIKE", "%{$ori}%")
            ->get();

            foreach($settings AS $data){
                $data->android_url = str_replace($ori, $new, $data->android_url);
                $data->ios_url = str_replace($ori, $new, $data->ios_url);
                $data->other_url = str_replace($ori, $new, $data->other_url);
                $data->save();
            }
            $process = true;
        } catch(Exception $e){
            
        }
        
        return json_encode(array('success' => $process));
    }
}
