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
    private function _getData($url)
    {
        $session = curl_init();

        curl_setopt_array($session, [
            CURLOPT_URL => "http://" . $this->_ip . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        ]);

        $json = curl_exec($session);

        if (curl_errno($session))
            $error_msg = curl_error($session);

        curl_close($session);

        if (isset($error_msg)) {
            log::add('healthbox', 'error', 'CURL : ' . $error_msg);
            return false;
        }

        return json_decode($json, true);
    }
    // ================================================================================
    private function _put($url, $data)
    {
        $session = curl_init();

        curl_setopt_array($session, [
            CURLOPT_URL => "http://" . $this->_ip . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        ]);

        $json = curl_exec($session);

        if (curl_errno($session))
            $error_msg = curl_error($session);

        curl_close($session);

        if (isset($error_msg)) {
            log::add('healthbox', 'error', 'CURL : ' . $error_msg);
            return false;
        }

        return true;
    }
    // ================================================================================
    public function getData()
    {
        return $this->_getData(self::URL_DATA);
    }
    // ================================================================================
    public function getBoost($i)
    {
        return $this->_getData(self::URL_BOOST . $i);
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
        return array_search($json['profile_name'], self::PROFIL);
    }
    // ================================================================================
    public function getSensor($json, $param)
    {
        return round($json['parameter'][$param]['value']);
    }
    // ================================================================================
    public function changeProfil($i, $profil)
    {
        $this->_put(self::URL_PROFIL . $i . '/profile_name', '"' . self::PROFIL[$profil] . '"');
    }
    // ================================================================================
    public function enableBoost($i, $j)
    {
        $this->_put(self::URL_BOOST . $i, $j);
    }
    // ================================================================================
    public function disableBoost($i)
    {
        $this->_put(self::URL_BOOST . $i, '{"enable": false}');
    }
}