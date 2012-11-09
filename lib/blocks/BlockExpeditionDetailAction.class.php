<?php
/**
 * kiala_BlockExpeditionDetailAction
 * @package modules.kiala.lib.blocks
 */
class kiala_BlockExpeditionDetailAction extends shipping_BlockExpeditionDetailAction
{
	
	protected function init()
	{
		$shippingAdress = $this->expedition->getAddress();
		$shippingMode = $this->expedition->getShippingMode();
		
		$this->param['relayCode'] = $shippingAdress->getLabel();
		$this->param['countryCode'] = $shippingAdress->getCountryCode();
		$this->param['dspId'] = $shippingMode->getDspid();
		$this->param['lang'] = strtoupper($this->getContext()->getLang());
		
		$this->param['webserviceUrl'] = Framework::getConfigurationValue('modules/kiala/webserviceUrl');
		$this->param['trackingUrl'] = Framework::getConfigurationValue('modules/kiala/trackingUrl');
	
	}
	
	protected function getRelayDetail()
	{
		$result = array();
		$result['openingHours'] = '';
		$result['planUrl'] = null;
		$result['pictureUrl'] = null;
		$result['coordinate'] = null;
		$result['locationHint'] = null;
		
		$url = $this->param['webserviceUrl'] . '?dspid=' . $this->param['dspId'] . '&countryid=' . $this->param['countryCode'] . '&shortID=' . $this->param['relayCode'] . '&language=' . $this->param['lang'];
		
		$httpClient = HTTPClientService::getInstance()->getNewHTTPClient();
		$xml = $httpClient->get($url);
		
		$doc = f_util_DOMUtils::fromString($xml);
		
		$kpNode = $doc->documentElement->firstChild;
		$subKpNodeList = $kpNode->childNodes;
		
		for ($i = 0; $i < $subKpNodeList->length; $i++)
		{
			$subKpNode = $subKpNodeList->item($i);
			if ($subKpNode->nodeName == 'address')
			{
				$addressNodeList = $subKpNode->childNodes;
				
				for ($j = 0; $j < $addressNodeList->length; $j++)
				{
					if ($addressNodeList->item($j)->nodeName == 'locationHint')
					{
						$result['locationHint'] = $addressNodeList->item($j)->nodeValue;
					}
				}
			}
			if ($subKpNode->nodeName == 'openingHours')
			{
				$hoursNodeList = $subKpNode->childNodes;
				$openingHours = array();
				
				// Monday
				$openingHours[] = $this->extractOpeningHour($hoursNodeList->item(0));
				
				// Tuesday
				$openingHours[] = $this->extractOpeningHour($hoursNodeList->item(1));
				
				// Wednesday
				$openingHours[] = $this->extractOpeningHour($hoursNodeList->item(2));
				
				// Thursday
				$openingHours[] = $this->extractOpeningHour($hoursNodeList->item(3));
				
				// Friday
				$openingHours[] = $this->extractOpeningHour($hoursNodeList->item(4));
				
				// Saturday
				$openingHours[] = $this->extractOpeningHour($hoursNodeList->item(5));
				
				// Sunday
				$openingHours[] = $this->extractOpeningHour($hoursNodeList->item(6));
				
				$result['openingHours'] = $openingHours;
			}
			if ($subKpNode->nodeName == 'picture')
			{
				$result['pictureUrl'] = $subKpNode->attributes->getNamedItem('href')->nodeValue;
			}
			if ($subKpNode->nodeName == 'coordinate')
			{
				$coordinate = array();
				
				$coordinate['latitude'] = $subKpNode->firstChild->nodeValue;
				$coordinate['longitude'] = $subKpNode->lastChild->nodeValue;
				
				$result['coordinate'] = $coordinate;
			}
		}
		
		return $result;
	}
	
	protected function getTrackingDetail($trackingNumber)
	{
		$url = $this->param['trackingUrl'] . '?dspid=' . $this->param['dspId'] . '&countryid=' . $this->param['countryCode'] . '&language=' . $this->param['lang'] . '&dspparcelid=' . $trackingNumber;
		
		$result['trackingUrl'] = $url;
		
		return $result;
	}
	
	/**
	 * Extract opening hours from raw hours data
	 * @param DOMNode hoursNodes
	 * @return string
	 */
	protected function extractOpeningHour($hoursNode)
	{
		$ls = LocaleService::getInstance();
		$result = '';
		if (!$hoursNode->hasChildNodes())
		{
			$result = $ls->transFO('m.shipping.general.closed');
		}
		else
		{
			$timeSpanList = $hoursNode->childNodes;
			$morningTimeSpan = $timeSpanList->item(0);
			$morningTimeSpanStart = $morningTimeSpan->firstChild;
			$morningTimeSpanEnd = $morningTimeSpan->lastChild;
			$result = $ls->transFO('m.shipping.general.opening-hours', array('ucf'), array('hour1' => $morningTimeSpanStart->nodeValue, 
				'hour2' => $morningTimeSpanEnd->nodeValue));
			
			$afternoonTimeSpan = $timeSpanList->item(1);
			if ($afternoonTimeSpan != null)
			{
				$afternoonTimeSpanStart = $afternoonTimeSpan->firstChild;
				$afternoonTimeSpanEnd = $afternoonTimeSpan->lastChild;
				$result .= ' ';
				$result .= $ls->transFO('m.shipping.general.and');
				$result .= ' ';
				$result .= $ls->transFO('m.shipping.general.opening-hours', array(), array('hour1' => $afternoonTimeSpanStart->nodeValue, 
					'hour2' => $afternoonTimeSpanEnd->nodeValue));
			}
		
		}
		
		return $result;
	}

}