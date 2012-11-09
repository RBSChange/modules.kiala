<?php
/**
 * kiala_GetFrameStylesheetAction
 * @package modules.kiala.actions
 */
class kiala_GetFrameStylesheetAction extends shipping_GetFrameStylesheetAction
{
	protected function getStylesheetName()
	{
		return 'modules.kiala.frame';
	}
}