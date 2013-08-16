<?php
/**
 * kiala_IsFirstModeAction
 * @package modules.kiala.actions
 */
class kiala_IsFirstModeAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
        $kms = kiala_KialamodeService::getInstance();
        $data = array();
        $data['isFirstMode'] = false;
        if ($kms->getKialaModeCount() == 0)
        {
            $data['isFirstMode'] = true;
        }
        return $this->sendJSON($data);
	}
}