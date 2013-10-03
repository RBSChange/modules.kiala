<?php
/**
 * kiala_BlockKialaModeConfigurationAction
 * @package modules.kiala.lib.blocks
 */
class kiala_BlockKialaModeConfigurationAction extends shipping_BlockRelayModeConfigurationAction
{
	/**
	 * @return string|NULL
	 */
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
	 * @return shipping_Relay[]
	 */
	protected function buildRelayList()
	{
		$webserviceUrl = Framework::getConfigurationValue('modules/kiala/webserviceUrl');
		$kpOnMap = Framework::getConfigurationValue('modules/kiala/maxKialaPointsOnMap');
		$mode = $this->param[self::P_MODE];
		
		$relays = array();
		$country = zone_CountryService::getInstance()->getByCode($this->param['countryCode']);
		$dspid = $mode->getDspidToCountry($country);
		$url = $webserviceUrl . '?dspid=' . $dspid->getDspidCode(). '&countryid=' . $this->param[self::P_COUNTRY_CODE] . '&zip=' . $this->param[self::P_ZIP_CODE] . '&max-result='.$kpOnMap;

		if (isset($this->param[self::P_LANG]))
		{
			$url .= '&language=' . $this->param[self::P_LANG];
		}

		$httpClient = HTTPClientService::getInstance()->getNewHTTPClient();
		$xml = $httpClient->get($url);
		
		try
		{
			$doc = f_util_DOMUtils::fromString($xml);
			
			$kplist = $doc->documentElement;
			$kpNodes = $kplist->childNodes;
			
			for ($i = 0; $i < $kpNodes->length; $i++)
			{
				$relay = kiala_KialamodeService::getInstance()->getRelayFromXml($kpNodes->item($i));
				$relay->setCountryCode($this->param['countryCode']);
				$relay->setIconUrl('/media/frontoffice/marker-kiala.png');
				$relays[] = $relay;
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		
		return $relays;
	}

	protected function getDefaultMapZoom()
	{
		return Framework::getConfigurationValue('modules/kiala/defaultMapZoom');
	}
}