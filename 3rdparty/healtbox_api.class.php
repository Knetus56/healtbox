<?php

class healtbox_api
{

    protected $_ip;
    protected $_url_data = '/v2/api/data/current';
    protected $_url_boost = '/v2/api/boost/';
    protected $_data;
    protected $_dataBoost;

    public function __construct($ip)
    {

        $this->_data = "";
        $this->_dataBoost = "";
        $this->setip($ip);
        $this->getData();
    }

    public function setip($ip)
    {
        $this->_ip = $ip;
    }

    public function getip()
    {
        return $this->_ip;
    }

    public function getData()
    {
        $session = curl_init();

        curl_setopt_array($session, [
            CURLOPT_URL => "http://" . $this->_ip . $this->_url_data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
        ]);

        $json = curl_exec($session);
        curl_close($session);
        $this->_data = json_decode($json, true);
    }


    public function getNbPiece()
    {
        return count($this->_data['room']);
    }

    public function getNamePiece($i)
    {
        return $this->_data['room'][$i]['name'];
    }  
    public function getTemperature($i)
    {
        return $this->_data['room'][$i]['name'];
    }
    public function isCO2($i)
    {
        return false;
    }
    public function isCOV($i)
    {
        return false;
    }
}
?>