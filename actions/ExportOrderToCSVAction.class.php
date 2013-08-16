<?php
/**
 * kiala_ExportOrderToCSVAction
 * @package modules.kiala.actions
 */
class kiala_ExportOrderToCSVAction extends f_action_BaseAction
{
    const SEPARATOR = "|";
	const DEFAULT_KIALA_PACKET_NUMBER = "999999999999";
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{

		try
		{
        $lsi = LocaleService::getInstance();
		$docId = $request->getParameter('cmpref');

        if (f_util_StringUtils::isEmpty($docId))
        {
            throw new Exception($lsi->transBO("m.kiala.bo.exceptions.no-selected-doc", array('ucf')));
        }

        /** @var order_persistentdocument_order $order */
        try
        {
            $order = order_persistentdocument_order::getInstanceById($docId);
        }
        catch (Exception $e)
        {
            $order = null;
        }

        if (f_util_ObjectUtils::isEmpty($order))
        {
            throw new Exception($lsi->transBO("m.kiala.bo.exceptions.no-doc-for-export", array('ucf'), array('orderId'=>$docId)));
        }
        $query = kiala_KialamodeService::getInstance()->createQuery();
        $query->setProjection(Projections::property("id"));
        $ids = array();
        foreach ($query->findColumn("id") as $id)
        {
            $ids[] = $id;
        }

        if (!in_array($order->getShippingModeId(), $ids))
        {
            throw new Exception($lsi->transBO("m.kiala.bo.exceptions.not-kiala-shipped", array('ucf')));
        }

            $csvLines = $this->getExportDatasForOrder($order);
			$csv = '';
			foreach ($csvLines as $line)
			{
				$csv .= implode(self::SEPARATOR, $line)."\n";
			}
			$fileName = "kiala_expedition_order_".$order->getOrderNumber().'_'.date('Ymd_His').'.csv';

			header("Content-type: text/comma-separated-values");
			header('Content-length: '.strlen($csv));
			header('Content-disposition: attachment; filename="'.$fileName.'"');
			echo $csv;
			exit;
        }
        catch (Exception $e)
        {
			header("Content-type: text/html; charset=utf-8");
			$error = "<html><head><title>";
			$error .= $lsi->transBO("m.kiala.bo.exceptions.export-error-title", array('ucf'));
			$error .= "</title></head><body bgcolor='#FF9CBD'><strong>";
			$error .= $lsi->transBO("m.kiala.bo.exceptions.export-error-title", array('ucf'));
			$error .= "</strong><br/>".$e->getMessage();
			$error .= "</body></html>";
			echo $error;
			exit;
	    }

	}

