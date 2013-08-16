<?php
/**
 * kiala_DspidScriptDocumentElement
 * @package modules.kiala.persistentdocument.import
 */
class kiala_DspidScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return kiala_persistentdocument_dspid
     */
    protected function initPersistentDocument()
    {
    	return kiala_DspidService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_kiala/dspid');
	}
}