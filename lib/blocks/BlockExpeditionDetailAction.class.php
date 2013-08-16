<?php
/**
 * kiala_BlockExpeditionDetailAction
 * @package modules.kiala.lib.blocks
 */
class kiala_BlockExpeditionDetailAction extends shipping_BlockExpeditionDetailAction
{
	/**
	 * Initialize $this->param
	 */
	protected function init()
	{
		$shippingAdress = $this->expedition->getAddress();
		$shippingMode = $this->expedition->getShippingMode();
		
		$this->param['relayCode'] = $shippingAdress->getLabel();
		$this->param['countryCode'] = $shippingAdress->getCountryCode();
		$this->param['dspId'] = $shippingMode->getDspidToCountry($shippingAdress->getCountry())->getDspidCode();
		$this->param['groupName'] = $shippingMode->getGroupName();
		$this->param['lang'] = strtoupper($this->getContext()->getLang());
		
		$this->param['webserviceUrl'] = Framework::getConfigurationValue('modules/kiala/webserviceUrl');
		$this->param['trackingUrl'] = Framework::getConfigurationValue('modules/kiala/trackingUrl');
	}
	
	/**
	 * @return shipping_Relay
	 */
	protected function getRelayDetail()
	{
		$relay = null;
		
		$url = $this->param['webserviceUrl'] . '?dspid=' . $this->param['dspId'] . '&countryid=' . $this->param['countryCode'] . '&shortID=' . $this->param['relayCode'] . '&language=' . $this->param['lang'];
		
		$httpClient = HTTPClientService::getInstance()->getNewHTTPClient();
		$xml = $httpClient->get($url);
		
		try
		{
			$doc = f_util_DOMUtils::fromString($xml);
			
			$kpNode = $doc->documentElement->firstChild;
			
			if ($kpNode != null && $kpNode->nodeName == 'kp')
			{
				$relay = kiala_KialamodeService::getInstance()->getRelayFromXml($kpNode);
				$relay->setCountryCode($this->param['countryCode']);
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		
		return $relay;
	}
	
	/**
	 * @param string $trackingNumber
	 * @return array
	 */
	protected function getTrackingDetail($trackingNumber)
	{
		$result = array();
		
		if ($trackingNumber != null && $trackingNumber != '')
		{
			$ls = LocaleService::getInstance();
			
			$url = $this->param['trackingUrl'] . '?group-name=' . $this->param['groupName'] . '&country=' . $this->param['countryCode'] . '&language=' . $this->param['lang'] . '&criteria-type=dspparcelid&criteria-value=' . $trackingNumber;
			
			$httpClient = HTTPClientService::getInstance()->getNewHTTPClient();
			$xml = $httpClient->get($url);
			
			try
			{
				$doc = f_util_DOMUtils::fromString($xml);
				
				$eventList = $doc->find('//parcelEvent');
				
				$result['steps'] = array();
				
				foreach ($eventList as $event)
				{
					$step = array();
					
					foreach ($event->getElementsByTagName('*') as $item)
					{
						/* @var $item DOMNode */
						
						switch ($item->localName)
						{
							case 'place' :
								$place = $item->attributes->getNamedItem('type')->nodeValue;
								$step['place'] = $ls->transFO('m.kiala.general.' . $place, array('ucf'));
								break;
							
							case 'eventDateTime' :
								$dateEvent = strtotime($item->nodeValue);
								$step['date'] = date_Formatter::format($dateEvent, 'd/m/Y');
								$step['hour'] = date_Formatter::format($dateEvent, 'H:i');
								break;
							
							case 'description' :
								$step['label'] = $item->nodeValue;
								break;
						}
					}
					
					$result['steps'][$step['date'] . count($result['steps'])] = $step;
				}
				ksort($result['steps']);
			}
			catch (Exception $e)
			{
				Framework::exception($e);
			}
		}
		
		return $result;
	}
}