<?php

namespace General;

use Model\Protocol;

use Model\Division;

use \TranslateController as Translate;

/**
 * @brief Statyczna klasa kontrolek
 * @author Paweł
 */
class Controls extends StaticUtils {

	/**
	 * Poziomy wskaźnik postępu
	 * @param int $current
	 * @param int $max
	 * @param array $options
	 * @return string
	 */
	static public function horizontalBar($current, $max, array $options = null)
	{

		$retVal = '';

		if (empty($options['width'])) {
			$options['width'] = 100;
		}
		if (empty($options['height'])) {
			$options['height'] = 16;
		}

		$tPercentage = $current / $max;

		$tInternal = floor($options['width'] * $tPercentage);

		if ($tInternal > $options['width']) {
			$tInternal = $options['width'];
		}

		$retVal .= '<div style="width: ' . $options['width'] . 'px; height: ' . $options['height'] . 'px;" class="hRuler"';

		if (!empty($options['title'])) {
			$retVal .= ' title="' . $options['title'] . '" ';
		}

		$retVal .= '>';
		$retVal .= '<div style="width: ' . $tInternal . 'px; height: ' . $options['height'] . 'px;"></div>';
		$retVal .= '</div>';

		return $retVal;
	}

	static public function getCurrentUrl()
	{
		$pageURL = 'http';
		if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		}
		else {
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	static public function openForm($class)
	{
		return "<form method='post' action='' name='myForm' onsubmit=\"checkSubmit('" . $class . "'); return false;\">";
	}

	static public function closeForm()
	{
		return "</form>";
	}

	static public function builUl($tData, $textField = 'Name')
	{
		$retVal = '<ul>';
		$tIndex = 0;
		foreach ($tData as $tKey => $tValue) {
			$tIndex++;
			$retVal .= '<li>' . $tValue[$textField] . '</li>';
		}

		$retVal .= '</ul>';
		return $retVal;
	}

	/**
	 * Wyrenderowanie tabeli na podstawie danych
	 *
	 * @param mixed $tData
	 * @param int $cols liczba kolumn w tabeli
	 * @return string
	 */
	static public function builTable($tData, $cols = 4)
	{
		$retVal = '<table class="table table-striped table-bordered table-condensed">';
		$retVal .= '<tbody><tr>';
		$tIndex = 0;
		foreach ($tData as $tKey => $tValue) {

			$tIndex++;

			$retVal .= '<th>' . $tKey . '</th>';
			$retVal .= '<td>' . $tValue . '</td>';

			if ($tIndex % $cols == 0) {
				$retVal .= '</tr><tr>';
			}
		}

		$retVal .= '</tr></tbody>';
		$retVal .= '</table>';
		return $retVal;
	}

	/**
	 * Wyrenderowanie tabeli na podstawie danych
	 *
	 * @param mixed $tData
	 * @return string
	 */
	static public function builEditTable($tData, $cols = 4)
	{
		$retVal = '<table class="table table-striped table-bordered table-condensed" border="0">';
		$retVal .= '<tbody><tr>';
		$tIndex = 0;
		foreach ($tData as $tKey => $tValue) {

			$tIndex++;

			$retVal .= '<th>' . $tKey . '</th>';
			$retVal .= '<td>' . self::renderInput('text', $tValue, $tKey) . '</td>';

			if ($tIndex % $cols == 0) {
				$retVal .= '</tr><tr>';
			}
		}

		$retVal .= '</tr></tbody>';
		$retVal .= '</table>';
		return $retVal;
	}

	/**
	 * Przeładowanie strony na adres
	 *
	 * @param string $address
	 */
	static public function pageReload($address)
	{
		$host = $_SERVER ['HTTP_HOST'];
		$uri = rtrim(dirname($_SERVER ['PHP_SELF']), '/\\');
		$extra = $address;
		header("Location: http://$host$uri/$extra");
	}

	/**
	 * @param string $name Nazwa selecta
	 * @param string $value Klucz wybranego domyślnie elementu
	 * @param array $values Tablica Klucz - Wartość dla zawartości selecta
	 * @param array $opts Dodatkowe parametry dla selecta
	 * @return string
	 */
	static function renderSelect($name, $value = "", $values = array(), $opts = array())
	{

		$retVal = "";

		if (empty($opts ['id'])) {
			$opts ['id'] = $name;
		}

		$tOpts = '';
		foreach ($opts as $tKey => $tValue) {
			$tOpts .= ' ' . $tKey . '="' . $tValue . '"';
		}

		$retVal .= "<select name='" . htmlspecialchars($name) . "' {$tOpts}>";
		foreach ($values as $k => $v) {

			$sTitle = $v;

			if (mb_strlen($v) > 52) {
				$v = mb_substr($v, 0, 50).' (...)';
			}

			$sel = '';

			if (is_array($value)) {

				if (array_search($k, $value) !== false) {
					$sel = "selected='selected'";
				}

			}
			else {
				$sel = ($k == $value) ? "selected='selected'" : '';
			}

			$retVal .= "<option title='{$sTitle}' $sel value='" . htmlspecialchars($k) . "' >" . htmlspecialchars($v) . "</option>";
		}
		$retVal .= "</select>";

		return $retVal;
	}

	/**
	 * Wyrenderowanie pola formularza
	 *
	 * @param string $type Typ pola
	 * @param string $value Wartość domyślna
	 * @param string $name
	 * @param string $id
	 * @param int $size
	 * @param string $class
	 * @param string $style
	 * @return string
	 */
	static function renderInput($type, $value = "", $name = "", $id = "", $size = 30, $class = "", $style = "")
	{

		$retVal = "";

		$originalValue = $value;

		$orgId = $id;
		$orgName = $name;
		$orgValue = $value;

		/*
		 * Sformatuj parametr name
		*/
		if ($name != "") {
			$name = "name=\"" . $name . "\"";
		}
		else {
			$name = "";
		}

		/*
		 * Sformatuj parametr id
		*/
		if ($id != "") {
			$id = "id=\"" . $id . "\"";
		}
		else {
			$id = "";
		}

		if ($type == 'decimal') {
			$size = 10;
		}

		if ($type == 'nuber') {
			$size = 10;
		}

		if ($size <= 10) {
			$class .= " span1";
		}


		if ($type == 'date') {
			$class .= " date-picker span2";

			if (empty($value)) {
				// 				$value = date('d-m-Y');
			}

		}

		if ($type == 'time') {
			$class .= " time-picker span1";
		}

		/*
		 * Sformatuj parametr class
		*/
		if (!empty($class)) {
			$class = "class=\"" . $class . "\"";
		}
		else {
			$class = "";
		}

		$class = trim($class);

		/*
		 * Sformatuj parametr style
		*/
		if ($style != "") {
			$style = "style=\"" . $style . "\"";
		}
		else {
			$style = "";
		}

		/*
		 * Sformatuj parametr value
		*/
		if ($value !== "" && $value !== null) {
			$value = "value=\"" . $value . "\"";
		}
		else {
			$value = "";
		}

		$tSize = "size=\"" . $size . "\"";

		/*
		 * Wyrenderuj input
		*/
		switch ($type) {


			case "gcs" :
				$retVal .= "<input type=\"text\" size='2' $name $id $value class='span1' max='15' onkeyup=\"FormValidators.mask(this,0)\" onblur=\"FormValidators.mask(this,0)\" />\n";

				$retVal .= "<span class='btn gcs' for='{$orgName}'>{T:Kalkulator}</span>";

				break;

			case "mth" :
				$retVal .= "<input type=\"text\" size='1' $name $id $value class='span1' max='5' onkeyup=\"FormValidators.mask(this,0)\" onblur=\"FormValidators.mask(this,0)\" />\n";

				break;
					
			case "password" :
				$retVal .= "<input type=\"password\" $tSize $name $id $value $class $style onkeyup=\"FormValidators.mask(this,99)\" onblur=\"FormValidators.mask(this,99)\" />\n";
				break;

			case "number" :
				$retVal .= "<input type=\"text\" $tSize $name $id $value $class $style onkeyup=\"FormValidators.mask(this,0)\" onblur=\"FormValidators.mask(this,0)\" />\n";
				break;

			case "nacl_volume" :
				$retVal .= "<input max='10000' type=\"text\" $name $id $value $class $style onkeyup=\"FormValidators.mask(this,0)\" onblur=\"FormValidators.mask(this,0)\" />\n";
				break;

			case "age" :
				$retVal .= "<input max='110' type=\"text\" size='3' $name $id $value $class $style onkeyup=\"FormValidators.mask(this,0)\" onblur=\"FormValidators.mask(this,0)\" />\n";
				break;

			case "decimal" :
				$retVal .= "<input type=\"text\" $tSize $name $id $value $class $style onkeyup=\"FormValidators.mask(this,1)\" onblur=\"FormValidators.mask(this,1)\" />\n";
				break;

			case "checkbox" :
				if ($originalValue == true || $originalValue == 'yes') {
					$checked = "checked";
				}
				else {
					$checked = "";
				}
				$retVal .= "<input type=\"checkbox\" value=\"1\" $name $id $class $style $checked  />\n";
				break;

			case "radio" :
				$retVal .= "<input type=\"radio\" $value $name $id $class $style />\n";
				break;

			case "hidden" :
				$retVal .= "<input type=\"hidden\" $value $name $id />\n";
				break;

			case "submit" :
				$retVal .= "<input type=\"submit\" $value $name $id />\n";
				break;

			case "textarea" :
				$retVal .= "<textarea $name $id $class $style onkeyup=\"javascript:return mask(this.value,this," . $size . ",7)\" onblur=\"javascript:return mask(this.value,this," . $size . ",7)\" cols=\"72\" rows=\"8\">" . $originalValue . "</textarea>\n";
				break;

			case "date" :
				$retVal .= "<input type=\"text\" $tSize $name $id $value $class $style onkeyup=\"FormValidators.mask(this,99)\" onblur=\"FormValidators.mask(this,99)\" />\n";
				break;

			case "datetime" :

				if (empty($orgValue)) {
					$valueDate = '';
					// 					$valueDate = date('d-m-Y');
					$valueTime = '';
				}else {
					$valueDate = date('d-m-Y', strtotime($orgValue));
					$valueTime = date('H:i', strtotime($orgValue));
				}

				$retVal .= "<input type='text' class='date-picker span2' name='{$orgName}-date' value='{$valueDate}' /> ";
				$retVal .= "<input type='text' class='time-picker span1' name='{$orgName}-time' value='{$valueTime}' />\n";
				break;

			case 'sex':
				$aArray = \Model\Protocol::getDictionary('sex');
				$retVal .= self::renderSelect($orgName,$originalValue,$aArray);

				break;

			case 'nzk':

				$aArray = \Model\Protocol::getDictionary('nzk');

				$retVal .= self::renderSelect($orgName,$originalValue,$aArray);

				break;

			case 'division':

				$userDivision = \General\LoggedUser::getInstance()->getUser()->getDataObject()->DivisionID;

				if (empty($userDivision)) {

					$oModel = new \Model\Division();

					$aArray = $oModel->getAll();

					$aDiv = array();

					foreach($aArray as $value) {
						$aDiv[$value['DivisionID']] = $value['Name'];
					}

					$retVal .= self::renderSelect($orgName,$originalValue,$aDiv);

				}else {
					$retVal .= "<input type='text' value='".\General\LoggedUser::getInstance()->getUser()->getDataObject()->DivisionName."' disabled /><input type='hidden' value='{$userDivision}' $name $id />\n";
				}

				break;

			case 'cpr_by':

				$aArray = \Model\Protocol::getDictionary('cpr_by');

				$retVal .= self::renderSelect($orgName,$originalValue,$aArray);

				break;

			case 'nzk_place':

				$aArray = \Model\Protocol::getDictionary('nzk_place');

				$retVal .= self::renderSelect($orgName,$originalValue,$aArray);

				break;

			case 'preliminary':

				$aArray = \Model\Protocol::getDictionary('preliminary');

				$retVal .= self::renderSelect($orgName,$originalValue,$aArray);

				break;

			case 'hipo_technique':

				$aArray = \Model\Protocol::getDictionary('hipo_technique');

				$retVal .= self::renderSelect($orgName,$originalValue,$aArray);

				break;

			case 'rankin':

				$aArray = \Model\Protocol::getDictionary('rankin');

				$retVal .= self::renderSelect($orgName,$originalValue,$aArray);

				break;

			case 'checkbox_date':

				if (!empty($orgValue)) {
					$checked = "checked";
					$dis = '';
					$valData = date('d-m-Y', strtotime($orgValue));
				}
				else {
					$checked = "";
					$dis = 'disabled';
					$valData = '';
				}

				$retVal .= "<input type='checkbox' class='checkbox_date' for='{$orgName}' name='{$orgName}-trigger' value='1' $checked  />";
				$retVal .= "<input type='text' value='{$valData}' {$dis} class='date-picker span2' name='{$orgName}' />\n";

				break;

			case 'comp_bleeding':

				$aArray = \Model\Protocol::getDictionary('comp_bleeding');

				if (!empty($orgValue)) {
					$valData = unserialize($orgValue);
				}else {
					$valData['type'] = null;
					$valData['date'] = date('d-m-Y');
				}

				$retVal .= self::renderSelect($orgName,$valData['type'],$aArray);

				if ($orgValue == 'nie' || empty($orgValue)) {
					$dis = 'disabled';
				}else {
					$dis = '';
				}

				$retVal .= "<input type='text' value='{$valData['date']}' {$dis} class='date-picker span2' name='comp_bleeding-date' />\n";

				break;

			case 'comp_rhythm':

				$aArray = \Model\Protocol::getDictionary('comp_rhythm');

				if (!empty($orgValue)) {
					$valData = unserialize($orgValue);
				}else {
					$valData['type'] = null;
					$valData['date'] = date('d-m-Y');
				}

				$retVal .= self::renderSelect($orgName,$valData['type'],$aArray);

				if ($orgValue == 'nie' || empty($orgValue)) {
					$dis = 'disabled';
				}else {
					$dis = '';
				}

				$retVal .= "<input type='text' value='{$valData['date']}' {$dis} class='date-picker span2' name='comp_rhythm-date' />\n";

				break;

			case "patient_id" :
				$userDivision = \General\LoggedUser::getInstance()->getUser()->getDataObject()->DivisionID;

				if (!empty($userDivision)) {
					$oModel = new Division(array('id'=>$userDivision));
					$retVal .= "<input type='text' class='span1' value='".$oModel->getDataObject()->DivisionNumber."' disabled />";

					if (empty($orgValue)) {
						$oModel = new Protocol();
							
						$value = "value=\"" . $oModel->getNextNumber($userDivision) . "\"";
					}
					$retVal .= "<input type=\"text\" class='span1' size='3' $name $id $value $class $style onkeyup=\"FormValidators.mask(this,0)\" onblur=\"FormValidators.mask(this,0)\" />\n";

				}else {
					$retVal .= "<input type=\"text\" class='span2' size='6' $name $id $value $class $style onkeyup=\"FormValidators.mask(this,0)\" onblur=\"FormValidators.mask(this,0)\" />\n";
				}


				break;

			default:
			case "text" :
				$retVal .= "<input type=\"text\" $tSize $name $id $value $class $style onkeyup=\"FormValidators.mask(this,99)\" onblur=\"FormValidators.mask(this,99)\" />\n";
				break;
		}

		return $retVal;
	}

	/**
	 * Wyświetlenie dialogu o błędzie
	 *
	 * @param string $dialogText
	 * @param string $returnLink
	 * @return string
	 */
	static function displayErrorDialog($dialogText, $returnLink = null)
	{
		$retVal = "<div style=\"text-align: center;\">";
		$retVal .= "<center>";
		$retVal .= "<div class=\"errorBox\" style=\"margin: 40px;\">";
		$retVal .= "<div class=\"errorTitle\">Błąd</div>";
		$retVal .= "<div class=\"errorText\">{$dialogText}</div>";
		if ($returnLink != null) {
			$retVal .= "<div style=\"text-align: center; margin-top: 1em;\">" . self::sStandardButton("Zamknij", $returnLink) . "</div>";
		}
		$retVal .= "</div>";
		$retVal .= "</center>";
		$retVal .= "</div>";

		return $retVal;
	}

	/**
	 * Wyrenderowanie dialogu Tak/Nie
	 * @param string $sTitle Tytuł
	 * @param string $sText Tekst
	 * @param string $sYesLink Zdarzenie onclick na przycisk Tak
	 * @param string $sNoLink Zdarzenie onclick na przycisk Nie
	 * @param string $sClass Klasa dialogu, kompatybilne z klasami alert z Twitter Bootstrap
	 * @return string
	 */
	static public function renderDialog($sTitle, $sText, $sYesLink = '', $sNoLink = '', $sClass='') {
			
		$retVal = "<div class='alert {$sClass}'>";
		$retVal .= "<h1>{$sTitle}</h1>";
		$retVal .= "<p>{$sText}</p>";

		if (!empty($sYesLink)) {
			$retVal .= self::bootstrapButton('{T:Tak}',$sYesLink,'btn-success','icon-ok');
		}
			
		if (!empty($sNoLink)) {
			$retVal .= self::bootstrapButton('{T:Nie}',$sNoLink,'','icon-remove');
		}
			
		$retVal .= "</div>";
			
		return $retVal;
			
	}

	/**
	 * Wyświetlenie dialogu potwierdzającego
	 *
	 * @param string $dialogTitle
	 * @param string $dialogText
	 * @param string $returnLink
	 * @return string
	 */
	static function displayConfirmDialog($dialogTitle, $dialogText, $returnLink = null, $style = "width: 350px; margin-top: 5px;")
	{

		global $t;

		$retVal = "<div class='confirmBox panel' style='" . $style . "' centerable='true'>";
		$retVal .= "<h1>{$dialogTitle}</h1>";
		$retVal .= "<h2>{$dialogText}</h2>";
		if ($returnLink != null) {
			$retVal .= "<div style=\"text-align: center; margin-top: 1em;\">" . self::sStandardButton(Translate::getDefault()->get('continue'), $returnLink) . "</div>";
		}
		$retVal .= "</div>";

		$retVal .= '<script>';
		$retVal .= 'setCenterable()';
		$retVal .= '</script>';

		return $retVal;
	}

	/**
	 * Funckja zwracająca label dla wybranego elementu
	 *
	 * @param  string $name Treść labela
	 * @param  string $class Klasa dla labela
	 * @param  string $for Nazwa elementu dla którego jest label
	 * @return  string HTML zawierający label
	 * @since 2012-06-14
	 * @version 1.0
	 */
	static function renderLabel($name, $class = '', $for)
	{
		return '<label class="' . $class . '" for="' . $for . '">{T:' . $name . '}</label>';
	}

	/**
	 *
	 * Przycisk zgodny z jQuery UI
	 * @param string $text Napis
	 * @param string $onclick Zdarzenie onclick
	 * @param string $icon Nazwa ikony
	 * @param boolean $ajaxParser
	 * @return string
	 * @since 2010-08-07
	 * @deprecated
	 */
	static public function uiButton($text = '', $onclick = null, $icon = null, $ajaxParser = false, $style = null)
	{
		$retVal = '';

		if (empty($text)) {
			$text = '';
		}

		if (!empty($icon)) {
			$icon = ' iconName="' . $icon . '" ';
		}
		else {
			$icon = '';
		}

		if (!empty($onclick)) {
			$onclick = ' onclick="' . $onclick . '" ';
		}
		else {
			$onclick = '';
		}

		if (!empty($ajaxParser)) {
			$ajaxParser = ' ajaxParse="true" ';
		}
		else {
			$ajaxParser = '';
		}

		if (!empty($style)) {
			$style = " style='{$style}' ";
		}
		else {
			$style = '';
		}

		$retVal = "<button {$onclick} {$icon} {$ajaxParser} {$style} title='{$text}'>" . $text . "</button>";

		return $retVal;
	}

	/**
	 *
	 * Render basic button with Twitter Bootstrap
	 * @param string $text
	 * @param string $onclick
	 * @param string $type
	 * @param string $icon
	 */
	static public function bootstrapButton($text = '', $onclick = null, $type = '', $icon = null)
	{
		$retVal = '';

		if (empty($text)) {
			$text = '';
		}

		if (!empty($icon)) {

			if (!empty($type)) {
				$icon .= ' icon-white';
			}

			$icon = '<i class="' . $icon . '"></i>  ';
		}
		else {
			$icon = '';
		}

		if (!empty($onclick)) {
			$onclick = ' onclick="' . str_replace('\\', '\\\\', $onclick) . '" ';
		}
		else {
			$onclick = '';
		}

		if (!empty($type)) {
			$type = ' ' . $type;
		}

		$retVal = " <button class='btn{$type}' {$onclick} title='{$text}'>" . $icon . $text . "</button> ";

		return $retVal;
	}

	/**
	 * Render basic icon with Twitter Bootstrap
	 * @param string $text
	 * @param string $onclick
	 * @param string $type
	 * @param string $icon
	 */
	static public function bootstrapIconButton($text = '', $onclick = null, $type = '', $icon = null, $opt = null)
	{
		$retVal = '';

		if (empty($text)) {
			$text = '';
		}

		if (!empty($icon)) {

			if (!empty($type)) {
				$icon .= ' icon-white';
			}

			$icon = '<i class="' . $icon . '"></i>  ';
		}
		else {
			$icon = '';
		}

		if (!empty($onclick)) {
			$onclick = ' onclick="' . str_replace('\\', '\\\\', $onclick) . '" ';
		}
		else {
			$onclick = '';
		}

		if (!empty($type)) {
			$type = ' ' . $type;
		}

		if(!empty($opt)) {
			foreach($opt as $key => $value) {
				$id = '';
				$class = '';
				switch($key) {
					case 'id':
						$id = $value;
						break;
					case 'class':
						$class = $value;
					default:
						break;
				}
			}
		} else {
			$id = '';
			$class = '';
		}

		$retVal = " <button id='$id' class='btn{$type} $class' {$onclick} title='{$text}'>" . $icon . "</button> ";

		return $retVal;
	}

	/**
	 *
	 * Funckcja generuje dialog z max. 2 przyciskami zgodny z jQuery UI
	 * @param string $title
	 * @param string $text
	 * @param string $onOK
	 * @param string $onCancel
	 * @param string $okText
	 * @param string $cancelText
	 * @since 2010-08-11
	 */
	static public function uiDialog($title, $text, $onOK = null, $onCancel = null, $okText = 'OK', $cancelText = 'Cancel')
	{

		$retVal = '';

		$retVal .= '<div id="dialog-message" title="' . $title . '" ><p>' . $text . '</p></div>';

		$retVal .= '<script type="text/javascript">';
		$retVal .= '$("#dialog-message").dialog({
		modal: true';

		if (!empty($onOK) || !empty($onCancel)) {

			if ($onOK == 'close') {
				$okFunction = '$(this).dialog("close");';
			}
			else {
				$okFunction = str_replace('\\', '\\\\', $onOK);
			}
			if ($onCancel == 'close') {
				$calcelFunction = '$(this).dialog("close");';
			}
			else {
				$calcelFunction = str_replace('\\', '\\\\', $onCancel);
			}

			$retVal .= ',buttons: {';
			$retVal .= $okText . ': function() {
			' . $okFunction . '
		}';
			if (!empty($onCancel)) {
				$retVal .= ',' . $cancelText . ': function() {
				' . $calcelFunction . '
			}';
			}
			$retVal .= '}';
		}

