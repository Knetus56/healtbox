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

class healthbox extends eqLogic
{
    // ================================================================================
    public static function cron()
    {
        foreach (healthbox::byType('healthbox') as $eqLogic) {
            $autorefresh = $eqLogic->getConfiguration('autorefresh');
            if ($autorefresh != '') {
                try {
                    $c = new Cron\CronExpression(checkAndFixCron($autorefresh), new Cron\FieldFactory);
                    if ($c->isDue()) {
                        $eqLogic->updatehealthbox();
                    }
                } catch (Exception $exc) {
                    log::add('healthbox', 'error', __('Expression cron non valide pour', __FILE__) . ' ' . $eqLogic->getHumanName() . ' : ' . $autorefresh);
                }
            }
        }
    }

    // ================================================================================
    public function updatehealthbox()
    {
        $api = new healthbox_api($this->getConfiguration('iphealthbox'));
        $data = $api->getData();
        if ($data) {

            $this->checkAndUpdateCmd('0:device_type', $data['description']);

            foreach ($data['room'] as $i => $room) {
                $this->checkAndUpdateCmd($i . ':profil', $api->getProfil($room));
                $this->checkAndUpdateCmd($i . ':debit', $api->getDebit($room));

                foreach ($room['sensor'] as $sensor) {
                    switch ($sensor['type']) {
                        case "indoor relative humidity":
                            $this->checkAndUpdateCmd($i . ':' . 'humidity', $api->getSensor($sensor, 'humidity'));
                            break;
                        case "indoor temperature":
                            $this->checkAndUpdateCmd($i . ':' . 'temperature', $api->getSensor($sensor, 'temperature'));
                            break;
                        case "indoor CO2":
                            $this->checkAndUpdateCmd($i . ':' . 'CO2', $api->getSensor($sensor, 'concentration'));
                            break;
                        case "indoor volatile organic compounds":
                            $this->checkAndUpdateCmd($i . ':' . 'COV', $api->getSensor($sensor, 'concentration'));
                            break;
                    }
                }

                $boost = $api->getBoost($i);
                if ($boost) {
                    $this->checkAndUpdateCmd($i . ':boost-status', $boost['enable']);
                    $this->checkAndUpdateCmd($i . ':boost-remaining', $boost['remaining']);
                }
            }

            //    $this->refreshWidget();
        }
    }
    // ================================================================================
    public function preUpdate()
    {
        if ($this->getConfiguration('iphealthbox') == '') {
            throw new Exception(__('Veuillez entrer une IP', __FILE__));
        }
    }
    // ================================================================================

