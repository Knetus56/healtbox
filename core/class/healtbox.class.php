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

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../3rdparty/healtbox_api.class.php';

class healtbox extends eqLogic
{


  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert()
  {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert()
  {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate()
  {
    if ($this->getConfiguration('ip') == '') {
      throw new Exception(__('Veuillez entrer une IP', __FILE__));
    }
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate()
  {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave()
  {
  }
  public function setLogical($Name, $Type, $Unit, $SubType)
  {
    $logic = $this->getCmd(null, $Name);
    if (!is_object($logic)) {
      $logic = new healtboxCmd();
    }
    $logic->setName(__($Name, __FILE__));
    $logic->setLogicalId($Name);
    $logic->setEqLogic_id($this->getId());
    $logic->setType($Type);
    $logic->setUnite($Unit);
    $logic->setSubType($SubType);
    $logic->save();
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave()
  {
    $api = new healtbox_api($this->getConfiguration('ip'));
    $ap = $api->getNbPiece();
  //   log::add('healtbox', 'info', $ap);

    $this->setLogical('device_type', 'info', '', 'string');
   
    for ($i = 1; $i <= $ap; $i++) {

      $NamePiece = str_replace(" ", "_", $api->getNamePiece($i));

      $this->setLogical('device_type', 'info', '', 'string');
      $this->setLogical($NamePiece . ':temperature', 'info', '°C', 'numeric');
      $this->setLogical($NamePiece . ':humidity', 'info', '%', 'numeric');
      $this->setLogical($NamePiece . ':debit', 'info', '%', 'numeric');
      $this->setLogical($NamePiece . ':profil', 'info', '', 'string');

      $CO2 = $api->isCO2($i);
      if ($CO2) {
        $this->setLogical($NamePiece . ':CO2', 'info', 'ppm', 'numeric');
      }

      $COV = $api->isCOV($i);
      if ($COV) {
        $this->setLogical($NamePiece . ':COV', 'info', 'ppm', 'numeric');
      }
    }
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove()
  {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove()
  {
  }


}
class healtboxCmd extends cmd
{
  /*     * *************************Attributs****************************** */

  public static $_widgetPossibility = array('custom' => false);

  /*     * ***********************Methode static*************************** */

  /*     * *********************Methode d'instance************************* */

  public function execute($_options = array())
  {
    if ($this->getLogicalId() == 'refresh') {
      $this->getEqLogic()->updateWeatherData();
    }

    return false;
  }
}