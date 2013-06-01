<?php

class Hackathon_LocaleFallback_Model_System_Config_Source_Locale
{
    public function toOptionArray()
    {
        return array_merge(
            array(array('value' => '', 'label' => 'Disable')),
            Mage::app()->getLocale()->getOptionLocales()
        );
    }
}
