<?php
/**
 * kiala_KialadspidService
 * @package modules.kiala
 */
class kiala_KialadspidService extends f_persistentdocument_DocumentService
{
	/**
	 * @var kiala_KialadspidService
	 */
	private static $instance;

	/**
	 * @return kiala_KialadspidService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return kiala_persistentdocument_kialadspid
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_kiala/kialadspid');
	}

	/**
	 * Create a query based on 'modules_kiala/kialadspid' model.
	 * Return document that are instance of modules_kiala/kialadspid,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_kiala/kialadspid');
	}
	
	/**
	 * Create a query based on 'modules_kiala/kialadspid' model.
	 * Only documents that are strictly instance of modules_kiala/kialadspid
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_kiala/kialadspid', false);
	}


    /**
     * @param kiala_persistentdocument_kialadspid $kialaDspid
     * @param array $array
     */
    public function transformToArray($kialaDspid, &$array)
    {
        $array[] = array(
            'id' => $kialaDspid->getId(),
           // 'fromCountry' => $kialaDspid->getPublicationstatus(),
            'status' => $kialaDspid->getPublicationstatus(),
            'toCountryName' => $kialaDspid->getToCountry()->getLabel(),
            'dspidCode' => $kialaDspid->getDspidCode(),
            'startpublicationdate' => $kialaDspid->getUIStartpublicationdate(),
            'endpublicationdate' => $kialaDspid->getUIEndpublicationdate(),
            'actionrow' => true
        );
    }

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
	protected function preSave($document, $parentNodeId)
	{
		parent::preSave($document, $parentNodeId);
		$document->setLabel($document->getToCountry()->getLabel());
		$document->setDspidCode(trim($document->getDspidCode()));
	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId)
	{
        parent::preInsert($document, $parentNodeId);
        $document->setInsertInTree(false);
        $document->setKialamodeId($parentNodeId);
	}

    /**
     * @param $kialamodeId : Id for kialamode
     * @param bool $onlyPublished : if true only published DSPIDs will be returned (default false)
     * @return kiala_persistentdocument_kialadspid[]
     */
    public function getDspidsWithKialamodeId($kialamodeId, $onlyPublished = false)
    {
        $query = $this->createQuery();
        $query->add(Restrictions::eq('kialamodeId', $kialamodeId));
        if ($onlyPublished)
        {
            $query->add(Restrictions::published());
        }
        return $query->find();
    }


    /**
     * @param $modeId integer id for mode
     * @param $country zone_persistentdocument_country
     * @return f_persistentdocument_PersistentDocument
     */
    public function getDspidWithModeIdAndCountry($modeId, $country)
    {
        $query = $this->createQuery();
        $query->add(Restrictions::eq('kialamodeId', $modeId));
        $query->add(Restrictions::eq('toCountry', $country));
        return $query->findUnique();
    }

    /**
     * @param $modeId integer id for mode
     * @return f_persistentdocument_PersistentDocument
     */
    public function getDspidCountWithModeId($modeId)
    {
        $query = $this->createQuery();
        $query->add(Restrictions::eq('kialamodeId', $modeId));
        $query->add(Restrictions::published());
        $query->setProjection(Projections::rowCount());
        $result = $query->find();

        return $result[0]["rowcount"];
    }

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postInsert($document, $parentNodeId)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preUpdate($document, $parentNodeId)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postUpdate($document, $parentNodeId)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postSave($document, $parentNodeId)
//	{
//	}


	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @return void
	 */
//	protected function preDelete($document)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @return void
	 */
//	protected function preDeleteLocalized($document)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @return void
	 */
//	protected function postDelete($document)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @return void
	 */
//	protected function postDeleteLocalized($document)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @return boolean true if the document is publishable, false if it is not.
	 */
//	public function isPublishable($document)
//	{
//		$result = parent::isPublishable($document);
//		return $result;
//	}


	/**
	 * Methode à surcharger pour effectuer des post traitement apres le changement de status du document
	 * utiliser $document->getPublicationstatus() pour retrouver le nouveau status du document.
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param String $oldPublicationStatus
	 * @param array<"cause" => String, "modifiedPropertyNames" => array, "oldPropertyValues" => array> $params
	 * @return void
	 */
	protected function publicationStatusChanged($document, $oldPublicationStatus, $params)
	{
        $mode = kiala_persistentdocument_kialamode::getInstanceById($document->getKialamodeId());
        $mode->getDocumentService()->publishDocumentIfPossible($mode);
	}

