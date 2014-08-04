<?php

require_once(_PS_MODULE_DIR_.'lpexpress24/models/LpExpressTerminal.php');
require_once(_PS_MODULE_DIR_.'lpexpress24/classes/BalticPostAPI.php');

class AdminLpExpressTerminalController extends AdminController
{
    public $bootstrap;
    public $msg;

    public function __construct()
    {
        $this->className  = 'LpExpressTerminal';
        $this->table      = LpExpressTerminal::getTableName(false);
        $this->moduleName = 'lpexpress24';

        $this->_defaultOrderBy  = 'city';
        $this->_defaultOrderWay = 'asc';

        $this->addRowAction('view');
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
            ),
        );

        $this->msg = array(
            'MSG_REFRESH_SUCCESS'    => $this->l('LP Express 24 parcel terminal list refreshed successfully.'),
            'MSG_REFRESH_FAILED'     => $this->l('Could not refresh parcel terminal list.'),
            'MSG_INVALID_LOGIN'      => $this->l('Could not connect to the LP Express 24 server. Please check you log in information and try again.'),
            'MSG_SOAPCLIENT_MISSING' => $this->l('PHP extension \'SoapClient\' is disabled or not installed. Cannot connect to LP Express 24 server. Please contact your server administrator and enable this required extension for the module to work.'),
        );

        $this->fields_list = array(
            'machineid'       => array(
                'title' => $this->l('Machine ID'),
                'width' => 'auto',
            ),
            'name'            => array(
                'title' => $this->l('Name'),
                'width' => 'auto',
            ),
            'address'         => array(
                'title' => $this->l('Address'),
                'width' => 'auto',
            ),
            'zip'             => array(
                'title' => $this->l('Zip'),
                'width' => 'auto',
            ),
            'city'            => array(
                'title' => $this->l('City'),
                'width' => 'auto',
            ),
            'comment'         => array(
                'title' => $this->l('Comment'),
                'width' => 'auto',
            ),
            'inside'          => array(
                'title' => $this->l('Inside'),
                'width' => 'auto',
            ),
            'boxcount'        => array(
                'title' => $this->l('Box Count'),
                'width' => 'auto',
            ),
            'collectinghours' => array(
                'title' => $this->l('Collecting Hours'),
                'width' => 'auto',
            ),
            'workinghours'    => array(
                'title' => $this->l('Working Hours'),
                'width' => 'auto',
            ),
            'latitude'        => array(
                'title' => $this->l('Latitude'),
                'width' => 'auto',
            ),
            'longitude'       => array(
                'title' => $this->l('Longitude'),
                'width' => 'auto',
            ),
            'boxes_s'         => array(
                'title' => $this->l('Boxes S'),
                'width' => 'auto',
            ),
            'boxes_m'         => array(
                'title' => $this->l('Boxes M'),
                'width' => 'auto',
            ),
            'boxes_l'         => array(
                'title' => $this->l('Boxes L'),
                'width' => 'auto',
            ),
            'boxes_xl'        => array(
                'title' => $this->l('Boxes XL'),
                'width' => 'auto',
            ),
        );

        $this->bootstrap = (version_compare(_PS_VERSION_, 1.6) >= 0) ? true : false;

        parent::__construct();
    }

    /**
     * Adds custom toolbar buttons and handles 'Refresh Terminals' button click. Also returns default rendered list
     * @return string
     */
    public function renderList()
    {
        $html   = '';
        $method = Tools::getValue('method');

        if($method == 'refreshList'){
            $html .= $this->_refreshTerminalList();
        }

        $iconRefresh = $this->bootstrap ? 'download' : 'refresh-index';
        $this->toolbar_btn[$iconRefresh] = array(
            'desc' => $this->l('Update Terminals'),
            'href' => AdminController::$currentIndex.'&method=refreshList&token='.Tools::getAdminTokenLite('AdminLpExpressTerminal'),
        );

        $iconConfig = $this->bootstrap ? 'edit' : 'new-url';
        $this->toolbar_btn[$iconConfig] = array(
            'desc' => $this->l('Configure'),
            'href' => $this->context->link->getAdminLink('AdminModules').'&configure='. $this->moduleName,
        );

        // Remove 'Add New' button
        unset($this->toolbar_btn['new']);

        $html .= parent::renderList();

        return $html;
    }

    /**
     * Tries to download latest parcel terminal data and replace it.
     * @return string
     */
    protected function _refreshTerminalList(){

        if(class_exists('SoapClient')){

            $auth = Configuration::getMultiple(array('BP_PARTNER_ID', 'BP_PARTNER_PASSWORD'));
            BalticPostAPI::setAuthData($auth['BP_PARTNER_ID'], $auth['BP_PARTNER_PASSWORD']);

            $terminalsData = BalticPostAPI::getPublicTerminals();

            if(!empty($terminalsData)){
                LpExpressTerminal::refreshTerminals($terminalsData);
                $html = $this->msg('success', $this->msg['MSG_REFRESH_SUCCESS']);
            } else {
                $html = $this->msg('warning', $this->msg['MSG_REFRESH_FAILED']);
            }

            // If BalticPostApi::testAuth();
            // $html = $this->msg('error', $this->msg['MSG_INVALID_LOGIN']);

        } else {
            $html = $this->msg('error', $this->msg['MSG_SOAPCLIENT_MISSING']);
        }

        return $html;
    }

    /**
     * Renders custom model view
     * @return string
     */
    public function renderView(){

        $toolbar = parent::renderView();
        $view    = '';

        if($id_lp_express_terminal = Tools::getValue('id_lp_express_terminal', false)){

            $terminal = new LpExpressTerminal( $id_lp_express_terminal );

            // Array containing field keys and values
            $fields = $terminal->getFields();

            // Create title array
            $titles = array();
            foreach($fields as $fieldKey => $fieldVal){
                if( !empty($this->fields_list[$fieldKey]['title']) ){
                    $titles[$fieldKey] = $this->fields_list[$fieldKey]['title'];
                } else {
                    $titles[$fieldKey] = $fieldKey;
                }

                if(empty($fields[$fieldKey])){
                    $fields[$fieldKey] = '&nbsp;';
                }
            }

            // Custom titles
            $titles['id_lp_express_terminal'] = 'ID';
            $titles['active']  = 'Active';
            $titles['deleted'] = 'Deleted';

            // Set custom values here, e.g. add anchors to email strings
            $customValues = array();

            $view = $this->renderFormView(array_merge($fields, $customValues), $titles);
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
        $view = $this->removeScript($view);
        if(!$this->bootstrap){
            $view = $this->removeFormTags($view);
        }

        // Style modifications
        if(!$this->bootstrap){
            $style = '<style>.margin-form{padding-top:0.3em;font-size:0.9em;word-break:break-all;</style>';
        } else {
            $style = '<style>.form-group .col-lg-9{padding-top:6px;word-break:break-all;</style>';
        }

        return $style.$view;
    }

    /**
     * Removes <script></script> block from given HTML and trims whitespaces.
     * @param string $html
     * @return string
     */
    public function removeScript( $html ){
        return trim( preg_replace('#\<script[.\s\S]*?\<\/script\>#mi', '', $html) );
    }

    /**
     * Removes <form></form> tags from given HTML and trims whitespaces.
     * @param string $html
     * @return string
     */
    public function removeFormTags( $html ){
        return trim( preg_replace('#<\/?form.*?>#mi', '', $html) );
    }

    /**
     * Returns PrestaShop style message HTML
     * @param  string $type Can be 'error', 'success', 'warning'
     * @param  string $text Text to be placed inside the message
     * @param  bool $time Appends time text to the message
     * @return string Message HTML
     */
    public function msg($type, $text, $time = true){
        $classes = array(
            'error'   => $this->bootstrap ? 'alert alert-danger'  : 'error',
            'success' => $this->bootstrap ? 'alert alert-success' : 'conf confirm',
            'warning' => $this->bootstrap ? 'alert alert-warning' : 'warn warning',
        );
        $class = array_key_exists($type, $classes) ? $classes[$type] : $classes['success'];
        $timeText = $time ? sprintf($this->l('Checked @ %s'), date('Y-m-d H:i:s')) : '';
        return '<div class="'.$class.'">'.$text.' '.$timeText.'.</div>';
    }

}