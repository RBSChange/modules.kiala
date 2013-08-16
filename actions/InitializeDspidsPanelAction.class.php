<?php
class kiala_InitializeDspidsPanelAction extends f_action_BaseJSONAction
{
	
	/**
	 * @param Request $request
	 * @return kiala_persistentdocument_kialamode
	 */
	private function getKialaModeFromRequest($request)
	{
		return $this->getDocumentInstanceFromRequest($request);
	}
	
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
        $kds = kiala_KialadspidService::getInstance();
		$kialamode = $this->getKialaModeFromRequest($request);
		$data = array('kialamodeId' => $kialamode->getId());

        $date = $request->getParameter('date');
        if (f_util_StringUtils::isEmpty($date))
        {
            $date = null;
        }
        $data['date'] = $date;

        $countriesInfo = array();
        /* @var $country zone_persistentdocument_country */
        foreach (kiala_ModuleService::getOperatedCountries() as $idx => $country)
        {
            $countriesInfo[$idx]= array('label' => $country->getLabel(),
                'id' => $country->getId(),
                'published' => $country->isPublished());
            if (f_util_StringUtils::toLower($country->getCode()) == 'fr')
            {
                $data['fromCountry'] = $countriesInfo[$idx]['id'];
            }
        }
        $data['countries'] = $countriesInfo;



        /*
        if ($request->hasParameter('fromCountry'))
        {
            $data['fromCountry'] = $request->getParameter('fromCountry');
        }
        else
        {
            if (!isset($data['fromCountry']))
            {
                $data['fromCountry'] = $countriesInfo[0]['id'];
            }
        }
        */

        /*
        if ($request->hasParameter('toCountry'))
        {
            $data['toCountry'] = $request->getParameter('toCountry');
        }
        else
        {
            $data['toCountry'] = $countriesInfo[0]['id'];
        }
*/
        // Add DSPIDs list.
        $dspids = array();
        foreach ($kds->getDspidsWithKialamodeId($kialamode->getId()) as $dspid)
        {
             $kds->transformToArray($dspid, $dspids);
        }
        $data['kialadspids'] = JsonService::getInstance()->encode($dspids);

		return $this->sendJSON($data);
	}
}