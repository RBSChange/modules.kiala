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
	
	/**
	 * @return string
	 */
	protected function getDetailExpeditionPageTagName()
	{
		return 'contextual_website_website_modules_kiala_kialaexpedition';
	}
	
	/**
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
	
	/**
	 * @param DOMNode $item
	 * @return shipping_Relay
	 */
	public function getRelayFromXml($item)
	{
		$relay = new shipping_Relay();
		
		$attributes = $item->attributes;
		$relay->setRef($attributes->getNamedItem('shortId')->nodeValue);
		
		$childList = $item->childNodes;
		
		for ($i = 0; $i < $childList->length; $i++)
		{
			$child = $childList->item($i);
			$nodeName = strtolower($child->nodeName);
			switch ($nodeName)
			{
				case 'name' :
					$relay->setName($child->nodeValue);
					break;
				case 'address' :
					$addressChildList = $child->childNodes;
					for ($j = 0; $j < $addressChildList->length; $j++)
					{
						$addressChild = $addressChildList->item($j);
						$addressChildNodeName = strtolower($addressChild->nodeName);
						switch ($addressChildNodeName)
						{
							case 'street' :
								$relay->setAddressLine1($addressChild->nodeValue);
								break;
							case 'zip' :
								$relay->setZipCode($addressChild->nodeValue);
								break;
							case 'city' :
								$relay->setCity($addressChild->nodeValue);
								break;
							case 'locationhint' :
								$value = trim($addressChild->nodeValue);
								if ($value != '')
								{
									$relay->setLocationHint($value);
								}
								break;
						}
					}
					
					break;
				
				case 'openinghours' :
					
					$hoursNodeList = $child->childNodes;
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
					
					$relay->setOpeningHours($openingHours);
					break;
				
				case 'picture' :
					$attributes = $child->attributes;
					$hrefAttributes = $attributes->getNamedItem('href');
					if ($hrefAttributes != null)
					{
						$relay->setPictureUrl($hrefAttributes->nodeValue);
					}
					
					break;
				
				case 'coordinate' :
					$relay->setLatitude($child->firstChild->nodeValue);
					$relay->setLongitude($child->lastChild->nodeValue);
					break;
			}
		}
		return $relay;
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
			$result = $ls->transFO('m.shipping.general.opening-hours', array('ucf'), array(
				'hour1' => $morningTimeSpanStart->nodeValue, 'hour2' => $morningTimeSpanEnd->nodeValue));
			
			$afternoonTimeSpan = $timeSpanList->item(1);
			if ($afternoonTimeSpan != null)
			{
				$afternoonTimeSpanStart = $afternoonTimeSpan->firstChild;
				$afternoonTimeSpanEnd = $afternoonTimeSpan->lastChild;
				$result .= ' ';
				$result .= $ls->transFO('m.shipping.general.and');
				$result .= ' ';
				$result .= $ls->transFO('m.shipping.general.opening-hours', array(), array(
					'hour1' => $afternoonTimeSpanStart->nodeValue, 'hour2' => $afternoonTimeSpanEnd->nodeValue));
			}
		}
		return $result;
	}

    /**
     * @param bool $onlyPublished
     * @return Integer
     */
    public function getKialaModeCount($onlyPublished=false)
    {
        $query = $this->createQuery();
        if ($onlyPublished)
        {
            $query->add(Restrictions::published());
        }
        $query->add(Restrictions::ne('publicationstatus', 'FILED'));
        $query->setProjection(Projections::rowCount());
        $result = $query->find();
        return $result[0]['rowcount'];
    }

    /**
     * @param kiala_persistentdocument_kialamode $document
     * @return bool
     */
    public function isPublishable($document)
    {
        $kdsi = kiala_KialadspidService::getInstance();
        if ($kdsi->getDspidCountWithModeId($document->getId()) > 0)
        {
            return parent::isPublishable($document);
        }

        return false;
    }

    /**
     * @param shipping_persistentdocument_mode $mode
     * @param integer $countryId
     * @return boolean
     */
    public function isValidForCountryId($mode, $countryId)
    {
        $kdsi = kiala_KialadspidService::getInstance();
        if ($kdsi->getDspidWithModeIdAndCountry($mode->getId(),
                    zone_persistentdocument_country::getInstanceById($countryId)))
        {
            return parent::isValidForCountryId($mode, $countryId);
        }
        return false;
    }

	/**
	 * @param catalog_persistentdocument_product|catalog_persistentdocument_declinedproduct $product
	 * @return bool
	 */
	public function canShipProductWithMode($product, $mode)
	{
		if (method_exists($product, 'getShippingWeight'))
		{
			if (($mode->getIsWeightRequired() && $product->getShippingWeight() !== null)
				|| !$mode->getIsWeightRequired())
			{
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * Returns the name of the CSV fields in the expected order
	 * @return array
	 */
	public function getCsvDefinition()
	{
		$fields = array();
		// <!-- DSP identification -->
		// "partnerId" = code DSPID/>
		$fields[] = 'partnerId';
		//   <!-- Parcel information -->
		// "partnerBarcode" type="string"/>
		$fields[] = 'partnerBarcode';
		// "parcelNumber" type="string"/>
		$fields[] = 'parcelNumber';
		// "orderNumber" type="string"/>
		$fields[] = 'orderNumber';
		// "orderDate" type="datetime" datepattern="yyyyMMdd"/>
		$fields[] = 'orderDate';

		// "invoiceNumber" type="string"/>
		$fields[] = 'invoiceNumber';
		// "invoiceDate" type="datetime" datepattern="yyyyMMdd"/>
		$fields[] = 'invoiceDate';
		// "shipmentNumber" type="string"/>
		$fields[] = 'shipmentNumber';
		// "CODAmount" type="float" numericpattern="#.##"/> CODAMount = Cash On Delivery Amount
		$fields[] = 'CODAmount';
		// "commercialValue" type="float" numericpattern="#.##"/>
		$fields[] = 'commercialValue';
		// "parcelWeight" type="float" numericpattern="#.###"/>
		$fields[] = 'parcelWeight';
		// "parcelVolume" type="float" numericpattern="#.###"/>
		$fields[] = 'parcelVolume';
		// "parcelDescription" type="string"/>
		$fields[] = 'parcelDescription';

		//  <!-- Information on the recipient of the parcel (destination address etc.) -->
		// "customerId" type="string"/>
		$fields[] = 'customerId';
		// "customerName" type="string"/>
		$fields[] = 'customerName';
		// "customerFirstName" type="string"/>
		$fields[] = 'customerFirstName';
		// "customerTitle" type="string"/>
		$fields[] = 'customerTitle';
		// "customerStreet" type="string"/>
		$fields[] = 'customerStreet';
		// "customerStreetNumber" type="string"/>
		$fields[] = 'customerStreetNumber';
		// "customerExtraAddressLine" type="string"/>
		$fields[] = 'customerExtraAddressLine';

		// "customerZip" type="string"/>
		$fields[] = 'customerZip';
		// "customerCity" type="string"/>
		$fields[] = 'customerCity';
		// "customerLocality" type="string"/>
		$fields[] = 'customerLocality';
		// "customerLanguage" type="string"/>
		$fields[] = 'customerLanguage';
		// "customerPhone1" type="string"/>
		$fields[] = 'customerPhone1';
		//$customer->getDefaultAddress()->getPhone();
		// "customerPhone2" type="string"/>
		$fields[] = 'customerPhone2';
		// "customerPhone3" type="string"/>
		$fields[] = 'customerPhone3';
		// "customerEmail1" type="string"/>
		$fields[] = 'customerEmail1';
		// "customerEmail2" type="string"/>
		$fields[] = 'customerEmail2';
		// "customerEmail3" type="string"/>
		$fields[] = 'customerEmail3';

		//  <!-- Parcel handling information for Kiala -->
		// "positiveNotificationRequested" type="string"/>
		$fields[] = 'positiveNotificationRequested';
		// "kialaPoint" type="string"/>
		$fields[] = 'kialaPoint';
		// "backupKialaPoint" type="string"/>
		$fields[] = 'backupKialaPoint';

		return $fields;
	}
}