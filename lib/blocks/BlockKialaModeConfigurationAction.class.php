<?php
/**
 * kiala_BlockKialaModeConfigurationAction
 * @package modules.kiala.lib.blocks
 */
class kiala_BlockKialaModeConfigurationAction extends shipping_BlockRelayModeConfigurationAction
{
	
	protected function buildFrameUrl()
	{
		$useFrame = Framework::getConfigurationValue('modules/kiala/useFrame');
		if ($useFrame === 'true')
		{
			return kiala_KialamodeService::getInstance()->getFrameUrl($this->param['mode'], $this->param['shippingAddress']);
		}
		return null;
	}
	
	/**
	 * Return the list of shipping_Relay
	 * @return array<shipping_Relay>
	 */
	protected function buildRelayList()
	{
		$webserviceUrl = Framework::getConfigurationValue('modules/kiala/webserviceUrl');
		$mode = $this->param['mode'];
		
		$relays = array();
		
		$url = $webserviceUrl . '?dspid=' . $mode->getDspid() . '&countryid=' . $this->param['countryCode'] . '&zip=' . $this->param['zipcode'] . '&language=' . $this->param['lang'];
		
		$httpClient = HTTPClientService::getInstance()->getNewHTTPClient();
		$xml = $httpClient->get($url);
		
		$doc = f_util_DOMUtils::fromString($xml);
		
		$kplist = $doc->documentElement;
		$kpNodes = $kplist->childNodes;
		
		for ($i = 0; $i < $kpNodes->length; $i++)
		{
			$relay = kiala_KialamodeService::getInstance()->getRelayFromXml($kpNodes->item($i));
			$relay->setCountryCode($this->param['countryCode']);
			$relays[] = $relay;
		}
		
		return $relays;
	}

}