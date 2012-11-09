<?php
/**
 * kiala_KialamodeScriptDocumentElement
 * @package modules.kiala.persistentdocument.import
 */
class kiala_KialamodeScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return kiala_persistentdocument_kialamode
     */
    protected function initPersistentDocument()
    {
    	return kiala_KialamodeService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_kiala/kialamode');
	}
}