		$retVal .= '});';
		$retVal .= '</script>';

		return $retVal;
	}

	/**
	 * Funkcja renderująca przycisk z funkcją obsługi JS
	 * @param $name - nazwa przycisku
	 * @param $onclick - zdarzenie onclick
	 * @param $style - opcjonalne wartości stylu dla elementu
	 * @param $class - klasa CSS, domyślnie formButton
	 * @return kod HTML
	 */
	static function renderButton($name, $onclick = null, $style = null, $class = 'smallButton')
	{

		if ($style != null) {
			$style = "style=\"" . $style . "\"";
		}
		else {
			$style = "";
		}

		if ($onclick != null) {
			$onclick = "onclick=\"" . $onclick . "\"";
		}
		else {
			$onclick = "";
		}

		if ($class == null) {
			$class = "formButton";
		}

		return "<input $style class=\"$class\" type=\"button\" value=\"$name\" $onclick />";
	}

	/**
	 * @deprecated
	 */
	static public function standardButton($name, $onclick, $class = 'closeButton')
	{
		return self::renderButton($name, $onclick, null, $class);
	}

	/**
	 * @deprecated
	 */
	static function renderSubmitButton($name, $style = null, $class = null)
	{

		if ($style != null) {
			$style = "style=\"" . $style . "\"";
		}
		else {
			$style = "";
		}

		if ($class == null) {
			$class = "formButton";
		}

		return "<input $style class=\"$class\" type=\"submit\" value=\"$name\" />";
	}

	/**
	 * Funkcja renderująca przycisk typu IMG
	 * @param $type - typ przycisku: info/edit/delete/... etc.
	 * @param $onclick - zdarzenie onclick
	 * @param $name - opis przycisku
	 * @param $class - klasa CSS, domyślnie img_link
	 * @return kod HTML
	 */
	static function renderImgButton($type, $onclick, $name, $class = "link", $style = '')
	{

		global $config;

		switch ($type) {
			case "dollar" :
				$imgAddr = 'gfx/dollar.png';
				break;

			case "reload" :
				$imgAddr = "gfx/ui_icons/small/reload.png";
				break;

			case "sell" :
				$imgAddr = "gfx/ui_icons/small/sell.png";
				break;

			case "buy" :
				$imgAddr = "gfx/ui_icons/small/buy.png";
				break;

			case "repair" :
				$imgAddr = "gfx/ui_icons/small/repair.png";
				break;

			case "info" :
				$imgAddr = "gfx/ui_icons/small/info.png";
				break;

			case "add" :
				$imgAddr = 'gfx/ui_icons/small/add.png';
				break;

			case "remove" :
				$imgAddr = 'gfx/ui_icons/small/remove.png';
				break;

			case "gather" :
				$imgAddr = 'gfx/ui_icons/small/gather.png';
				break;

			case "edit" :
				$imgAddr = "gfx/edit.gif";
				break;

			case 'attack' :
				$imgAddr = 'gfx/ui_icons/big/attack.png';
				break;

			case 'examine' :
				$imgAddr = 'gfx/ui_icons/big/search.png';
				break;

			case 'delete' :
				$imgAddr = 'gfx/ui_icons/small/trash.png';
				break;

			case 'deleteall' :
				$imgAddr = 'gfx/ui_icons/small/trash_all.png';
				break;

			case "all" :
				$imgAddr = "gfx/all.png";
				break;

			case "none" :
				$imgAddr = "gfx/none.png";
				break;

			case "yes" :
				$imgAddr = "gfx/ui_icons/small/yes.png";
				break;

			case 'no' :
				$imgAddr = "gfx/ui_icons/small/no.png";
				break;

			case 'up' :
				$imgAddr = 'gfx/ui_icons/small/up.png';
				break;

			case 'down' :
				$imgAddr = 'gfx/ui_icons/small/down.png';
				break;

			case 'left' :
				$imgAddr = 'gfx/left.gif';
				break;

			case 'leftFar' :
				$imgAddr = 'gfx/left_far.gif';
				break;

			case 'right' :
				$imgAddr = 'gfx/right.gif';
				break;

			case "rightFar" :
				$imgAddr = "gfx/right_far.gif";
				break;

			case "popupOpen" :
				$imgAddr = "gfx/strzala3.gif";
				break;

			case "message" :
				$imgAddr = "gfx/message.png";
				break;

			case "warningA" :
				$imgAddr = "gfx/warning1.png";
				break;

			case "warningB" :
				$imgAddr = "gfx/warning2.png";
				break;

			case "follow" :
				$imgAddr = "gfx/follow.png";
				break;

			default:
				$imgAddr = 'gfx/' . $type . '.png';
				break;
		}

		$imgAddr = $config['general']['cdn'] . $imgAddr;

		return "<img src='$imgAddr' class='$class' onclick=\"$onclick\" title='$name' {$style} />";
	}

	/**
	 * Funkcja tłumacząca nazwy miesiąca
	 * @param $date - data Unixowa
	 * @return string
	 */
	static public function translateMonth($date){
		$iMonth = date("n", $date);
		switch($iMonth){
			case 1:
				$sMonth = 'Stycznia';
				break;
			case 2:
				$sMonth = 'Lutego';
				break;
			case 3:
				$sMonth = 'Marca';
				break;
			case 4:
				$sMonth = 'Kwietnia';
				break;
			case 5:
				$sMonth = 'Maja';
				break;
			case 6:
				$sMonth = 'Czerwca';
				break;
			case 7:
				$sMonth = 'Lipca';
				break;
			case 8:
				$sMonth = 'Sierpnia';
				break;
			case 9:
				$sMonth = 'Września';
				break;
			case 10:
				$sMonth = 'Października';
				break;
			case 11:
				$sMonth = 'Listopada';
				break;
			case 12:
				$sMonth = 'Grudnia';
				break;
		}
		return $sMonth;
	}

}