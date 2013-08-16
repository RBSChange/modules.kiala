<?php
/**
 * kiala_patch_0360
 * @package modules.kiala
 */
class kiala_patch_0360 extends patch_BasePatch
{
//  by default, isCodePatch() returns false.
//  decomment the following if your patch modify code instead of the database structure or content.
    /**
     * Returns true if the patch modify code that is versionned.
     * If your patch modify code that is versionned AND database structure or content,
     * you must split it into two different patches.
     * @return Boolean true if the patch modify code that is versionned.
     */
//	public function isCodePatch()
//	{
//		return true;
//	}
 
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{

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


		$smartFolder = order_SmartfolderService::getInstance()->getNewDocumentInstance();
		$smartFolder->setQuery('{"operator":"and","elements":[{"class":"shipping_ShippingModeFilter","parameters":{"type":[null,null,"modules_kiala/kialamode"]}},{"class":"order_OrderFilter","parameters":{"field":["modules_order/order.orderStatus","eq","in_progress"]}}]}');
		$smartFolder->setLabel("Commandes à expédier avec KIALA");
		$smartFolder->save(ModuleService::getInstance()->getRootFolderId("order"));
	}
}