	/**
	 * Correction document is available via $args['correction'].
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Array<String=>mixed> $args
	 */
//	protected function onCorrectionActivated($document, $args)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagAdded($document, $tag)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagRemoved($document, $tag)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $fromDocument
	 * @param f_persistentdocument_PersistentDocument $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedFrom($fromDocument, $toDocument, $tag)
//	{
//	}

	/**
	 * @param f_persistentdocument_PersistentDocument $fromDocument
	 * @param kiala_persistentdocument_kialadspid $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedTo($fromDocument, $toDocument, $tag)
//	{
//	}

	/**
	 * Called before the moveToOperation starts. The method is executed INSIDE a
	 * transaction.
	 *
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Integer $destId
	 */
//	protected function onMoveToStart($document, $destId)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param Integer $destId
	 * @return void
	 */
//	protected function onDocumentMoved($document, $destId)
//	{
//	}

	/**
	 * this method is call before saving the duplicate document.
	 * If this method not override in the document service, the document isn't duplicable.
	 * An IllegalOperationException is so launched.
	 *
	 * @param kiala_persistentdocument_kialadspid $newDocument
	 * @param kiala_persistentdocument_kialadspid $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
//	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
//	{
//		throw new IllegalOperationException('This document cannot be duplicated.');
//	}

	/**
	 * this method is call after saving the duplicate document.
	 * $newDocument has an id affected.
	 * Traitment of the children of $originalDocument.
	 *
	 * @param kiala_persistentdocument_kialadspid $newDocument
	 * @param kiala_persistentdocument_kialadspid $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
//	protected function postDuplicate($newDocument, $originalDocument, $parentNodeId)
//	{
//	}

	/**
	 * @param website_UrlRewritingService $urlRewritingService
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param website_persistentdocument_website $website
	 * @param string $lang
	 * @param array $parameters
	 * @return f_web_Link | null
	 */
//	public function getWebLink($urlRewritingService, $document, $website, $lang, $parameters)
//	{
//		return null;
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @return integer | null
	 */
//	public function getWebsiteId($document)
//	{
//		return parent::getWebsiteId($document);
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @return integer[] | null
	 */
//	public function getWebsiteIds($document)
//	{
//		return parent::getWebsiteIds($document);
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @return website_persistentdocument_page | null
	 */
//	public function getDisplayPage($document)
//	{
//		return parent::getDisplayPage($document);
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
//	public function getResume($document, $forModuleName, $allowedSections = null)
//	{
//		$resume = parent::getResume($document, $forModuleName, $allowedSections);
//		return $resume;
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param string $bockName
	 * @return array with entries 'module' and 'template'. 
	 */
//	public function getSolrsearchResultItemTemplate($document, $bockName)
//	{
//		return array('module' => 'kiala', 'template' => 'Kiala-Inc-KialadspidResultDetail');
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
//	public function addTreeAttributes($document, $moduleName, $treeType, &$nodeAttributes)
//	{
//	}
	
	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param string[] $propertiesName
	 * @param array $datas
	 * @param integer $parentId
	 */
//	public function addFormProperties($document, $propertiesName, &$datas, $parentId = null)
//	{
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document (Read only)
	 * @param array $defaultSynchroConfig string : string[]
	 * @return array string : string[]
	 */
//	public function getI18nSynchroConfig($document, $defaultSynchroConfig)
//	{
//		return parent::getI18nSynchroConfig($document, $defaultSynchroConfig);
//	}

	/**
	 * @param kiala_persistentdocument_kialadspid $document (Read only)
	 * @param kiala_persistentdocument_kialadspidI18n $from (Read only)
	 * @param kiala_persistentdocument_kialadspidI18n $to
	 * @return boolean
	 */
//	public function synchronizeI18nProperties($document, $from, $to)
//	{
//		return parent::synchronizeI18nProperties($document, $from, $to);
//	}	

	/**
	 * @param kiala_persistentdocument_kialadspid $document
	 * @param string[] $subModelNames
	 * @param integer $locateDocumentId null if use startindex
	 * @param integer $pageSize
	 * @param integer $startIndex
	 * @param integer $totalCount
	 * @param string $orderBy
	 * @return f_persistentdocument_PersistentDocument[]
	 */
//	public function getVirtualChildrenAt($document, $subModelNames, $locateDocumentId, $pageSize, &$startIndex, &$totalCount, $orderBy)
//	{
//		$totalCount = 0
//		return array();
//	}	
}