<?php
/**
 * Class where to put your custom methods for document kiala_persistentdocument_kialamode
 * @package modules.kiala.persistentdocument
 */
class kiala_persistentdocument_kialamode extends kiala_persistentdocument_kialamodebase 
{

    /**
     * @param zone_persistentdocument_country $country
     * @return kiala_persistentdocument_kialadspid
     */
    public function getDspidToCountry($country)
    {
        $kdi = kiala_KialadspidService::getInstance();
        return $kdi->getDspidWithModeIdAndCountry($this->getId(), $country);
    }
}