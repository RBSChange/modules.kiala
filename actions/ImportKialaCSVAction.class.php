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
		$extension = 'csv';
		
		if ($_FILES['filename']['error'] != UPLOAD_ERR_OK || substr($_FILES['filename']['name'], - strlen($extension)) != $extension)
		{
			return $this->sendJSONError($lsi->transBO('m.kiala.bo.exceptions.import-bad-file-type'));

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
		$this->importKialaCsvForOrder($lines, $order);
		Framework::fatal("########## FILEDS TO IMPORT :\n".var_export($lines, true));
		Framework::fatal("########## DOCUMENT :\n".var_export($order, true));

		return $this->sendJSON(array('message' => $lsi->transBO('m.kiala.bo.exceptions.import-success')));
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

	protected function importKialaCsvForOrder($csvLines, $order)
	{
		$lsi = LocaleService::getInstance();

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