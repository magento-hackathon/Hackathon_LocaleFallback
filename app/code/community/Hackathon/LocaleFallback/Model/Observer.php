<?php

class Hackathon_LocaleFallback_Model_Observer
{
    /**
     * @param $observer
     */
    public function compareLocales($observer)
    {
        $scope = $observer->getStore();
        // get config
        $localeMain = Mage::getStoreConfig('general/locale/code', Mage::app()->getStore($scope));
        $localeFallback = Mage::getStoreConfig('general/locale/code_fallback' , Mage::app()->getStore($scope));

        $translationModel = Mage::getModel('hackathon_localefallback/translate');
        // fetch complete translations
        $mainTranslation = $translationModel->fetchTranslation($localeMain);
        $fallbackTranslation = $translationModel->fetchTranslation($localeFallback);
        // get similarities
        $localeSimilarities = array_intersect(
            $mainTranslation,
            $fallbackTranslation
        );
        $session = Mage::getSingleton('adminhtml/session');
        if(count($localeSimilarities) == count($mainTranslation)){
            $message = Mage::helper("hackathon_localefallback")->__('Translations are identical, you can safely switch off the Locale Fallback.');
            $session->addNotice($message);
        }else{
            $message = Mage::helper("hackathon_localefallback")->__('Translations are differing, values from locale fallback will be used.');
            $session->addSuccess($message);
        }
    }
}
