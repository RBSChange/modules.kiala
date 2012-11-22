<?php
/**
 * kiala_SelectRelayAction
 * @package modules.kiala.actions
 */
class kiala_SelectRelayAction extends shipping_SelectRelayAction
{
	/**
	 * @param integer $modeId
	 * @return kiala_persistentdocument_kialamode
	 */
	protected function getMode($modeId)
	{
		return kiala_persistentdocument_kialamode::getInstanceById($modeId);
	}
	
	/**
	 * @return string
	 */
	protected function getRelayCodeParamName()
	{
		return 'shortkpid';
	}
	
	/**
	 * @return string
	 */
	protected function getRelayCountryCodeParamName()
	{
		return '';
	}
	
	/**
	 * @return string
	 */
	protected function getRelayNameParamName()
	{
		return 'kpname';
	}
	
	/**
	 * @return string
	 */
	protected function getRelayAddress1ParamName()
	{
		return 'street';
	}
	
	/**
	 * @return string
	 */
	protected function getRelayAddress2ParamName()
	{
		return '';
	}
	
	/**
	 * @return string
	 */
	protected function getRelayAddress3ParamName()
	{
		return '';
	}
	
	/**
	 * @return string
	 */
	protected function getRelayZipCodeParamName()
	{
		return 'zip';
	}
	
	/**
	 * @return string
	 */
	protected function getRelayCityParamName()
	{
		return 'city';
	}
}