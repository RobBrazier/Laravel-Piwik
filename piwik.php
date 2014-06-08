<?php

namespace Piwik;

class Piwik {

    protected $api_key = "";

    public static function __callStatic($name, $arguments) {
        $piwikLib = new PiwikLib();
        $piwik = new \ReflectionMethod('Piwik\PiwikLib', $name);
        return $piwik->invokeArgs($piwikLib, $arguments);
    }

    public function __construct($api_key = null) {
        $this->api_key = $api_key;
    }

    public function __call($name, $arguments) {
        $piwikLib = new PiwikLib();

        if($this->api_key != "") {
            $piwik = new \ReflectionMethod('Piwik\PiwikLib', '__construct');
            $piwik->invokeArgs($piwikLib, array($this->api_key));
        }
        $method = new \ReflectionMethod('Piwik\PiwikLib', $name);
        return $method->invokeArgs($piwikLib, $arguments);
    }

}