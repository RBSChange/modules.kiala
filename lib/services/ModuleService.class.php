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
	
}