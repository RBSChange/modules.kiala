<?php
/**
 * kiala_SelectRelayAction
 * @package modules.kiala.actions
 */
class kiala_SelectRelayAction extends shipping_SelectRelayAction
{
	
	protected function getMode($modeId)
	{
		return kiala_persistentdocument_kialamode::getInstanceById($modeId);
	}
	
	protected function getRelayCodeParamName()
	{
		return 'shortkpid';
	}
	
	protected function getRelayCountryCodeParamName()
	{
		return '';
	}
	
	protected function getRelayNameParamName()
	{
		return 'kpname';
	}
	
	protected function getRelayAddress1ParamName()
	{
		return 'street';
	}
	
	protected function getRelayAddress2ParamName()
	{
		return '';
	}
	
	protected function getRelayAddress3ParamName()
	{
		return '';
	}
	
	protected function getRelayZipCodeParamName()
	{
		return 'zip';
	}
	
	protected function getRelayCityParamName()
	{
		return 'city';
	}

}