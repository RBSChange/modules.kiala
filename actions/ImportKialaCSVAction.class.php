<?php
/**
 * kiala_ImportKialaCSVAction
 * @package modules.kiala.actions
 */
class kiala_ImportKialaCSVAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$lsi = LocaleService::getInstance();
		$order = $this->getDocumentInstanceFromRequest($request);
		if (!count($_FILES))
		{
			return $this->sendJSONError($lsi->transBO('m.kiala.bo.exceptions.import-no-file'));
		}

		$extension = f_util_StringUtils::toLower(substr($_FILES['filename']['name'], - 3));
		if ($_FILES['filename']['error'] != UPLOAD_ERR_OK || ($extension != 'csv' && $extension != 'txt'))
		{
			return $this->sendJSONError($lsi->transBO('m.kiala.bo.exceptions.import-bad-file-type', array('ucf')));
		}
		$tempPath = $_FILES['filename']['tmp_name'];
		

		$lines = array();
		if (($h = fopen($tempPath, 'r')) !== FALSE) {;
			while (($data = fgetcsv($h, 2500, '|')) !== FALSE) {
				$csvLine = $this->getFieldsFromDatas($data);
				$lines[$csvLine['parcelNumber']] = $csvLine;
			}
			fclose($h);
		}

		try {
			$this->importKialaCsvForOrder($lines, $order);
		}
		catch (Exception $e)
		{
			return $this->sendJSONError($e->getMessage());
		}

		return $this->sendJSON(array('message' => $lsi->transBO('m.kiala.bo.exceptions.import-success', array('ucf'))));
	}

	protected function getFieldsFromDatas($datas)
	{
		$fields = array();
		$orderedFields = kiala_KialamodeService::getInstance()->getCsvDefinition();

		foreach ($orderedFields as $index=>$key)
		{
			if (array_key_exists($index, $datas))
			{
				$fields[$key] = $datas[$index];
			}
			else
			{
				$fields[$key] = '';
			}
		}
		return $fields;
	}

	/**
	 * Set tracking number on expeditions for $order or throw an exception if problem
	 * @param String[] $csvLines
	 * @param order_persistentdocument_order $order
	 * @throws Exception
	 */
	protected function importKialaCsvForOrder($csvLines, $order)
	{
		$lsi = LocaleService::getInstance();

		// Quick integrity check
		foreach ($csvLines as $line)
		{
			if ($order->getOrderNumber() != $line['orderNumber'])
			{
				throw new Exception($lsi->transBO('m.kiala.bo.exceptions.bad-order', array('ucf'), array('orderNumber'=>$line['orderNumber'])));
			}
		}

		$query = kiala_KialamodeService::getInstance()->createQuery();
		$query->setProjection(Projections::property("id"));
		$ids = array();
		foreach ($query->findColumn("id") as $id)
		{
			$ids[] = $id;
		}

		$expeditions = $order->getPublishedExpeditionArrayInverse();
		if (count($expeditions) == 0)
		{
			throw new Exception($lsi->transBO("m.kiala.bo.exceptions.no-expedition-for-import", array('ucf')));
		}
		$tm = f_persistentdocument_TransactionManager::getInstance();
		/* @var order_persistentdocument_expedition $expedition */
		foreach ($expeditions as $expedition)
		{

			$lines = $expedition->getLineArray();
			foreach ($lines as $line)
			{
				/* @var order_persistentdocument_expeditionline $line */
				$parcelNumber = $line->getPacketNumber();
				if (array_key_exists($parcelNumber, $csvLines))
				{
					try
					{
						$trackingNumber = $csvLines[$parcelNumber]['shipmentNumber'];
						$tm->beginTransaction();
						$line->setTrackingNumber($trackingNumber);
						$line->save();
						$tm->commit();
					}
					catch (Exception $e)
					{
						$tm->rollback($e);
						throw $e;
					}
				}
			}
		}
	}
}