<?php
/**
 * kiala_OpenKialaHelpAction
 * @package modules.kiala.actions
 */
class kiala_OpenKialaHelpAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
        $context->getController()->redirectToUrl(kiala_ModuleService::getKialaHelpPage());
        return View::NONE;
	}
}