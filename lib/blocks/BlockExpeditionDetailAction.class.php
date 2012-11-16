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
		$relay = null;
		
		$url = $this->param['webserviceUrl'] . '?dspid=' . $this->param['dspId'] . '&countryid=' . $this->param['countryCode'] . '&shortID=' . $this->param['relayCode'] . '&language=' . $this->param['lang'];
		
		$httpClient = HTTPClientService::getInstance()->getNewHTTPClient();
		$xml = $httpClient->get($url);
		
		$doc = f_util_DOMUtils::fromString($xml);
		
		$kpNode = $doc->documentElement->firstChild;
		
		if ($kpNode != null && $kpNode->nodeName == 'kp')
		{
			$relay = kiala_KialamodeService::getInstance()->getRelayFromXml($kpNode);
			$relay->setCountryCode($this->param['countryCode']);
		}
		
		return $relay;
	}
	
	protected function getTrackingDetail($trackingNumber)
	{
		$url = $this->param['trackingUrl'] . '?dspid=' . $this->param['dspId'] . '&countryid=' . $this->param['countryCode'] . '&language=' . $this->param['lang'] . '&dspparcelid=' . $trackingNumber;
		
		$result['trackingUrl'] = $url;
		
		return $result;
	}

}