    /**
     * @param order_persistentdocument_order $order
     * @return array
     */
    protected function getExportDatasForOrder($order)
    {
        $lsi = LocaleService::getInstance();

        $datas = array();
		/* @var customer_persistentdocument_customer $customer */
        $customer = $order->getCustomer();
        $shipAdr = $order->getShippingAddress();


		$query = kiala_KialamodeService::getInstance()->createQuery();
		$query->setProjection(Projections::property("id"));
		$kialaModeIds = array();
		foreach ($query->findColumn("id") as $id)
		{
			$kialaModeIds[] = $id;
		}

		$expeditions = $order->getPublishedExpeditionArrayInverse();
		if (count($expeditions) == 0)
		{
			throw new Exception($lsi->transBO("m.kiala.bo.exceptions.no-expedition-for-export", array('ucf'), array('orderId'=>$order->getId())));
		}
		$parcels = array();
		$tm = f_persistentdocument_TransactionManager::getInstance();
		/* @var order_persistentdocument_expedition $expedition */
		foreach ($expeditions as $expedition)
		{
			if (!in_array($expedition->getShippingModeId(), $kialaModeIds))
			{
				continue;
			}

			if ($parcelNumber = $expedition->getPacketNumber())
			{
				$parcels[$parcelNumber] = array('number'=>$parcelNumber, 'weight'=>0);
			}
			else
			{
				$parcels['allInOne'] = array('number'=>self::DEFAULT_KIALA_PACKET_NUMBER, 'weight'=>0);
			}

			$lines = $expedition->getLineArray();
			foreach ($lines as $line)
			{
				/* @var order_persistentdocument_expeditionline $line */
				$lineParcelNumber = $line->getPacketNumber();
				$lineTrackingNumber = $line->getTrackingNumber();
				if (!$lineParcelNumber)
				{
					$lineParcelNumber = "allInOne";
					try
					{
						$tm->beginTransaction();
						$line->setPacketNumber(self::DEFAULT_KIALA_PACKET_NUMBER);
						$line->save();
						$tm->commit();
					}
					catch (Exception $e)
					{
						$tm->rollback($e);
						throw $e;
					}
				}

				$product = $line->getProduct();
				if (array_key_exists($lineParcelNumber, $parcels))
				{
					//$parcel = $parcels[$parcelNumber];
					//$parcelWeight = $parcel['weight'];
					if (method_exists($product, 'getShippingWeight'))
					{
						$parcels[$lineParcelNumber]['weight'] += $product->getShippingWeight()*$line->getOrderProductQuantity();
					}
				}
				else
				{
					$parcel = array();
					$parcel['weight'] = 0;
					if (method_exists($product, 'getShippingWeight'))
					{
						$parcel['weight'] += $product->getShippingWeight()*$line->getOrderProductQuantity();
					}
					$parcel['number'] = $lineParcelNumber;
					$parcels[$lineParcelNumber] = $parcel;
					$parcels[$lineParcelNumber]['trackingNumber'] = $lineTrackingNumber;
				}
			}
		}


        $shippingMode = shipping_ModeService::getInstance()->createQuery()
            ->add(Restrictions::eq('id', $order->getShippingModeId()))
            ->findUnique();
        $dspid = $shippingMode->getDspidToCountry($shipAdr->getCountry());

        if (is_null($dspid))
        {
            $params = array('fromCountry'=>$shippingMode->getFromCountry()->getLabel(), 'toCountry'=>$shipAdr->getCountry()->getLabel());
            throw new Exception($lsi->transBO("m.kiala.bo.exceptions.no-dspid-for-country", array('ucf'), $params));
        }
        // <!-- DSP identification -->
        // "partnerId" = code DSPID/>
        $datas['partnerId'] = trim($dspid->getDspidCode());

        //   <!-- Parcel information -->
        // "partnerBarcode" type="string"/>
        //$datas['partnerBarcode'] = "";
            // "parcelNumber" type="string"/>
        //$datas['parcelNumber'] = "";
        // "orderNumber" type="string"/>
        $datas['orderNumber'] = $order->getOrderNumber();
        // "orderDate" type="datetime" datepattern="yyyyMMdd"/>
        $datas['orderDate'] = $order->getUICreationdate();

        $bill = $this->getFirstBillForOrder($order);
        $bill->getUICreationdate();

        if (!$bill->isPublished())
        {
            throw new Exception($lsi->transBO("m.kiala.bo.exceptions.no-export-bill-unpaid", array('ucf')));
        }
        if ($bill->hasTemporaryNumber())
        {
            throw new Exception($lsi->transBO("m.kiala.bo.exceptions.no-export-with-temporary-bill-number", array('ucf')));
        }
        // "invoiceNumber" type="string"/>
        $datas['invoiceNumber'] = $bill->getLabel();
        // "invoiceDate" type="datetime" datepattern="yyyyMMdd"/>
        $date = date_Calendar::getInstance(date_Converter::convertDateToLocal($bill->getCreationdate()));
        $datas['invoiceDate'] = date_Formatter::format($date, 'Ymd');
        // "shipmentNumber" type="string"/>
        //$datas['shipmentNumber'] = "";
        // "CODAmount" type="float" numericpattern="#.##"/> CODAMount = Cash On Delivery Amount
        $datas['CODAmount'] = "0.00";
        // "commercialValue" type="float" numericpattern="#.##"/>
        $datas['commercialValue'] = $bill->getAmount();
        // "parcelWeight" type="float" numericpattern="#.###"/>
        //$datas['parcelWeight'] = "";
        // "parcelVolume" type="float" numericpattern="#.###"/>
        //$datas['parcelVolume'] = "";
        // "parcelDescription" type="string"/>
        //$datas['parcelDescription'] = "";

        //  <!-- Information on the recipient of the parcel (destination address etc.) -->
        // "customerId" type="string"/>
        $datas['customerId'] = ($customer->getCodeReference()) ? $customer->getCodeReference() : $customer->getEmail();
        // "customerName" type="string"/>
        $datas['customerName'] = $customer->getLastname();
        // "customerFirstName" type="string"/>
        $datas['customerFirstName'] = $customer->getFirstname();
        // "customerTitle" type="string"/>
        $datas['customerTitle'] = $customer->getCivility();
        // "customerStreet" type="string"/>
        $datas['customerStreet'] = $shipAdr->getAddressLine1();
        // "customerStreetNumber" type="string"/>
        //$datas['customerStreetNumber'] = "";
        // "customerExtraAddressLine" type="string"/>
        $datas['customerExtraAddressLine'] = $shipAdr->getAddressLine2();
		if (strlen($shipAdr->getAddressLine3()))
		{
			$datas['customerExtraAddressLine'] .= ' - '.$shipAdr->getAddressLine3();
		}
        // "customerZip" type="string"/>
        $datas['customerZip'] = $shipAdr->getZipCode();
        // "customerCity" type="string"/>
        $datas['customerCity'] = $shipAdr->getCity();
        // "customerLocality" type="string"/>
        $datas['customerLocality'] = f_util_StringUtils::toUpper($shipAdr->getCountryCode());
        // "customerLanguage" type="string"/>
        $datas['customerLanguage'] = $customer->getLang();
        // "customerPhone1" type="string"/>
        $datas['customerPhone1'] = $shipAdr->getPhone();
		//$customer->getDefaultAddress()->getPhone();
        // "customerPhone2" type="string"/>
        //$datas['customerPhone2'] = "";
        // "customerPhone3" type="string"/>
        //$datas['customerPhone3'] = "";
        // "customerEmail1" type="string"/>
        $datas['customerEmail1'] = $customer->getEmail();
        // "customerEmail2" type="string"/>
        $datas['customerEmail2'] = $shipAdr->getEmail();
        // "customerEmail3" type="string"/>
        //$datas['customerEmail3'] = "";

        //  <!-- Parcel handling information for Kiala -->
        // "positiveNotificationRequested" type="string"/>
        $datas['positiveNotificationRequested'] = "Y";
        // "kialaPoint" type="string"/>
        $datas['kialaPoint'] = $order->getShippingAddress()->getLabel();
        // "backupKialaPoint" type="string"/>
		//$datas['backupKialaPoint'] = "";
		//$datas['backupKialaPoint'] = "";

		$lines = array();
		foreach ($parcels as $key=>$parcel)
		{
			$line = $datas;
			//   <!-- Parcel information -->
			// "partnerBarcode" type="string"/>
			//$line['partnerBarcode'] = " TODO Missing partnerBarcode";
			// "parcelNumber" type="string"/>
			$line['parcelNumber'] = $parcel['number'];
			// "shipmentNumber" type="string"/>
			$line['shipmentNumber'] = $parcel['trackingNumber'];
			// "parcelWeight" type="float" numericpattern="#.###"/>
			$line['parcelWeight'] = number_format($parcel['weight']/1000, 3, '.', '');

			$lines[] = $this->orderDatasForCSV($line);

		}

        return $lines;
    }

	protected function orderDatasForCSV($datas)
	{
		$ordered = array();
		$orderedFields = kiala_KialamodeService::getInstance()->getCsvDefinition();

		foreach ($orderedFields as $index=>$key)
		{
			if (array_key_exists($key, $datas))
			{
				$ordered[$index] = $datas[$key];
			}
			else
			{
				$ordered[$index] = '';
			}
		}
		return $ordered;
	}
    /**
     * @param $order order_persistentdocument_order
     * @return order_persistentdocument_bill
     *
     */
    protected function getFirstBillForOrder($order)
    {
        $bsi = order_BillService::getInstance();
        $query = $bsi->createQuery();
        $query->add(Restrictions::eq('order', $order->getId()));
        //$query->add(Restrictions::published());
        $query->addOrder(Order::asc('creationdate'));

        $result = $query->find();

        return $result[0];
    }
}