    public function getNameCmd($i, $room, $name)
    {
        $NamePLogical = $i . ':' . $name;

        $logic = $this->getCmd(null, $NamePLogical);
        if (!is_object($logic)) {
            $logic = new healthboxCmd();
        }
        if ($room == '') {
            $logic->setName(__($name, __FILE__));
        } else {
            $logic->setName(__($room . ':' . $name, __FILE__));
        }
        $logic->setLogicalId($NamePLogical);
        $logic->setEqLogic_id($this->getId());
        $logic->setIsVisible(0);
        return $logic;
    }
    // ================================================================================
    public function postSave()
    {
        $api = new healthbox_api($this->getConfiguration('iphealthbox'));
        $data = $api->getData();
        if ($data) {

            $device_type = $this->getNameCmd('0', '', 'device_type');
            $device_type->setType('info');
            $device_type->setSubType('string');
            $device_type->save();

            foreach ($data['room'] as $i => $room) {

                $room_name = $room['name'];

                $debit = $this->getNameCmd($i, $room_name, 'debit');
                $debit->setType('info');
                $debit->setUnite('%');
                $debit->setIsHistorized(1);
                $debit->setSubType('numeric');
                $debit->save();

                $profil = $this->getNameCmd($i, $room_name, 'profil');
                $profil->setType('info');
                $profil->setSubType('numeric');
                $profil->setConfiguration("minValue", 0);
                $profil->setConfiguration("maxValue", 2);
                $profil->save();

                foreach ($room['sensor'] as $sensor) {
                    switch ($sensor['type']) {
                        case "indoor relative humidity":
                            $humidity = $this->getNameCmd($i, $room_name, 'humidity');
                            $humidity->setType('info');
                            $humidity->setSubType('numeric');
                            $humidity->setUnite('%');
                            $humidity->setConfiguration("minValue", 0);
                            $humidity->setConfiguration("maxValue", 100);
                            $humidity->save();
                            break;
                        case "indoor temperature":
                            $temperature = $this->getNameCmd($i, $room_name, 'temperature');
                            $temperature->setType('info');
                            $temperature->setSubType('numeric');
                            $temperature->setUnite('°C');
                            $temperature->save();
                            break;
                        case "indoor CO2":
                            $CO2 = $this->getNameCmd($i, $room_name, 'CO2');
                            $CO2->setType('info');
                            $CO2->setSubType('numeric');
                            $CO2->setIsHistorized(1);
                            $CO2->setUnite('ppm');
                            $CO2->save();
                            break;
                        case "indoor volatile organic compounds":
                            $CO2 = $this->getNameCmd($i, $room_name, 'COV');
                            $CO2->setType('info');
                            $CO2->setIsHistorized(1);
                            $CO2->setSubType('numeric');
                            $CO2->setUnite('ppm');
                            $CO2->save();
                            break;
                    }
                }
             
                $boostenable = $this->getNameCmd($i, $room_name, 'boost-status');
                $boostenable->setType('info');
                $boostenable->setSubType('binary');
                $boostenable->save();

                $boostremaining = $this->getNameCmd($i, $room_name, 'boost-remaining');
                $boostremaining->setType('info');
                $boostremaining->setSubType('numeric');
                $boostremaining->save();

                $changeProfil = $this->getNameCmd($i, $room_name, 'changeProfil');
                $changeProfil->setType('action');
                $changeProfil->setSubType('slider');
                $changeProfil->setConfiguration("minValue", 0);
                $changeProfil->setConfiguration("maxValue", 2);
                $changeProfil->setConfiguration("listValue", '0|eco;1|health;2|intense');
                $changeProfil->setValue($profil->getId());
                $changeProfil->save();

                $boostON = $this->getNameCmd($i, $room_name, 'boost-toogle');
                $boostON->setType('action');
                $boostON->setSubType('other');
                $boostON->setConfiguration("request", '{"level": 200, "timeout": 900};');
                $boostON->setValue($boostenable->getId());
                $boostON->save();

            }

            if ($this->getIsEnable() == 1) {
                $this->updatehealthbox();
            }
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
    // ================================================================================
    public function execute($_options = array())
    {
        if ($this->getType() == 'info') {
            return;
        }

        $eqLogic = $this->getEqlogic();

        $index = explode(":", $this->getLogicalId())[0];
        $command = explode(":", $this->getLogicalId())[1];

        if ($this->getSubType() == 'slider') {
            $value = $_options['slider'];
        } else if ($this->getSubType() == 'select') {
            $value = $_options['select'];
        } else {
            $value = $this->getConfiguration("request", "");
        }

        $result = jeedom::evaluateExpression($value);

        switch ($command) {

            case "changeProfil":
                if (is_numeric($result)) {
                    $api = new healthbox_api($eqLogic->getConfiguration('iphealthbox'));
                    $api->changeProfil($index, intval($result));
                } else {
                    log::add('healthbox', 'error', 'Commande changeProfil : Donnée non numérique');
                    return false;
                }
                break;

            case "boost-toogle":
                $request = substr($result, 0, -1);

                $new = json_decode($request, true);
                $new['enable'] = true;
                $request = json_encode($new);

                $onoff = $this->byId($this->getValue())->execCmd();

                $api = new healthbox_api($eqLogic->getConfiguration('iphealthbox'));

                if ($onoff === 0) {

                    if ($this->isJson($request)) {
                        $api->enableBoost($index, $request);
                    } else {
                        log::add('healthbox', 'error', 'Commande boostON : JSON invalide');
                        return false;
                    }

                } else {
                    $api->disableBoost($index);
                }
                break;
        }

        if ($eqLogic->getIsEnable() == 1) {
            $eqLogic->updatehealthbox();
        }
    }
}