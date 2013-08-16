<?php
/**
 * kiala_KialadspidScriptDocumentElement
 * @package modules.kiala.persistentdocument.import
 */
class kiala_KialadspidScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return kiala_persistentdocument_kialadspid
     */
    protected function initPersistentDocument()
    {
    	return kiala_KialadspidService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_kiala/kialadspid');
	}
}