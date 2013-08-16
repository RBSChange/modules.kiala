<?php
/**
 * kiala_ListOperatedcountriesService
 * @package modules.kiala.lib.services
 */
class kiala_ListOperatedcountriesService extends BaseService implements list_ListItemsService
{
	/**
	 * @var kiala_ListOperatedcountriesService
	 */
	private static $instance;

	/**
	 * @return kiala_ListOperatedcountriesService
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
	 * @see list_persistentdocument_dynamiclist::getItems()
	 * @return list_Item[]
	 */
	public function getItems()
	{
        $items = array();
        foreach (kiala_ModuleService::getOperatedCountries() as $country)
        {
            /* @var $country zone_persistentdocument_country */
            $items[] = new list_Item(
                $country->getLabel() . ' ('. $country->getCode() .')',
                $country->getId()
            );
        }
        return $items;
	}

	/**
	 * @var Array
	 */
	private $parameters = array();
	
	/**
	 * @see list_persistentdocument_dynamiclist::getListService()
	 * @param array $parameters
	 */
	public function setParameters($parameters)
	{
		$this->parameters = $parameters;
	}
	
	/**
	 * @see list_persistentdocument_dynamiclist::getItemByValue()
	 * @param string $value;
	 * @return list_Item
	 */
//	public function getItemByValue($value)
//	{
//	}
}