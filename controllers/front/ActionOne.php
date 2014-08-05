<?php

require_once (_PS_MODULE_DIR_.'mymodule/models/MyObjectModel.php');
require_once (_PS_MODULE_DIR_.'mymodule/mymodule.php');

class MyModuleActionOneModuleFrontController extends FrontController {

    const MSG_INVALID_EMAIL  = 1,
          MSG_EMPTY_DESC     = 2,
          MSG_TOO_LONG_DESC  = 3,
          MSG_INVALID_DESC   = 4,
          MSG_INQUIRY_SAVED  = 5,
          MSG_INQUIRY_FAIL   = 6,
          MSG_UPLOAD_FAIL    = 7,
          MSG_INVALID_FILE   = 8,
          MSG_FILE_NOT_SAVED = 9,
          MSG_FILE_MAX_SIZE  = 10;

    const MAX_UPLOAD_SIZE    = 20971520;

    public function setMedia()
    {
        parent::setMedia();
        $this->addJS (_MODULE_DIR_.'mymodule/js/jquery.autosize.min.js');
        $this->addCSS(_MODULE_DIR_.'mymodule/css/mymodule.css');
    }

    public function initContent()
    {
        parent::initContent();

        $id_lang = (int) $this->context->language->id;
        $submit  = Context::getContext()->link->getModuleLink('mymodule', 'ActionOne');

        $this->context->smarty->assign(array(
            'paragraph' => Configuration::get('MY_MODULE_PARAGRAPH', $id_lang),
            'submit'    => $submit,
            'time'      => date("Y-m-d H:i:s"),
        ));
    }

    public function init()
    {
        parent::init();
        $this->setTemplate(_PS_MODULE_DIR_.'mymodule/views/mymodule.tpl');

        $input = array(
            'email'                 => Tools::getValue('email', ''),
            'phone_number'          => Tools::getValue('phone_number', ''),
            'module_description'    => Tools::getValue('module_description', ''),
            'description_file_path' => '',
        );

        $error = null;
        if (Tools::isSubmit('submitRequestForm')){

            $description_file = Tools::fileAttachment('description_file');

            if(!empty($description_file)){
                // Check for upload errors
                if (!empty($description_file['name']) && $description_file['error'] != 0){
                    $error = array('type' => 'error', 'code' => self::MSG_UPLOAD_FAIL);
                }

                // Check if file does not exceed allowed file size
                if(filesize ($description_file['tmp_name']) > self::MAX_UPLOAD_SIZE){
                    $error = array('type' => 'error', 'code' => self::MSG_FILE_MAX_SIZE);
                }

                // Check if extension is valid
                $allowedExtensions = array_keys( ModuleRequest::allowedMimeTypes() );
                $ext = pathinfo($description_file['name'], PATHINFO_EXTENSION);
                if (!empty($description_file['name']) && !in_array($ext, $allowedExtensions)){
                    $error = array('type' => 'error', 'code' => self::MSG_INVALID_FILE);
                }
            }

            // Check if email is valid
            if(!Validate::isEmail($input['email'])){
                $error = array('type' => 'error', 'code' => self::MSG_INVALID_EMAIL);
            }

            // Check if description is too short
            if(strlen($input['module_description']) > 10000){
                $error = array('type' => 'error', 'code' => self::MSG_TOO_LONG_DESC);
            }

            // Check if no JS script is present in the description
            if(!Validate::isCleanHtml($input['module_description'])){
                $error = array('type' => 'error', 'code' => self::MSG_INVALID_DESC);
            }

            if($error){
                $this->context->smarty->assign('message', $error);
                $this->context->smarty->assign($input);
            } else {

                $req = new ModuleRequest();
                $req->hydrate($input);

                if($description_file){
                    $fileName  = basename($description_file['name']);

                    $year    = date('Y');
                    $month   = date('m');
                    $day     = date('d');

                    // Cannot use id in folder structure,
                    // because email is sent after object add event without any file
                    //$id = $req->id;

                    $relDir  = $year.'/'.$month.'/'.$day.'/';
                    $fullDir = _PS_UPLOAD_DIR_.'requestamodule/'.$relDir;

                    MyTools::makePublicDir(_PS_UPLOAD_DIR_.'requestamodule/');
                    MyTools::makePublicDir(_PS_UPLOAD_DIR_.'requestamodule/'.$year.'/');
                    MyTools::makePublicDir(_PS_UPLOAD_DIR_.'requestamodule/'.$year.'/'.$month.'/');
                    MyTools::makePublicDir(_PS_UPLOAD_DIR_.'requestamodule/'.$year.'/'.$month.'/'.$day.'/');

                    $relFilePath  = $relDir .$fileName;
                    $fullFilePath = $fullDir.$fileName;

                    $req->description_file_path = $relFilePath;

                    if(!rename($description_file['tmp_name'], $fullFilePath)){
                        $msg =  array('type' => 'success', 'code' => self::MSG_FILE_NOT_SAVED);
                    } else {
                        @chmod($fullFilePath, 0775);
                    }
                }

                if($req->save()){
                    if(!isset($msg)){
                        $msg = array('type' => 'success', 'code' => self::MSG_INQUIRY_SAVED);
                    }
                } else {
                    $msg = array('type' => 'error', 'code' => self::MSG_INQUIRY_FAIL);
                }

                $this->context->smarty->assign('message', $msg);
            } /* if !$error */
        } /* if isSubmit */
    }

}