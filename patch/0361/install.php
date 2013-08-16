<?php
/**
 * kiala_patch_0361
 * @package modules.kiala
 */
class kiala_patch_0361 extends patch_BasePatch
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
        $newPath = f_util_FileUtils::buildWebeditPath('modules/kiala/persistentdocument/kialamode.xml');
        $newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'kiala', 'kialamode');
        $newProp = $newModel->getPropertyByName('fromCountry');
        f_persistentdocument_PersistentProvider::getInstance()->addProperty('kiala', 'kialamode', $newProp);
		$newProp = $newModel->getPropertyByName('isWeightRequired');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('kiala', 'kialamode', $newProp);
        $newProp = $newModel->getPropertyByName('packnshiptype');
        f_persistentdocument_PersistentProvider::getInstance()->addProperty('kiala', 'kialamode', $newProp);

		$this->execChangeCommand('compile-db-schema');
    }
}