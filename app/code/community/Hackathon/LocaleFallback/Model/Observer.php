<?php
/**
 * @category Hackathon
 * @package Hackathon_LocaleFallback
 * @author Bastian Ike <b-ike@b-ike.de>
 * @developer 
 * @version 0.1.0
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)  
 */
class Hackathon_LocaleFallback_Model_Observer
{
    /**
     * @param $observer
     */
    public function compareLocales($observer)
    {
        $scope = $observer->getStore();
        $helper = Mage::helper("hackathon_localefallback");
        // get config
        $localePreferred = Mage::getStoreConfig('general/locale/code',          Mage::app()->getStore($scope));
        $localeFallback  = Mage::getStoreConfig('general/locale/code_fallback', Mage::app()->getStore($scope));

        $translationModel = Mage::getModel('hackathon_localefallback/translate');
        // fetch complete translations
        $translationPreferred = $translationModel->fetchTranslation($localePreferred);
        $translationFallback  = $translationModel->fetchTranslation($localeFallback);
        // get similarities
        $localeSimilarities = array_intersect(
            $translationPreferred,
            $translationFallback
        );
        $session = Mage::getSingleton('adminhtml/session');
        if(count($localeSimilarities) == count($translationPreferred)) {
            $message = $helper->__('Translations are identical, you can safely switch off the Locale Fallback.');
            $session->addNotice($message);
        } else {
            $message = $helper->__('Translations are differing, values from Locale Fallback will be used.');
            $session->addSuccess($message);
        }
    }
}
