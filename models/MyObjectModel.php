<?php

class MyObjectModel extends ObjectModel {

    public $prop1,
           $prop2,
           $prop3,
           $prop4;

    /**
     * Model definition
     * @var array
     */
    public static $definition = array(
        'table'   => 'my_object_model',
        'primary' => 'id_my_object_model',
        'fields'  => array(
            'prop1' => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'prop2' => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'prop3' => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'prop4' => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),

            /* Lang fields */
        )
    );

    public function add($autodate = true, $null_values = false){
        //$this->module_description = nl2br($this->module_description);
        return parent::add($autodate, $null_values);
    }


    /**
     * Returns model's table name
     * @param bool $prefix
     * @return string
     */
    public static function getTableName($prefix = false){
        return ($prefix ? _DB_PREFIX_ : '').self::$definition['table'];
    }

    /**
     * Drops all current data saved in the table.
     * @return bool
     */
    public static function dropData(){
        $sql = 'TRUNCATE TABLE `'.self::getTableName(true).'`';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Get records count
     * @return int|false
     */
    public static function getCount(){
        $sql = new DbQuery();
        $sql->select('COUNT(*)')->from(self::getTableName());
        //$sql->where('active = 1');
        return Db::getInstance()->getValue($sql);
    }

    /**
     * Creates model's table
     * @return bool
     */
    public static function createTable(){
        $sql =
            'CREATE TABLE IF NOT EXISTS `'.self::getTableName(true).'` (
            `id_my_object_model` int(11)      NOT NULL AUTO_INCREMENT,
            `prop1`              varchar(255) NOT NULL,
            `prop2`              varchar(255) NOT NULL,
            `prop3`              varchar(255) NOT NULL DEFAULT 0,
            `prop4`              varchar(255) NOT NULL,
            PRIMARY KEY (`id_my_object_model`)
        ) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Drops model's table
     * @return bool
     */
    public static function dropTable(){
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'.self::getTableName(true).'`;');
    }

    public static function allowedMimeTypes(){
        return array(
            'txt'  => 'text/plain',
            'rtf'  => 'application/rtf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pdf'  => 'application/pdf',
            'zip'  => 'application/zip',
            'png'  => 'image/png',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'jpg'  => 'image/jpeg',
            'rar'  => 'application/x-rar-compressed',
            '7z'   => 'application/x-7z-compressed',
        );
    }

    public function getFileMimeType(){
        $ext = pathinfo($this->getFileName(), PATHINFO_EXTENSION);
        $mimeTypes = self::allowedMimeTypes();
        return array_key_exists($ext, $mimeTypes) ? $mimeTypes[$ext] : false;
    }

}