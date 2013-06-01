<?php
/**
 * Hackathon_LocaleFallback_Model_Translate
 *
 * @author Bastian Ike <b-ike@b-ike.de>
 * @todo validate and test scopes
 */
class Hackathon_LocaleFallback_Model_Translate extends Mage_Core_Model_Translate
{
    /**
     * get translation with Zend_Translate_Adapter_Gettext
     *
     * @see Zend_Translate_Adapter_Gettext
     * @param string $file
     * @return array
     */
    protected function _getGettextFileData($file)
    {
        $data = array();
        if (file_exists($file)) {
            $gettextTranslator = new Zend_Translate(array(
                'adapter' => 'Zend_Translate_Adapter_Gettext',
                'content' => $file,
            ));
            $data = $gettextTranslator->getMessages();
        }
        return $data;
    }

    /**
     * iterates thru files, replaces .csv with .mo an tries to load the gettext translation
     *
     * @param string $moduleName
     * @param array $files
     * @param bool $forceReload
     * @return Hackathon_LocaleFallback_Model_Translate
     */
    protected function _loadGettextModuleTranslation($moduleName, $files, $forceReload=false)
    {
        foreach ($files as $file) {
            $temp = pathinfo($file, PATHINFO_EXTENSION);
            if(isset($temp['extension']) && ($temp['extension'] == 'csv')) {
                $file = substr($file, 0, -3) . 'mo';

                $file = $this->_getModuleFilePath($moduleName, $file);
                $this->_addData($this->_getGettextFileData($file), $moduleName, $forceReload);
            }
        }
        return $this;
    }

    /**
     * loads design locale gettext file
     *
     * @param bool $forceReload
     * @return Hackathon_LocaleFallback_Model_Translate
     */
    private function _loadGettextTranslation($forceReload)
    {
        $file = Mage::getDesign()->getLocaleFileName('translate.mo');
        $this->_addData($this->_getGettextFileData($file), false, $forceReload);
        return $this;
    }

    /**
     * Initialization translation data
     *
     * rewritten to add gettext
     * gettext is loaded after .csv-files!
     *
     * @param   string $area
     * @param   boolean $forceReload
     * @return  Hackathon_LocaleFallback_Model_Translate
     */
    public function init($area, $forceReload = false)
    {
        $this->setConfig(array(self::CONFIG_KEY_AREA => $area));

        $this->_translateInline = Mage::getSingleton('core/translate_inline')
            ->isAllowed($area=='adminhtml' ? 'admin' : null);

        if (!$forceReload) {
            if ($this->_canUseCache()) {
                $this->_data = $this->_loadCache();
                if ($this->_data !== false) {
                    return $this;
                }
            }
            Mage::app()->removeCache($this->getCacheId());
        }

        $this->_data = array();

        if ($localeFallback = Mage::getStoreConfig('general/locale/code_fallback')) {
            // save original locale
            $origLocale = $this->getLocale();

            // set locale fallback
            $this->setLocale($localeFallback);

            // load translations as usual
            foreach ($this->getModulesConfig() as $moduleName => $info) {
                $info = $info->asArray();
                $this->_loadModuleTranslation($moduleName, $info['files'], $forceReload);
                $this->_loadGettextModuleTranslation($moduleName, $info['files'], $forceReload);
            }

            $this->_loadThemeTranslation($forceReload);
            $this->_loadGettextTranslation($forceReload);
            $this->_loadDbTranslation($forceReload);

            // restore original locale
            $this->setLocale($origLocale);
        }

        foreach ($this->getModulesConfig() as $moduleName => $info) {
            $info = $info->asArray();
            $this->_loadModuleTranslation($moduleName, $info['files'], $forceReload);
            $this->_loadGettextModuleTranslation($moduleName, $info['files'], $forceReload);
        }

        $this->_loadThemeTranslation($forceReload);
        $this->_loadGettextTranslation($forceReload);
        $this->_loadDbTranslation($forceReload);

        if (!$forceReload && $this->_canUseCache()) {
            $this->_saveCache();
        }

        return $this;
    }

    /**
     * Load Translation for specific locale and return translation data
     *
     * @param $locale
     * @return array
     */
    public function fetchTranslation($locale)
    {
        // Set Config
        $this->setConfig(array(self::CONFIG_KEY_AREA => 'adminhtml'));

        $this->setLocale($locale);

        $this->_data = array();
        foreach ($this->getModulesConfig() as $moduleName => $info) {
            $info = $info->asArray();
            $this->_loadModuleTranslation($moduleName, $info['files'], false);
            $this->_loadGettextModuleTranslation($moduleName, $info['files'], false);
        }
        $this->_loadThemeTranslation(false);
        $this->_loadGettextTranslation(false);
        $this->_loadDbTranslation(false);

        return $this->getData();
    }
}
