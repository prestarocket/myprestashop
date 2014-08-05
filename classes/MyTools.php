<?php

class MyTools extends Tools
{
    /**
     * Returns shop's languages IDs array
     * @param bool $active
     * @param bool $id_shop
     * @return array Integer array of IDs
     */
    public static function getLangIDs($active = false, $id_shop = false){
        $langIDs = array();
        foreach(Language::getLanguages($active, $id_shop) as $lang){
            $langIDs[] = (int) $lang['id_lang'];
        }
        return $langIDs;
    }

    /**
     * Returns shop's users groups IDs array
     * @return array Integer array of IDs
     */
    public function getGroupsIDs(){
        $sql = new DbQuery();
        $sql->select('id_group')->from('group');
        $rows = Db::getInstance()->executeS($sql);
        $ids = array();
        foreach($rows as $row){
            $ids[] = (int) $row['id_group'];
        }
        return $ids;
    }

    /**
     * Makes a language array, keys are language IDs, values are the same ($value)
     * @param mixed $value
     * @return array
     */
    public static function makeValueLangArray($value = null){
        return array_fill_keys( self::getLangIDs(), $value );
    }

    /**
     * Parses submitted floating point number (replaces commas and convert to float) and returns it
     * @param $key
     * @param bool $default_value
     * @return float
     */
    public static function getValueFloat($key, $default_value = false){
        return (float) str_replace(',', '.', Tools::getValue($key, $default_value));
    }

    /**
     * Executes an array of SQL statements
     * @param array $sql
     * @return bool
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

    /**
     * Creates an HTML email link
     * @param string $email
     * @return string HTML
     */
    public static function emailLink($email){
        return '<a href="mailto:'.$email.'">'.$email.'</a>';
    }

    /**
     * Creates an HTML phone link (works on Android, Skype)
     * @param string $phoneNum
     * @return string HTML
     */
    public static function telephoneLink($phoneNum){
        return '<a href="tel:'.$phoneNum.'">'.$phoneNum.'</a>';
    }

    /**
     * Removes <script>.*</script> blocks from HTML
     * @param $html HTML
     * @return string HTML
     */
    public static function removeScript( $html ){
        return trim( preg_replace('#\<script[.\s\S]*?\<\/script\>#mi', '', $html) );
    }

    /**
     * Removes only <form>, </form> tags, not the content
     * @param $html HTML
     * @return string HTML
     */
    public static function removeFormTags( $html ){
        return trim( preg_replace('#<\/?form.*?>#mi', '', $html) );
    }

    /**
     * Checks if PrestaShop is using bootstrap
     * @return bool
     */
    public static function isBootstrap(){
        return (version_compare(_PS_VERSION_, 1.6) >= 0) ? true : false;
    }

    /*
     *
     */
    /**
     * Creates a publicly readable path (folders)
     * @param $fullPath Full local folder path
     * @return bool
     */
    public static function makePublicDir( $fullPath ){

        $isRecursive = true;
        $isCreateDir = true;
        if(!is_dir($fullPath)){
            $isCreateDir = mkdir($fullPath, 0775, $isRecursive);
        }
        chmod($fullPath, 0775);

        return $isCreateDir;
    }

    /**
     * @param  string $type Can be 'error', 'success', 'warning'
     * @param  string $text Text to be placed inside the message
     * @param  bool $time Appends time text to the message
     * @return string Message HTML
     */
    public function msg($type, $text, $checked = false){
        $isBootstrap = self::isBootstrap();
        $classes = array(
            'error'   => $isBootstrap ? 'alert alert-danger'  : 'error',
            'success' => $isBootstrap ? 'alert alert-success' : 'conf confirm',
            'warning' => $isBootstrap ? 'alert alert-warning' : 'warn warning',
        );
        $class = array_key_exists($type, $classes) ? $classes[$type] : $classes['success'];
        $timeText = $checked ? sprintf($this->l('Checked @ %s'), date('Y-m-d H:i:s')) : '';
        return '<div class="'.$class.'">'.$text.' '.$timeText.'.</div>';
    }

}