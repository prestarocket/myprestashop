<?php

require_once(_PS_MODULE_DIR_.'lpexpress24/models/LpExpressTerminal.php');
require_once(_PS_MODULE_DIR_.'lpexpress24/classes/BalticPostAPI.php');

class AdminMyModuleMyObjectModelController extends AdminController
{
    public $bootstrap;
    public $msg;

    // protected $context;

    public function __construct()
    {
        $this->className  = 'MyObjectModel';
        $this->table      =  MyObjectModel::getTableName();
        $this->moduleName = 'mymodule';

        //$this->identifier = 'id_mymod';
        //$this->context   = Context::getContext();

        $this->_defaultOrderBy  = 'city';
        $this->_defaultOrderWay = 'asc';

        $this->addRowAction('view');
        $this->addRowAction('delete');
        // $this->addRowAction('edit');

        $this->bulk_actions = array(
            'delete' => array(
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
            ),
        );

        $this->msg = array(
            'TYPE1' => $this->l('TEXT 1'),
            'TYPE2' => $this->l('TEXT 2'),
        );

        /**
         * Fields to show in renderList and renderForm
         */
        $this->fields_list = array(
            'field1'       => array(
                'title' => $this->l('Field 1'),
                'width' => 'auto',
                /* 'align' => 'center', */
            ),
            'field3'            => array(
                'title' => $this->l('Field 2'),
                'width' => 'auto',
            ),
            'field4'         => array(
                'title' => $this->l('Field 3'),
                'width' => 'auto',
            ),
            'field5'    => array(
                'title' => $this->l('Field 4'),
                'width' => 'auto',
            ),
        );

        $this->bootstrap = (version_compare(_PS_VERSION_, 1.6) >= 0) ? true : false;

        parent::__construct();
    }

    /**
     * Add custom toolbar buttons, handle submits
     * @return string HTML
     */
    public function renderList()
    {

        $method = Tools::getValue('method');
        if($method == 'processList'){
            // Process list
            //$html .= $this->_processList();
        }

        $iconRefresh = $this->bootstrap ? 'download' : 'refresh-index';
        $this->toolbar_btn[$iconRefresh] = array(
            'desc' => $this->l('Update Objects'),
            'href' => AdminController::$currentIndex.'&method=processList&token='.Tools::getAdminTokenLite('AdminMyModuleMyObjectModel'),
        );

        $iconConfig = $this->bootstrap ? 'edit' : 'new-url';
        $this->toolbar_btn[$iconConfig] = array(
            'desc' => $this->l('Configure'),
            'href' => $this->context->link->getAdminLink('AdminModules').'&configure='. $this->moduleName,
        );

        // Remove 'Add New' button
        unset($this->toolbar_btn['new']);

        return parent::renderList();
    }

    /**
     * Process and change list data saved in DB
     * @return string HTML
     */
    protected function _processList(){
        $html = '';
        return $html;
    }

    /**
     * Renders custom model view
     * @return string
     */
    public function renderView(){

        $toolbar = parent::renderView();
        $view    = '';



        if($id_object_model = Tools::getValue('id_object_model')){

            $object = new MyObjectModel($id_object_model);

            // Array containing field keys and values
            $fields = $object->getFields();

            // Create title array
            $titles = array();
            foreach($fields as $fieldKey => $fieldVal){
                if( !empty($this->fields_list[$fieldKey]['title']) ){
                    $titles[$fieldKey] = $this->fields_list[$fieldKey]['title'];
                } else {
                    $titles[$fieldKey] = $fieldKey;
                }

                // If value is empty, assign HTML whitespace
                if(empty($fields[$fieldKey])){
                    $fields[$fieldKey] = '&nbsp;';
                }
            }

            // Set custom titles
            $titles['id_object_model'] = 'ID';

            // Set custom values here, e.g. add anchors to email strings
            $customValues = array(
                $fields['email'] = MyTools::makeEmailLink($fields['email']),
            );

            $mergedFields = array_merge($fields, $customValues);

            $view = $this->renderFormView($mergedFields, $titles);
        }

        return $toolbar.$view;
    }

    /**
     * Builds a model view using FormHelper
     * @param mixed array $fields
     * @param string array $titles
     * @return string HTML
     */
    protected function renderFormView($fields, $titles = array() ){

        $fields_form[0]['form'] = array();
        foreach($fields as $fieldKey => $fieldVal){
            $fields_form[0]['form']['input'][] = array(
                'type'  => 'free',
                'label' => $titles[$fieldKey],
                'name'  => $fieldKey,
            );
        }

        $helper = new HelperForm();
        $helper->fields_value = $fields;
        $helper->show_toolbar = false;

        $view = $helper->generateForm($fields_form);
        $view = MyTools::removeScript($view);
        if(!$this->bootstrap){
            $view = MyTools::removeFormTags($view);
        }

        // Add style modifications
        if(!$this->bootstrap){
            $style = '<style>.margin-form{padding-top:0.3em;font-size:0.9em;word-break:break-all;</style>';
        } else {
            $style = '<style>.form-group .col-lg-9{padding-top:6px;word-break:break-all;</style>';
        }

        return $style.$view;
    }

}