<?php
/**
 * kiala_BlockKialaModeConfigurationAction
 * @package modules.kiala.lib.blocks
 */
class kiala_BlockKialaModeConfigurationAction extends shipping_BlockRelayModeConfigurationAction
{
	
	protected function getRelayModeService()
	{
		return kiala_KialamodeService::getInstance();
	}
	
}