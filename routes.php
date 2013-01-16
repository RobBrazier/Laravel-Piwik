<?php
$bundles = include(path('app').'bundles.php');
$piwik_handle = null;
if(isset($bundles['piwik']['handles'])){
    $piwik_handle = $bundles['piwik']['handles'];
}
if($piwik_handle){
    Route::get('(:bundle)', function()
    {
        return View::make('piwik::install');
    });
    Route::post('(:bundle)', function()
    {
        $input = Input::all();
        $rules = array(
            'piwik_url' => 'required|url',
            'username'  => 'required_with:password',
            'password'  => 'required_with:username',
            'format'    => 'required|in:php,xml,json,html,rss,original',
            'period'    => 'required|in:today,yesterday,previous7,previous30,last7,last30,currentweek,currentmonth,currentyear',
            'site_id'   => 'required|integer',
        );
        $messages = array(
            'piwik_url_required' => 'You must enter a Piwik URL',
            'piwik_url_url' => 'Piwik URL must be a valid URL',
            'username_required_with'  => 'You must enter a Username if you have entered a Password',
            'password_required_with'  => 'You must enter a Password if you have entered a Username',
            'format_required'   => 'Format is Required',
            'format_in'    => 'Incorrect Format',
            'period_required'   => 'Period is Required',
            'period_in'    => 'Incorrect Period',
            'site_id_required'   => 'The Site ID is Required',
            'site_id_integer'   => 'The Site ID must be an Integer'
        );
        $validation = Validator::make($input, $rules, $messages);
         if ($validation->fails()) {
            return Redirect::back()->with_errors($validation)->with_input('except', array('password'));
        } else {
            $contents = "<?php

    return array(

        /*
         *  Server URL
         */

        'piwik_url'     => '".Input::get('piwik_url')."',

        /*
         *  Piwik Username and Password
         */

        'username'      => '".Input::get('username')."',
        'password'      => '".Input::get('password')."',

        /*
         *  Optional API Key (will be used instead of Username and Password) 
         *  The bundle works much faster with the API Key, rather than username and password.
         */

        'api_key'       =>  '".Input::get('api_key')."',

        /*
         *  Format for API calls to be returned in
         *  
         *  Can be [php, xml, json, html, rss, original]
         *  
         *  The default is 'json'
         */

        'format'        => '".Input::get('format')."',

        /*
         *  Period/Date range for results
         *  
         *  Can be [today, yesterday, previous7, previous30, last7, last30, currentweek, currentmonth, currentyear]
         *
         *  The default is 'yesterday'
         */

        'period'        => '".Input::get('period')."',

        /*
         *  The Site ID you want to use
         */

        'site_id'       => '".Input::get('site_id')."',
    );";
            File::put(Bundle::path('piwik').'config/config.php', $contents);
            return View::make('piwik::installed');
        }
    });
}