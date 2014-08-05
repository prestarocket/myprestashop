<?php

/**
 * Check if file has been called by PrestaShop
 */
if (!defined('_PS_VERSION_')){
    exit;
}

class MyModule extends Module
{
    /**
     * @var Custom class properties must be declared first (works 4 times faster)
     */
    public $customProperty;

    /**
     * Module class constructor
     */
    public function __construct()
    {
        $this->name    = 'mymodule';
        /*
         * administration
         * advertising_marketing
         * analytics_stats
         * billing_invoicing
         * checkout
         * content_management
         * emailing
         * export
         * front_office_features
         * i18n_localization
         * market_place
         * merchandizing
         * migration_tools
         * mobile
         * others
         * payments_gateways
         * payment_security
         * pricing_promotion
         * quick_bulk_update
         * search_filter
         * seo
         * shipping_logistics
         * slideshows
         * smart_shopping
         * social_networks
         */
        $this->tab     = 'front_office_features';
        $this->version = '1.0.0';
        $this->author  = 'reservationpartner.com';

        // $this->module_key => '';

        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');

        parent::__construct();

        /**
         * $this->l() can only called after parent::construct();
         */
        $this->displayName      = $this->l('MyModule Name');
        $this->description      = $this->l('MyModule Description');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        /**
         * Shows warning in module page, back-office
         */
        $this->warning = $this->l('warning');

        /**
         * Module folder
         */
        $this->folder = _PS_MODULE_DIR_.$this->name.'/';

        /**
         * Bootstrap variables in module and template scopes
         */
        $this->bootstrap = (version_compare(_PS_VERSION_, 1.6) >= 0) ? true : false;
        Context::getContext()->smarty->assign(array(
            'bootstrap' => $this->bootstrap,
        ));

        $this->init();
    }

    /**
     * Custom initialization function
     */
    protected function init(){
        /**
         * Check if module is installed
         */
        if (self::isInstalled($this->name)){

        }
    }

    /**
     * Module install function
     * @return bool
     */
    public function install(){

        /* Must be called before registering any hooks */
        if (!parent::install()){
            return false;
        }

        /* Always roll back changes if install fails */
        $cond1 = false;
        if(!$cond1){
            $this->uninstall();
            return false;
        }

        return true;
    }

    /**
     * Module uninstall function
     * @return bool
     */
    public function uninstall(){

        /* Move to bottom ? */
        if (!parent::uninstall()){
            return false;
        }

        return true;
    }

    /**
     * On module disable event
     * @param bool $forceAll
     * @return bool
     */
    public function disable($forceAll = false) {
        // Disable installed features
        return parent::disable($forceAll);
    }

    /**
     * On module enable event
     * @param bool $forceAll
     * @return bool
     */
    public function enable($forceAll = false) {
        // Re-enable installed features
        return parent::enable($forceAll);
    }

    /**
     * Creates and returns configuration page content for this module
     * @return string HTML
     */
    public function getContent()
    {
        $info  = $this->_getPostProcessMessage();
        $info .= $this->_getConfigurationJS();

        $fields = array(
            'MYMODULE_CONFIG_VAR_1' => array(
                'title' => $this->l(''),
                'desc'  => $this->l(''),
                /* {'intval', 'floatval', 'boolval', 'strval'} */
                'cast'  => 'intval',
                /* 'text', 'hidden', 'select', 'bool', 'radio', 'checkbox', 'password',
                   'textarea', 'file', 'textLang', 'textareaLang', 'selectLang'*/
                'type' => 'text',
                'suffix' => '$',
                /* id ? */
                'identifier' => 'configVar1',
                /* for select field only */
                'list' => array(),
                /* for select field only */
                'empty_message' => $this->l('Please select an item'),
                /* for textarea field only */
                'cols' => 40,
                /* for textarea field only */
                'rows' => 5,
                /* for file field only */
                'thumb' => 'url/img/img.jpg',
                /* Disable the field depending on shop context */
                'is_invisible' => false,
            ),
        );

        $fieldsets = array();
        $fieldsets['general'] = array(
            'title'   => $this->l('Module settings'),
            'image'   => '../img/admin/information.png',
            /* 'info' - unstyled info inside form */
            'info'    => $info,
            /* 'description' - styled message text inside form */
            /* 'top' - unstyled text above form */
            'fields'  => $fields,
            'submit'  => array(
                'name'  => 'submit'.$this->name,
            ),
        );

        /**
         * PS 1.6 shows save button below
         */
        if($this->bootstrap){
            $fieldsets['general']['submit']['title'] = $this->l('Save');
            /* 'class' => 'button btn btn-default pull-right' */
        }

        /* Helper Options automatically retrieves specified fields (their values) from configuration table */
        $helper = new HelperOptions();
        $helper->module = $this;
        $helper->id     = $this->id; /* ? */
        $helper->token  = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->title  = $this->displayName;

        /* Enable toolbar on PS 1.5 only */
        $helper->show_toolbar = !$this->bootstrap; /**/
        /* Enables sticky toolbar */
        $helper->toolbar_scroll = true;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'desc' => $this->l('Back to list'),
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            )
        );

        return $helper->generateOptions($fieldsets);
    }

    protected function getHtmlFormTemplate(){
        $this->context->smarty->assign(array(
            'param1' => 'val1',
            'param2' => 'val2',
        ));
        return $this->context->smarty->fetch($this->folder.'views/templates/template.tpl');
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
        $tab->module     = $this->name;
        $tab->name       = is_array($tabTitle) ? $tabTitle : MyTools::makeValueLangArray($tabTitle);

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

}