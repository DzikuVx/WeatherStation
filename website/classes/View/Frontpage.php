<?php

namespace View;

use General\Formater;

use General\Templater;

use Database\Factory;

class Frontpage extends Base {

	public function mainpage()
	{
		$oTemplate = new Templater('mainpage.html');

		$oModel = new \Model\Readout();
		
		$oCurrent = $oModel->getCurrent();
		$oTemplate->add($oCurrent);

		$aHistory = $oModel->getHistory();

		$sTable = '';
		foreach ($aHistory as $iIndex => $oReadout) {
			
			$sTable .= '<tr>';
			$sTable .= '<td>'.($iIndex+1).'</td>';
			$sTable .= '<td>'.Formater::formatDateTime($oReadout['Date']).'</td>';
			$sTable .= '<td>'.$oReadout['Temperature'].'&deg;C</td>';
			$sTable .= '<td>'.$oReadout['Humidity'].'%</td>';
			
			$sTable .= '</tr>';
			
		}
		$oTemplate->add('Table', $sTable);
		
		$o1DayAvg = $oModel->getAverage(1);
		$oTemplate->add('1dTempAvg', Formater::formatFloat($o1DayAvg->Temperature, 2));
		$oTemplate->add('1dHumidityAvg', Formater::formatFloat($o1DayAvg->Humidity, 2));
		
		$o7DayAvg = $oModel->getAverage(7);
		$oTemplate->add('7dTempAvg', Formater::formatFloat($o7DayAvg->Temperature, 2));
		$oTemplate->add('7dHumidityAvg', Formater::formatFloat($o7DayAvg->Humidity, 2));
		
		return (string) $oTemplate;
	}

}