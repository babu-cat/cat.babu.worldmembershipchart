<?php

use CRM_WorldMembershipChart_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_WorldMembershipChart_Form_Main extends CRM_Core_Form {
  public function buildQuickForm() {
    $language = array();
    $language = CRM_Admin_Form_Setting_Localization::getDefaultLocaleOptions();
    $options[CRM_Core_I18n::getLocale()] = $language[CRM_Core_I18n::getLocale()];
    $options = array_merge($options,$language);
    $this->addEntityRef('membership_type', 'Tipo de Membresia', array(
      'entity' => 'MembershipType',
      'multiple' => TRUE,
      'placeholder' => ts('- any -'),
      'select' => array('minimumInputLength' => 0),
    ));
    $this->add(
      'text',
      'membership_name',
      'Nombre Etiqueta',
      '',
      TRUE
    );
    $this->add(
      'text',
      'color_max',
      'Color mas oscuro',
      array('placeholder' => ts('#D52027'))
    );
    $this->add(
      'text',
      'color_min',
      'Color mas claro',
      array('placeholder' => ts('#F8DAD9'))
    );
    $this->add(
      'text',
      'division_num',
      'Cantidad de divisiones',
      array('placeholder' => ts('5'))
    );
    $this->add(
      'text',
      'division_point',
      'Puntos de divisiones',
      array('placeholder' => ts('50,200,500,1000,10000'))
    );
    $this->add(
      'select',
      'idioma',
      'Selecciona el Idioma',
      $options
    );
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Get Code'),
        'isDefault' => TRUE,
      ),
    ));
    parent::buildQuickForm();
  }

  public function postProcess() {
    $export = $this->exportValues();
    $it = explode(',',$export['membership_type']);
    $colorMax=$export['color_max'];
    $colorMin=$export['color_min'];
    $divisionNum=$export['division_num'];
    $divisionPoint=$export['division_point'];
    $idioma=$export['idioma'];
    $code = array();
    if ($export['membership_type']) {
      $membership = civicrm_api3('Membership', 'get', [
        'sequential' => 1,
        'membership_type_id' => ['IN' => $it],
        'status_id' => ['IN' => ["New", "Current"]],
        'contact_id.is_deleted' => 0,
        'options' => ['limit' => 0],
        'return' => ["contact_id"],
      ]);
    } else {
      $membership = civicrm_api3('Membership', 'get', [
        'sequential' => 1,
        'status_id' => ['IN' => ["New", "Current"]],
        'contact_id.is_deleted' => 0,
        'options' => ['limit' => 0],
        'return' => ["contact_id"],
      ]);
    }
    $countries = civicrm_api3('Country', 'get', [
      'sequential' => 1,
      'return' => ["id", "iso_code", "name"],
      'options' => ['limit' => 0],
    ]);
    if(empty($colorMax)) {
      $colorMax = '#D52027';
    }
    if(empty($colorMin)) {
      $colorMin = '#F8DAD9';
    }
    if(empty($divisionNum)) {
      $divisionNum = 5;
    }
    if(empty($divisionPoint)) {
      $divisionPoint = array(50,200,500,1000,10000);
    } else {
      $divisionPoint = explode(',',$divisionPoint);
    }
    $a1 = hexdec(substr($colorMax,1,2));
    $a2 = hexdec(substr($colorMax,3,2));
    $a3 = hexdec(substr($colorMax,5,2));
    $b1 = hexdec(substr($colorMin,1,2));
    $b2 = hexdec(substr($colorMin,3,2));
    $b3 = hexdec(substr($colorMin,5,2));
    $colorIndex = $this->lineargradient(
      $a1, $a2 , $a3,  // rgb of the start color
      $b1, $b2, $b3, // rgb of the end color
      $divisionNum+1     // number of colors in your linear gradient
    );//ejecuta la funcion y saca 5 colores mas el 6 es el color asignado
    foreach ($countries['values'] as $key => $value) {
      $countryCode[$countries['values'][$key]['id']] = $countries['values'][$key]['iso_code'];
      $countryName[$countries['values'][$key]['iso_code']] = $countries['values'][$key]['name'];
      $country[$countries['values'][$key]['iso_code']] = $countries['values'][$key]['id'];
      $code[$countries['values'][$key]['iso_code']] = 0;
    }
    foreach ($membership['values'] as $k => $v) {
      $ids[] = $v['contact_id'];
    }
    $contact = civicrm_api3('Contact', 'get', [
      'id' => ['IN' => $ids],
      'options' => ['limit' => 0],
      "return" => ["country"],
    ]);
    foreach ($contact['values'] as $k => $v) {
      $countryId[$v['contact_id']] = $v['country_id'];
    }
    foreach ($membership['values'] as $k => $v) {
      if (isset($countryId[$v['contact_id']]) ) {
        if ( !isset ($code[$countryCode[$countryId[$v['contact_id']]]] ) ) {
          $code[$countryCode[$countryId[$v['contact_id']]]]=1;
        }
        else {
          $code[$countryCode[$countryId[$v['contact_id']]]]+=1;
        }
      }
    }
    $i18n = new CRM_Core_I18n($idioma);
    $i18n->localizeArray($country, array(
      'context' => 'country',
    ));
    $i18n->setlocale($idioma);

    $css = '&lt;style type="text/css"&gt;'."\n" . '
      .knobContainer {'."\n".'    text-align: center;'."\n".'    margin: 10px;'."\n".'  }'."\n".'
      .knobContainer canvas {'."\n".'    cursor: pointer;'."\n".'  }'."\n".'
      .mapael .mapTooltip {'."\n".'    position: absolute;'."\n".'    background-color: #fff;'."\n".'    moz-opacity: 0.80;'."\n".'    opacity: 0.80;'."\n".'    filter: alpha(opacity=80);'."\n".'    border-radius: 4px;'."\n".'    padding: 10px;
      z-index: 1000;'."\n".'    max-width: 200px;'."\n".'    display: none;'."\n".'    color: '.$colorMax.';'."\n".'  }'."\n".'
      .mapael .map {'."\n".'    overflow: hidden;'."\n".'    position: relative;'."\n".'    background-color: #ffffff;'."\n".'    border-radius: 5px;'."\n".'  }'."\n".'
      .mapael .zoomButton {'."\n".'    background-color: #aaa;'."\n".'    border: 1px solid #fff;'."\n".'    color: #fff;'."\n".'    width: 15px;'."\n".'    height: 15px;'."\n".'    line-height: 15px;
      text-align: center;'."\n".'    border-radius: 3px;'."\n".'    cursor: pointer;'."\n".'    position: absolute;'."\n".'    top: 0;'."\n".'    font-weight: bold;'."\n".'    left: 10px;
      -webkit-user-select: none;'."\n".'    -khtml-user-select : none;'."\n".'    -moz-user-select: none;'."\n".'    -o-user-select : none;'."\n".'    user-select: none;'."\n".'  }'."\n".'
      .mapael .zoomReset {'."\n".'    top: 10px;'."\n".'  }'."\n".'
      .mapael .zoomIn {'."\n".'    top: 30px;'."\n".'  }'."\n".'
      .mapael .zoomOut {'."\n".'    top: 50px;'."\n".'  }'."\n".'
      &lt;/style&gt;';
    $script = '&lt;script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js" charset="utf-8"&gt;&lt;/script&gt;
      &lt;script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.13/jquery.mousewheel.min.js" charset="utf-8"&gt;&lt;/script&gt;
      &lt;script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.2.7/raphael.min.js" charset="utf-8"&gt;&lt;/script&gt;
      &lt;script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mapael/2.2.0/js/jquery.mapael.js" charset="utf-8"&gt;&lt;/script&gt;
      &lt;script src="https://rawgit.com/aterrien/jQuery-Knob/master/dist/jquery.knob.min.js" charset="utf-8"&gt;&lt;/script&gt;
      &lt;script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mapael/2.2.0/js/maps/world_countries.js" charset="utf-8"&gt;&lt;/script&gt;';
    $data .= '&lt;script type="text/javascript"&gt;'."\n".'  $(function () {'."\n".'    function oscurecerColor(color, cant){
      var rojo = color.substr(1,2);'."\n".'      var verd = color.substr(3,2);'."\n".'      var azul = color.substr(5,2);'."\n".'      var introjo = parseInt(rojo,16);
      var intverd = parseInt(verd,16);'."\n".'      var intazul = parseInt(azul,16);'."\n".'      if ((introjo+cant<=255)&&(introjo+cant>=0)) introjo = introjo+cant;
      if ((intverd+cant<=255)&&(intverd+cant>=0)) intverd = intverd+cant;'."\n".'      if ((intazul+cant<=255)&&(intazul+cant>=0)) intazul = intazul+cant;
      rojo = introjo.toString(16);'."\n".'      verd = intverd.toString(16);'."\n".'      azul = intazul.toString(16);'."\n".'      if (rojo.length<2) rojo = "0"+rojo;
      if (verd.length<2) verd = "0"+verd;'."\n".'      if (azul.length<2) azul = "0"+azul;'."\n".'      var oscuridad = "#"+rojo+verd+azul;
      return oscuridad;'."\n".'    }'."\n".'    var data = {'."\n".'      "areas" : {'."\n";
    foreach ($code as $cod => $co) {
      if ($cod != '') {
        $data .= '        "'.$cod.'": { "value" : ' .$co.',"tooltip": {"content": "&lt;span style=\"font-weight:bold;\"&gt;'.$i18n->crm_translate($countryName[$cod], array('context' => 'country')).'&lt;/span&gt;';
        if ($co != 0) {
          $data.='&lt;br/&gt; ' .number_format($co,0, '', '.').' '.$export['membership_name'].'"}},'."\n";
        } else {
          $data .= '"}},'."\n";
        }
      }
    }
    $data .= '       }'."\n".'    };
      $(".knob").knob({'."\n".'      release: function (value) {'."\n".'        $(".world").trigger("update", [{'."\n".'          mapOptions: data[value],'."\n".'          animDuration: 300'."\n".'        }]);'."\n".'      }'."\n".'    });
      $world = $(".world");'."\n".'    $world.mapael({'."\n".'      map: {'."\n".'        name: "world_countries",'."\n".'        defaultArea: {'."\n".'          attrs: {
      fill: "#CCCCCC",'."\n".'            stroke: "#ffffff",'."\n".'            "stroke-width": 1'."\n".'          }'."\n".'          , attrsHover: { '."\n".'            fill: oscurecerColor("#CCCCCC", 25),'."\n".'          }'."\n".'        },
      zoom: {'."\n".'          enabled: true'."\n".'          , step: 0.25'."\n".'          , maxLevel: 20'."\n".'        }'."\n".'      },
      legend: {'."\n".'        area: {'."\n".'          display: true,'."\n".'          title: "'.$export['membership_name'].'",'."\n".'          marginBottom: 7,'."\n".'          slices: ['."\n";
    $data .= '            {'."\n";
    $data .= '              max: 0,'."\n".'              attrs: { fill: "#CCCCCC" },'."\n";
    $data .= '              label: "0" '."\n";
    $data .= '              , attrsHover: { '."\n".'                fill: oscurecerColor("#CCCCCC", 25),';
    $data .=  '          }'."\n".'            },'."\n";
    foreach ($divisionPoint as $pointNum => $point) {
      $data .= '            {'."\n";
      if (isset($divisionPoint[$pointNum-1])) {
        $data .= '              min: '.$divisionPoint[$pointNum-1].', '."\n";
      } else {
        $data .= '              min: 1, '."\n";
      }
      $data .= '              max: '.$divisionPoint[$pointNum].','."\n".'              attrs: { fill: "'.$colorIndex[$pointNum].'" },'."\n";
      if (isset($divisionPoint[$pointNum-1])) {
        $data .= '              label: " '.number_format($divisionPoint[$pointNum-1],0, '', '.').' - '.number_format($divisionPoint[$pointNum],0, '', '.').' " '."\n";
      } else {
        $data .= '              label: "&lt; '.number_format($divisionPoint[$pointNum],0, '', '.').'" '."\n";
      }
      $data .= '              , attrsHover: { '."\n".'                fill: oscurecerColor("'.$colorIndex[$pointNum].'", 25),';
      $data .=  '          }'."\n".'            },'."\n";
    }
    $data .= '            {'."\n".'              min: '.$divisionPoint[count($divisionPoint)-1].','."\n".'              attrs: { fill: "'.$colorMax.'" },'."\n".
      '           label: "&gt; '.number_format($divisionPoint[count($divisionPoint)-1],0, '', '.').'"'."\n".'              , attrsHover: {'."\n".'                fill: oscurecerColor("'.$colorMax.'", 25),'."\n".'              }'."\n".'            }'."\n".'          ]'."\n".'        },'."\n".'      },'."\n".'      areas: data["areas"]'."\n".'    });'."\n".'  });'."\n".'&lt;/script&gt;';
    $body = '&lt;div class="world row"&gt;'."\n".'    &lt;div class="map col-xs-12 col-sm-10"&gt;&lt;/div&gt;'."\n" .
      '    &lt;div class="rightPanel col-xs-12 col-sm-2"&gt;'."\n".'      &lt;div class="areaLegend"&gt;&lt;/div&gt;'."\n".'    &lt;/div&gt;'."\n".'    &lt;div style="clear: both;"&gt;&lt;/div&gt;'."\n".'  &lt;/div&gt;'."\n".'&lt;/div&gt;';
    $this->assign('data', $data);
    $this->assign('css', $css);
    $this->assign('script', $script);
    $this->assign('body', $body);
    parent::postProcess();
  }

  function lineargradient($ra,$ga,$ba,$rz,$gz,$bz,$iterationnr) {
    $colorIndex = array();
    for ($iterationc=1; $iterationc<=$iterationnr; $iterationc++) {
      $iterationdiff = $iterationnr-$iterationc;
      $colorIndex[] = '#'.
        dechex(intval((($ra*$iterationc)+($rz*$iterationdiff))/$iterationnr)) .
        dechex(intval((($ga*$iterationc)+($gz*$iterationdiff))/$iterationnr)) .
        dechex(intval((($ba*$iterationc)+($bz*$iterationdiff))/$iterationnr));
    }
    return $colorIndex;
  }
}
