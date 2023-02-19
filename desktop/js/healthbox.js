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


///////////////////////////////////////////////////////////////////////////////////////////////////
// Modals associated to buttons "Rechercher équipement" for Action and Info Cmd
//
$("#table_cmd").delegate(".listEquipementAction", 'click', function () {
  var el = $(this);
  jeedom.cmd.getSelectModal({ cmd: { type: 'action' } }, function (result) {
    var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.attr('data-input') + ']');
    calcul.value(result.human);
  });
});

$("#table_cmd").delegate(".listEquipementInfo", 'click', function () {
  var el = $(this);
  jeedom.cmd.getSelectModal({ cmd: { type: 'info' } }, function (result) {
    var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.data('input') + ']');
    calcul.atCaret('insert', result.human);
    jeeFrontEnd.modifyWithoutSave = true
  });
});

///////////////////////////////////////////////////////////////////////////////////////////////////
/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
})

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} }
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  if (init(_cmd.type) == 'info') {
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
    tr += '<td class="hidden-xs">'
    tr += '<span class="cmdAttr" data-l1key="id"></span>'
    tr += '</td>'
    tr += '<td>'
    tr += '<div class="input-group">'
    tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
    tr += '<span class="input-group-btn">'
    tr += '<a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a>'
    tr += '</span>'
    tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
    tr += '</div>'
    tr += '</td>'
    tr += '<td>'
    tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="margin-bottom:5px;">'
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
    tr += '</td>'
    tr += '<td></td>'
    tr += '<td>'
    tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
    tr += '</td><td>';
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
    tr += '<div style="margin-top:7px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '</div>'
    tr += '</td><td>';
    if (is_numeric(_cmd.id)) {
      tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
      tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>'
    tr += '</tr>'
    $('#table_cmd tbody').append(tr)
    $('#table_cmd tbody tr').last().setValues(_cmd, '.cmdAttr')

    if (isset(_cmd.type)) {
      $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type))
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr').last(), init(_cmd.subType))
  }

  if (init(_cmd.type) == 'action') {
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td class="hidden-xs">'
    tr += '<span class="cmdAttr" data-l1key="id"></span>'
    tr += '</td>'
    tr += '<td>'
    tr += '<div class="input-group">'
    tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
    tr += '<span class="input-group-btn">'
    tr += '<a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a>'
    tr += '</span>'
    tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
    tr += '</div>'
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande information liée}}">'
    tr += '<option value="">{{Aucune}}</option>';
    tr += '</select>'
    tr += '</td>'
    tr += '<td>'
    tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="action" disabled style="margin-bottom:5px;" />'
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
    tr += '<input class="cmdAttr" data-l1key="configuration" data-l2key="virtualAction" value="1" style="display:none;" />'
    tr += '</td>'
    tr += '<td>'

		tr += '<textarea class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="request" style="height:18px;" placeholder="{{Valeur}}"></textarea>';
		tr += '<a class="btn btn-default btn-sm cursor listEquipementInfo" data-input="request" style="margin-top:5px;"><i class="fas fa-list-alt "></i> {{Rechercher équipement}}</a>';
		tr +='</select></span>';
    tr += '</td>'
    tr += '<td>'
    tr += '</td>'
    tr += '<td>'
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
    tr += '<div style="margin-top:7px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="listValue" placeholder="{{Liste : valeur|texte (séparées par un point-virgule)}}" title="{{Liste : valeur|texte}}">'
    tr += '</div>'
    tr += '</td>';
    tr += '<td>'
    if (is_numeric(_cmd.id)) {
      tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
      if (init(_cmd.type) == 'action') {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
      }
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>'
    tr += '</tr>'


    $('#table_cmd tbody').append(tr)
    var tr = $('#table_cmd tbody tr').last()
    jeedom.eqLogic.buildSelectCmd({
      id: $('.eqLogicAttr[data-l1key=id]').value(),
      filter: { type: 'info' },
      error: function (error) {
        $('#div_alert').showAlert({ message: error.message, level: 'danger' })
      },
      success: function (result) {
        tr.find('.cmdAttr[data-l1key=value]').append(result)
        tr.setValues(_cmd, '.cmdAttr')
        jeedom.cmd.changeType(tr, init(_cmd.subType))
      }
    })
  }
}
