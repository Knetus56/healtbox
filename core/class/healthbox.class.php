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
        $ap = $api->getNbPiece();
        //   log::add('healthbox', 'info', $ap);

        $this->checkAndUpdateCmd('0:' . 'device_type', $api->getDevice());

        for ($i = 1; $i <= $ap; $i++) {
            $NamePiece = str_replace(" ", "_", $api->getNamePiece($i));
            $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':temperature', $api->getTemperature($i));
            $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':humidity', $api->getHumidity($i));
            $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':profil', $api->getProfil($i));
            $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':debit', $api->getDebit($i));
            $CO2 = $api->isCO2($i);
            if ($CO2) {
                $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':CO2', $api->getCO2($i));
            }
            $COV = $api->isCOV($i);
            if ($COV) {
                $this->checkAndUpdateCmd($i . ':' . $NamePiece . ':COV', $api->getCOV($i));
            }
        }

        $this->refreshWidget();
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
    public function postUpdate()
    {
    }

    // ================================================================================
    public function preSave()
    {
    }

    // ================================================================================
    public function setLogical($i, $room, $Type, $Unit, $SubType)
    {

        $NamePiece = str_replace(" ", "_", $i . ':' . $room);

        $logic = $this->getCmd(null, $NamePiece);
        if (!is_object($logic)) {
            $logic = new healthboxCmd();
        }
        $logic->setName(__($room, __FILE__));
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
        $ap = $api->getNbPiece();
        //   log::add('healthbox', 'info', $ap);

        $this->setLogical(0, 'device_type', 'info', '', 'string');

        for ($i = 1; $i <= $ap; $i++) {

            $NamePiece = $api->getNamePiece($i);
            $this->setLogical($i, $NamePiece . ':temperature', 'info', '°C', 'numeric');
            $this->setLogical($i, $NamePiece . ':humidity', 'info', '%', 'numeric');
            $this->setLogical($i, $NamePiece . ':debit', 'info', '%', 'numeric');
            $this->setLogical($i, $NamePiece . ':profil', 'info', '', 'numeric');

            $CO2 = $api->isCO2($i);
            if ($CO2) {
                $this->setLogical($i, $NamePiece . ':CO2', 'info', 'ppm', 'numeric');
            }

            $COV = $api->isCOV($i);
            if ($COV) {
                $this->setLogical($i, $NamePiece . ':COV', 'info', 'ppm', 'numeric');
            }

            $this->setLogical($i, $NamePiece . ':boostON', 'action', '', 'other');
            $this->setLogical($i, $NamePiece . ':boostOFF', 'action', '', 'other');
            $this->setLogical($i, $NamePiece . ':changeProfil', 'action', '', 'other');

        }

        if ($this->getIsEnable() == 1) {
            $this->updatehealthbox();
        }
    }

}
// ================================================================================
class healthboxCmd extends cmd
{
    public function execute($_options = array())
    {

        if ($this->getType() == 'info') {
            return;
        }

        $request = $this->getConfiguration("request", "");
        $r = $this->getLogicalId();

        $p = explode(":", $r);

        $id = $p[0];
        $req = $p[2];

        $eqLogic = $this->getEqlogic();
        $device_type = $eqLogic->getConfiguration('iphealthbox');

        if ($req == 'changeProfil') {
            $api = new healthbox_api($device_type);
            $api->changeProfil($id, intval($request));

        }

    }
}
