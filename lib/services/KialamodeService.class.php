<?php
/**
 * kiala_KialamodeService
 * @package modules.kiala
 */
class kiala_KialamodeService extends shipping_RelayModeService
{
	/**
	 * @var kiala_KialamodeService
	 */
	private static $instance;
	
	/**
	 * @return kiala_KialamodeService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
	/**
	 * @return kiala_persistentdocument_kialamode
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_kiala/kialamode');
	}
	
	/**
	 * Create a query based on 'modules_kiala/kialamode' model.
	 * Return document that are instance of modules_kiala/kialamode,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_kiala/kialamode');
	}
	
	/**
	 * Create a query based on 'modules_kiala/kialamode' model.
	 * Only documents that are strictly instance of modules_kiala/kialamode
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_kiala/kialamode', false);
	}
	
	/**
	 * @param kiala_persistentdocument_kialamode $mode
	 * @param order_CartInfo $cart
	 * @return string[]|false
	 */
	public function getConfigurationBlockForCart($mode, $cart)
	{
		return array('kiala', 'KialaModeConfiguration');
	}
	
	protected function getDetailExpeditionPageTagName()
	{
		return 'contextual_website_website_modules_kiala_kialaexpedition';
	}
	
	/**
	 *
	 * @param kiala_persistentdocument_kialamode $mode
	 * @param customer_persistentdocument_address $shippingAddress
	 * @param array $extraUrlParams
	 */
	public function getFrameUrl($mode, $shippingAddress, $extraUrlParams = array())
	{
		$baseUrl = Framework::getConfigurationValue('modules/kiala/frameBaseUrl');
		$thumbnails = Framework::getConfigurationValue('modules/kiala/disableThumbnails', 'false');
		$map = Framework::getConfigurationValue('modules/kiala/disableMap', 'false');
		$mapWidth = Framework::getConfigurationValue('modules/kiala/mapWidth', '500');
		$mapHeight = Framework::getConfigurationValue('modules/kiala/mapHeight', '400');
		$align = Framework::getConfigurationValue('modules/kiala/alignMap', 'right');
		$header = Framework::getConfigurationValue('modules/kiala/disableHeader', 'false');
		
		$modeId = $mode->getId();
		$dspid = $mode->getDspid();
		$zipCode = $shippingAddress->getZipCode();
		$country = $shippingAddress->getCountry();
		if ($country instanceof zone_persistentdocument_country)
		{
			$countryCode = $country->getCode();
		}
		else
		{
			$countryCode = 'FR'; // Default
		}
		
		$backUrl = LinkHelper::getActionUrl('kiala', 'SelectRelay');
		$backUrl .= '?modeId=' . $modeId . '&';
		$backUrl = urlencode($backUrl);
		
		$frameCssUrl = LinkHelper::getActionUrl('kiala', 'GetFrameStylesheet');
		$frameCssUrl = urlencode($frameCssUrl);
		
		$frameUrl = $baseUrl . '?dspid=' . $dspid . '&countryid=' . $countryCode . '&zip=' . $zipCode . '&bckUrl=' . $backUrl . '&css=' . css . '&align=' . $align . '&target=_parent';
		
		if ($thumbnails == 'true')
		{
			$frameUrl .= '&thumbnails=off';
		}
		
		if ($map == 'true')
		{
			$frameUrl .= '&map=off';
		}
		else
		{
			$frameUrl .= '&map=' . $mapWidth . 'x' . $mapHeight;
		}
		
		if ($header == 'true')
		{
			$frameUrl .= '&header=off';
		}
		
		return $frameUrl;
	}

}