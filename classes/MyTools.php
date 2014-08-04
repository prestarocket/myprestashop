<?php

class MyTools extends Tools
{

    /*
     * Returns an array of enabled language IDs for iterations
     */
    public static function getLangIDs($active = false, $id_shop = false){
        $langIDs = array();
        foreach(Language::getLanguages($active, $id_shop) as $lang){
            $langIDs[] = (int) $lang['id_lang'];
        }
        return $langIDs;
    }

    /*
     * Makes a language array, keys are language IDs, values are the same ($value)
     */
    public static function makeValueLangArray($value = ''){
        return array_fill_keys( self::getLangIDs(), $value );
    }

    /*
     * Parses submitted floating point number and returns it
     */
    public static function getValueFloat($key, $default_value = false){
        return (float) str_replace(',', '.', Tools::getValue($key, $default_value));
    }

    /*
     * Execute an array of SQL statements
    */
    public static function runSQL($sql)
    {
        $db = Db::getInstance();
        foreach ($sql as $query) {
            try {
                if(!$db->execute($query)){
                    return false;
                }
            } catch (Exception $e) {
                // @TODO Set and return module install error
                error_log($e);
                return false;
            }
        }
        return true;
    }

    public static function emailLink( $email ){
        return '<a href="mailto:'.$email.'">'.$email.'</a>';
    }

    public static function telephoneLink( $phoneNum ){
        return '<a href="tel:'.$phoneNum.'">'.$phoneNum.'</a>';
    }

    public static function renderFormView($fields, $titles = array() ){

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
        $view = self::removeScript($view);
        $view = self::removeFormTags($view);

        $style = '';
        if(!self::isBootstrap()){
            $style = '<style>.margin-form{padding-top:0.3em;font-size:0.9em;word-break:break-all;</style>';
        }

        return $style.$view;
    }

    public static function removeScript( $html ){
        return trim( preg_replace('#\<script[.\s\S]*?\<\/script\>#mi', '', $html) );
    }

    public static function removeFormTags( $html ){
        return trim( preg_replace('#<\/?form.*?>#mi', '', $html) );
    }

    public static function isBootstrap(){
        return (version_compare(_PS_VERSION_, 1.6) >= 0) ? true : false;
    }

    public static function makePublicDir( $fullPath ){

        $isCreateDir = true;
        if(!is_dir($fullPath)){
            $isCreateDir = @mkdir($fullPath, 0775);
        }
        @chmod($fullPath, 0775);

        return $isCreateDir;
    }

}