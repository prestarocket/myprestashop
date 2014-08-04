<?php


class MyModule extends Module
{

    /**
     * Module constructor
     */
    public function __construct()
    {
        $this->author = 'reservationpartner.com';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');


        $this->folder = _PS_MODULE_DIR_.$this->name.'/';

        $this->bootstrap = (version_compare(_PS_VERSION_, 1.6) >= 0) ? true : false;
        Context::getContext()->smarty->assign(array(
            'bootstrap' => $this->bootstrap,
        ));

        parent::__construct();

        //$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    /**
     * Register an array of module hooks and return true or false
     * @param array $hookNames
     * @return bool
     */
    protected function registerHooks($hookNames){
        $isSuccess = true;
        foreach($hookNames as $hookName){
            $isSuccess &= $this->registerHook($hookName);
        }
        return $isSuccess;
    }

    protected function generateConfigurationForm()
    {
        $castValues = array(
            'int'    => 'intval',
            'float'  => 'floatval',
            'bool'   => 'boolval',
            'string' => 'strval'
        );

        $langFields = array(
            'textarea' => 'textareaLang',
            'text'     => 'textLang',
            'select'   => 'selectLang',
        );


        // Rewrite keys and values to fit HelperOptions class standards

        $isRTE = false;
        $configs = $this->configuration['fields'];

        // change 'type'  => 'cast'
        // and    'input' => 'type'

        /*
          'type' => {'text', 'hidden', 'select', 'bool', 'radio',
          'checkbox', 'password', 'textarea', 'file', 'textLang',
          'textareaLang', 'selectLang'},
        */

        foreach($configs as $key => &$config)
        {
            // Set input ID same as name
            $config['id'] = $key;
            /* $config['identifier'] = 'identifier'.$key; */

            // Set rich text editing for textareas // activate with pregreplace
            if($config['input'] == 'textarea'){
                if(isset($config['rte'])){
                    if($config['rte'] == true){
                        $config['autoload_rte'] = true;
                        unset($config['rte']);
                        $isRTE = true;
                    }
                }

                // Set default textarea sizes
                if(!isset($config['cols'])){
                    $config['cols'] = 70;
                }

                if(!isset($config['rows'])){
                    $config['rows'] = 7;
                }
            }

            if(($config['input'] == 'text') || ($config['input'] == 'textLang')){
                if(!isset($config['size'])){
                    $config['size'] = 70;
                }
            }


            // Set 'cast' => intval | floatval | boolval | strval
            if(array_key_exists($config['type'], $castValues)){
                $config['cast'] = $castValues[ $config['type'] ];
            }

            // 'switch' => 'bool', lang ? textLang, textareaLang, selectLang
            $inputType = $config['input'];
            unset( $config['input'] );

            $config['type'] = $inputType;

            if($inputType == 'switch'){
                $config['type'] = 'bool';
            }

            if(isset($config['lang'])){
                if($config['lang'] == true){
                    if(array_key_exists($inputType, $langFields)){
                        $config['type'] = $langFields[ $inputType ];
                    }
                }
            }
        }

        /*
        $icon = array();
        $icon['key'] = ($this->bootstrap ? 'icon' : 'image');
        $icon['val'] = ($this->bootstrap ? 'icon-cogs' : _PS_ADMIN_IMG_.'information.png');
        */

        $fieldsets = array();
        $fieldsets['general'] = array(
            'title'      => $this->l('Module settings'),
            'image'      => '../img/admin/information.png',
            /* $icon['key'] => $icon['val'], */ // Skip to place default
            /* 'top' => $this->l('Text to display before the fieldset'), Unstyled text above form */
            /* 'description' => $this->l('Display as description'), Styled info inside form*/
            /* 'info' => $this->l('Display as info'), Unstyled text inside form */

            'tinymce' => $isRTE,
            'fields'  => $configs,
            'submit'  => array(
                'name'  => 'submit'.$this->name,
                /* 'title' => $this->l('Save'), */ // If PS 1.6
                /* 'class' => 'button btn btn-default pull-right' */ // If PS 1.6
            ),
        );

        if(isset($this->configuration['info'])){
            $fieldsets['general']['description'] = $this->configuration['info'];
        }




        /*
         * ['title'] => $this->l('Carrier options'),                  // The title of the fieldset. If missing, default is 'Options'.
    ['top'] => $this->l('Text to display before the fieldset'),    // This text is display right above the first. Rarely used.
    ['image'] => 'url to icon',                                    // If missing, will use the default icon for the tab.
    ['description'] => $this->l('Display as description'),         // Displays an informational box above the fields.
    ['info'] => $this->l('Display as info'),                       // Displays an unstyled text above the fields.
*/

        //error_log(print_r($fieldsets, true));

        $helper = new HelperOptions();
        $helper->module = $this;
        $helper->id = $this->id;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->title = $this->displayName;

        // If PS 1.5
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                    '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        return $helper->generateOptions($fieldsets);
    }

    /**
     * Executes an array of SQL statements
     * @param array $sql
     * @return bool
     */
    protected function runSQL($sql)
    {
        $db = Db::getInstance();
        foreach ($sql as $query) {
            try {
                if(!$db->execute($query)){
                    return false;
                }
            } catch (Exception $e) {
                // Set module install error ?
                error_log($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Installs a new Admin Tab with provided parameters
     * @param string|array $tabTitle Tab title: single string or language array
     * @param string $controllerClassName Controller's class name, without word 'Controller' at the end
     * @param string|int $parentClassName Parent class name or ID
     * @return bool|int
     */
    protected function installAdminTab($tabTitle, $controllerClassName, $parentClassName)
    {
        $tab = new Tab();

        $tab->class_name = $controllerClassName;
        $tab->module  = $this->name;

        if(is_array($tabTitle)){
            $tab->name = $tabTitle;
        } else {
            $tab->name = MyTools::makeValueLangArray($tabTitle);
        }

        if(!empty($parentClassName) && is_string($parentClassName)){
            $tab->id_parent = (int) Tab::getIdFromClassName($parentClassName);
        } else if( is_int($parentClassName) ) {
            $tab->id_parent = $parentClassName;
        } else {
            $tab->id_parent = 0;
        }

        return $tab->add() ? ((int) $tab->id) : false;
    }

    /**
     * Uninstalls specified Admin Tab
     * @param string $controllerClassName Controller's class name, without word 'Controller' at the end
     * @return bool
     */
    protected function uninstallAdminTab($controllerClassName)
    {
        $id_tab = (int) Tab::getIdFromClassName($controllerClassName);
        $tab    = new Tab($id_tab);
        return $tab->delete();
    }


    /**
     * Installs a module carrier
     * @param array $carrierProperties
     * @return bool|int Returns installed carrier ID or false if failed to install
     */
    public function installModuleCarrier($carrierProperties) {

        $carrier = new Carrier();
        $carrier->hydrate($carrierProperties);

        if ($carrier->add())
        {
            $id_carrier = (int) $carrier->id;

            // Assign carrier to all groups
            $groupIDs = $this->getGroupsIDs();
            $carrier->setGroups($groupIDs);

            // Add weight ranges to carrier
            $rangePrices = array();
            foreach($carrierProperties['ranges'] as $range){
                $rangeWeight = new RangeWeight();
                $rangeWeight->hydrate(array(
                    'id_carrier' => $id_carrier,
                    'delimiter1' => (float) $range['delimiter1'],
                    'delimiter2' => (float) $range['delimiter2'],
                ));
                $rangeWeight->add();

                // Save range ID and price and set it after the Zones have been added
                $rangePrices[] = array(
                    'id_range_weight' => $rangeWeight->id,
                    'price' => $range['price'],
                );
            }

            // Set tax rule group to none (id = 0, all_shops=true)
            $carrier->setTaxRulesGroup(0, true);

            // Add Europe for EVERY carrier range
            // Automatically creates rows in delivery table, price is 0
            $id_zone_europe = Zone::getIdByName('Europe');
            $carrier->addZone($id_zone_europe ? $id_zone_europe : 1);

            // Update prices in delivery table for each range (need IDs)
            foreach($rangePrices as $rangePrice){
                $data  = array('price' => $rangePrice['price'],);
                $where = 'id_range_weight = '.$rangePrice['id_range_weight'];
                Db::getInstance()->update('delivery', $data, $where);
            }

            // Copy carrier logo
            copy($carrierProperties['img'], _PS_SHIP_IMG_DIR_.'/'.$id_carrier.'.jpg');

            return $id_carrier;
        }

        // Failed to add carrier
        return false;
    }

}