<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

// ================================================================================
require_once __DIR__ . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../3rdparty/healthbox_api.class.php';
require_once __DIR__ . '/../../3rdparty/sensor_api.class.php';

class healthbox extends eqLogic
{
    // ================================================================================
    public static function cron()
    {
        foreach (healthbox::byType('healthbox') as $eqLogic) {
            try {
                $eqLogic->updatehealthbox();
            } catch (Exception $e) {
                log::add('healthbox', 'info', $e->getMessage());
            }
        }
    }
    // ================================================================================
    public function updatehealthbox()
    {
        $api = new healthbox_api($this->getConfiguration('iphealthbox'));
        $sensorapi = new sensor_api($api->getData());


        foreach ($this->getCmd() as $cmmd) {
            if ($cmmd->getType() == 'info') {
                log::add('healthbox', 'debug', print_r($cmmd->getLogicalId(), true));
            }
           
        }
        // $ap = $api->getNbPiece();
        // //   log::add('healthbox', 'info', $ap);

        // $this->checkAndUpdateCmd('0:' . 'device_type', $api->getDevice());

        // for ($i = 1; $i <= $ap; $i++) {
        //     $NamePiece = str_replace(" ", "_", $api->getNamePiece($i));
        //     $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':temperature', $api->getTemperature($i));
        //     $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':humidity', $api->getHumidity($i));
        //     $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':profil', $api->getProfil($i));
        //     $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':debit', $api->getDebit($i));
        //     $CO2 = $api->isCO2($i);
        //     if ($CO2) {
        //         $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':CO2', $api->getCO2($i));
        //     }
        //     $COV = $api->isCOV($i);
        //     if ($COV) {
        //         $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':COV', $api->getCOV($i));
        //     }
        //     $boost = $api->getBoost($i);
        //     $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':boost-enable', $boost['enable']);
        //     $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':boost-level', $boost['level']);
        //     $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':boost-remaining', $boost['remaining']);
        //     $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':boost-timeout', $boost['timeout']);
        // }
        // $this->refreshWidget();
    }
    // ================================================================================
    public function preInsert()
    {
    }

    // ================================================================================
    public function postInsert()
    {
    }

    // ================================================================================
    public function preUpdate()
    {
        if ($this->getConfiguration('iphealthbox') == '') {
            throw new Exception(__('Veuillez entrer une IP', __FILE__));
        }
    }

    // ================================================================================
    private function checkType($type)
    {
        if ($type == "indoor relative humidity") {
            return ['humidity', '%'];
        } elseif ($type == "indoor temperature") {
            return ['temperature', '°C'];
        } elseif ($type == "indoor air quality index") {
            return ['index', ''];
        } elseif ($type == "indoor CO2") {
            return ["CO2", 'ppm'];
        } elseif ($type == "indoor volatile organic compounds") {
            return ["COV", 'ppm'];
        } else {
            return false;
        }
    }
    // ================================================================================
    public function setLogical($name_eq, $name, $Type, $Unit, $SubType)
    {
        $NamePiece = str_replace(" ", "_", $name_eq);

        $logic = $this->getCmd(null, $NamePiece);
        if (!is_object($logic)) {
            $logic = new healthboxCmd();
        }
        $logic->setName(__($name, __FILE__));
        $logic->setLogicalId($NamePiece);
        $logic->setEqLogic_id($this->getId());
        $logic->setType($Type);
        $logic->setUnite($Unit);
        $logic->setSubType($SubType);
        $logic->save();
    }
    // ================================================================================
    public function postSave()
    {
        $api = new healthbox_api($this->getConfiguration('iphealthbox'));
        $data = $api->getData();

        $this->setLogical('device_type', 'device_type', 'info', '', 'string');

        foreach ($data['room'] as $i => $room) {

            $room_name = $room['name'];
            $this->setLogical($i . ':debit', $room_name . ':debit', 'info', '%', 'numeric');
            $this->setLogical($i . ':profil', $room_name . ':profil', 'info', '', 'numeric');

            foreach ($room['sensor'] as $ii => $sensor) {

                $type = $this->checkType($sensor['type']);

                if (is_array($type)) {
                    $name_eq = $i . ':' . $room_name . ':' . $ii;
                    $name = $room_name . ':' . $type[0];
                    $this->setLogical($name_eq, $name, 'info', $type[1], 'numeric');
                }

                $this->setLogical($i . ':boost-enable', $room_name . ':boost-enable', 'info', '', 'binary');
                $this->setLogical($i . ':boost-level', $room_name . ':boost-level', 'info', '', 'numeric');
                $this->setLogical($i . ':boost-remaining', $room_name . ':boost-remaining', 'info', '', 'numeric');
                $this->setLogical($i . ':boost-timeout', $room_name . ':boost-timeout', 'info', '', 'numeric');

                $this->setLogical($i . ':changeProfil', $room_name . ':changeProfil', 'action', '', 'other');
                $this->setLogical($i . ':boostON', $room_name . ':boostON', 'action', '', 'other');
                $this->setLogical($i . ':boostOFF', $room_name . ':boostOFF', 'action', '', 'other');
            }

        }

        if ($this->getIsEnable() == 1) {
            $this->updatehealthbox();
        }
    }
}
// ================================================================================
class healthboxCmd extends cmd
{
    // ================================================================================
    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    // ===============================================================================
    public function dontRemoveCmd() {
        return true;
      }
    // ================================================================================
    public function execute($_options = array())
    {
        if ($this->getType() == 'info') {
            return;
        }

        $eqLogic = $this->getEqlogic();
        $request = jeedom::evaluateExpression($this->getConfiguration("request", ""));

        $p = explode(":", $this->getLogicalId());

        if ($p[2] == 'changeProfil') {
            if (is_numeric($request)) {
                $api = new healthbox_api($eqLogic->getConfiguration('iphealthbox'));
                $api->changeProfil($p[0], intval($request));
            } else {
                log::add('healthbox', 'error', 'Commande changeProfil : Donnée non numérique');
                return false;
            }
        } elseif ($p[2] == 'boostON') {
            if ($this->isJson($request)) {
                $api = new healthbox_api($eqLogic->getConfiguration('iphealthbox'));
                $api->enableBoost($p[0], $request);
            } else {
                log::add('healthbox', 'error', 'Commande boostON : JSON invalide');
                return false;
            }
        } elseif ($p[2] == 'boostOFF') {
            $api = new healthbox_api($eqLogic->getConfiguration('iphealthbox'));
            $api->disableBoost($p[0]);
        }

        if ($eqLogic->getIsEnable() == 1) {
            $eqLogic->updatehealthbox();
        }
    }
}