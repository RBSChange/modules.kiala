<?php
/**
 * @package modules.kiala.setup
 */
class kiala_Setup extends object_InitDataSetup
{
	public function install()
	{
        try
        {
            $scriptReader = import_ScriptReader::getInstance();
            $scriptReader->executeModuleScript('kiala', 'lists.xml');
        }
        catch (Exception $e)
        {
            echo "ERROR: " . $e->getMessage() . "\n";
            Framework::exception($e);
        }

		$mbs = uixul_ModuleBindingService::getInstance();
		$mbs->addImportInPerspective('shipping', 'kiala', 'shipping.perspective');
		$mbs->addImportInActions('shipping', 'kiala', 'shipping.actions');
        $result = $mbs->addImportform('shipping', 'modules_kiala/kialamode');

		if ($result['action'] == 'create')
		{
			uixul_DocumentEditorService::getInstance()->compileEditorsConfig();
		}

        $result = $mbs->addImportform('shipping', 'modules_kiala/kialadspid');
        if ($result['action'] == 'create')
        {
            uixul_DocumentEditorService::getInstance()->compileEditorsConfig();
        }

		f_permission_PermissionService::getInstance()->addImportInRight('shipping', 'kiala', 'shipping.rights');
        $mbs->addImportInActions('order', 'kiala', 'order.actions');

    }

	/**
	 * @return String[]
	 */
	public function getRequiredPackages()
	{
		// Return an array of packages name if the data you are inserting in
		// this file depend on the data of other packages.
		// Example:
		// return array('modules_website', 'modules_users');
		return array();
	}
}