<?php
/**
 * @package modules.kiala.lib.services
 */
class kiala_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var kiala_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return kiala_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

    /**
     * @return zone_persistentdocument_country[]
     */
    public static function getOperatedCountries()
    {
        $countriesCode = array();

        foreach (explode(',', Framework::getConfigurationValue('modules/kiala/operatedCountries')) as $isoCode)
        {
            $countriesCode[] = f_util_StringUtils::toUpper(trim($isoCode));
        }

        $query = zone_CountryService::getInstance()->createQuery();
        $query->add(Restrictions::in('code', $countriesCode));
        $query->addOrder(Order::asc('label'));
        $query->add(Restrictions::published());

        return $query->find();
    }

    /**
     * Get help page provided by Kiala
     * @return String
     */
    public static function getKialaHelpPage()
    {
        return Framework::getConfigurationValue('modules/kiala/kialaHelpPage');
    }
}