<?php

class sensor_api
{

    private $_data;


    public function __construct($data)
    {
        $this->setData($data);
    }
    // ================================================================================
    public function setData($data)
    {
        $this->_data = $data;
    }
    // ================================================================================
    public function geData()
    {
        return $this->_data;
    }
    // ================================================================================

    // ================================================================================
    public function getDevice()
    {
        return $this->_data['description'];
    }
    // ================================================================================
    public function getTemperature($i)
    {
        return round($this->_data['room'][$i]['sensor'][0]['parameter']['temperature']['value'], 1);
    }
    // ================================================================================
    public function getHumidity($i)
    {
        return round($this->_data['room'][$i]['sensor'][1]['parameter']['humidity']['value'], 0);
    }
    // ================================================================================
    public function getDebit($i)
    {
        $nominal = $this->_data['room'][$i]['parameter']['nominal']['value'];
        $flow_rate = $this->_data['room'][$i]['actuator'][0]['parameter']['flow_rate']['value'];
        return round(($flow_rate * 100) / $nominal, 0);
    }
    // ================================================================================
    public function getCO2($i)
    {
        return round($this->_data['room'][$i]['sensor'][2]['parameter']['concentration']['value'], 0);
    }
    // ================================================================================
    public function getCOV($i)
    {
        return round($this->_data['room'][$i]['sensor'][2]['parameter']['concentration']['value'], 0);
    }
    // ================================================================================
    public function getProfil($i)
    {
        return array_search($this->_data['room'][$i]['profile_name'], self::PROFIL);
    }
    // ================================================================================
    public function getNbPiece()
    {
        return count($this->_data['room']);
    }
    // ================================================================================
    private function getNbSensor($i)
    {
        return count($this->_data['room'][$i]['sensor']);
    }
    // ================================================================================
    public function getNamePiece($i)
    {
        return $this->_data['room'][$i]['name'];
    }
    // ================================================================================
    public function isCO2($i)
    {
        $n = $this->getNbSensor($i);
        if ($n < 4) {
            return false;
        }
        if ($this->_data['room'][$i]['sensor'][2]['type'] == 'indoor CO2') {
            return true;
        }
        return false;
    }
    // ================================================================================
    public function isCOV($i)
    {
        $n = $this->getNbSensor($i);
        if ($n < 4) {
            return false;
        }
        if ($this->_data['room'][$i]['sensor'][2]['type'] == 'indoor volatile organic compounds') {
            return true;
        }
        return false;
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
    public function disableBoost($i)
    {
        $this->put(self::URL_BOOST . $i, '{"enable": false}');
    }
}
