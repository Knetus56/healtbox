<?php

class healthbox_api
{

    private $_ip;
    private const PROFIL = ["eco", "health", "intense"];
    private const URL_DATA = '/v2/api/data/current';
    private const URL_BOOST = '/v2/api/boost/';
    private const URL_PROFIL = '/v2/api/data/current/room/';

    public function __construct($ip)
    {
        $this->setip($ip);
    }
    // ================================================================================
    public function setip($ip)
    {
        $this->_ip = $ip;
    }
    // ================================================================================
    public function getip()
    {
        return $this->_ip;
    }
    // ================================================================================
    public function getData()
    {
        $session = curl_init();

        curl_setopt_array($session, [
            CURLOPT_URL => "http://" . $this->_ip . self::URL_DATA,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        ]);

        $json = curl_exec($session);
        curl_close($session);
        return json_decode($json, true);
    }
    // ================================================================================
    public function getBoost($i)
    {
        $session = curl_init();

        curl_setopt_array($session, [
            CURLOPT_URL => "http://" . $this->_ip . self::URL_BOOST . $i,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        ]);

        $json = curl_exec($session);
        curl_close($session);
        return json_decode($json, true);
    }
    // ================================================================================
    public function put($url, $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "http://" . $this->_ip . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    // ================================================================================
    public function checkType($type)
    {
        if ($type == "indoor relative humidity") {
            return ['humidity', '%', 'humidity'];
        } elseif ($type == "indoor temperature") {
            return ['temperature', '°C', 'temperature'];
        } elseif ($type == "indoor air quality index") {
            return false; //['index', '','index'];
        } elseif ($type == "indoor CO2") {
            return ["CO2", 'ppm', 'concentration'];
        } elseif ($type == "indoor volatile organic compounds") {
            return ["COV", 'ppm', 'concentration'];
        } else {
            return false;
        }
    }
    // ================================================================================
    public function getDebit($json)
    {
        $nominal = $json['parameter']['nominal']['value'];
        $flow_rate = $json['actuator'][0]['parameter']['flow_rate']['value'];
        return round(($flow_rate * 100) / $nominal, 0);
    }
    // ================================================================================
    public function getProfil($json)
    {
        log::add('healthbox', 'info', print_r($json['parameter']['profile_name']));
        return array_search($json['parameter']['profile_name'], self::PROFIL);
    }
    // ================================================================================
    public function getSensor($json, $param)
    {
        return round($json['parameter'][$param]['value']);
    }
    // ================================================================================
    public function changeProfil($i, $profil)
    {
        $this->put(self::URL_PROFIL . $i . '/profile_name', '"' . self::PROFIL[$profil] . '"');
    }
    // ================================================================================
    public function enableBoost($i, $j)
    {
        $this->put(self::URL_BOOST . $i, $j);
    }
    // ================================================================================
    public function disableBoost($i)
    {
        $this->put(self::URL_BOOST . $i, '{"enable": false}');
    }
}