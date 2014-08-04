<?php

class LpExpressTerminal extends ObjectModel {

    public $machineid,
        $name,
        $address,
        $zip,
        $city,
        $comment,
        $inside,
        $boxcount,
        $collectinghours,
        $workinghours,
        $latitude,
        $longitude,
        $boxes_s,
        $boxes_m,
        $boxes_l,
        $boxes_xl,
        $active = 1,
        $deleted = 0;

    /**
     * Model definition
     * @var array
     */
    public static $definition = array(
        'table'   => 'lp_express_terminal',
        'primary' => 'id_lp_express_terminal',
        'fields'  => array(
            'machineid'       => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'name'            => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'address'         => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'zip'             => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'city'            => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'comment'         => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'inside'          => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'boxcount'        => array('type' => self::TYPE_INT,     'validate' => 'isUnsignedInt'                                  ),
            'collectinghours' => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'workinghours'    => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'latitude'        => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'longitude'       => array('type' => self::TYPE_STRING,  'validate' => 'isCleanHtml', 'required' => false, 'size' => 255),
            'boxes_s'         => array('type' => self::TYPE_INT,     'validate' => 'isUnsignedInt'                                   ),
            'boxes_m'         => array('type' => self::TYPE_INT,     'validate' => 'isUnsignedInt'                                   ),
            'boxes_l'         => array('type' => self::TYPE_INT,     'validate' => 'isUnsignedInt'                                   ),
            'boxes_xl'        => array('type' => self::TYPE_INT,     'validate' => 'isUnsignedInt'                                   ),
            'active'          => array('type' => self::TYPE_BOOL,    'validate' => 'isBool'                                          ),
            'deleted'         => array('type' => self::TYPE_BOOL,    'validate' => 'isBool'                                          ),
        )
    );

    /**
     * Returns model's table name
     * @param bool $prefix
     * @return string
     */
    public static function getTableName($prefix = true){
        return ($prefix ? _DB_PREFIX_ : '').self::$definition['table'];
    }

    /**
     * Drops all current terminals saved in the table.
     * @return bool
     */
    public static function deleteAllTerminals(){
        $sql = 'TRUNCATE TABLE `'.self::getTableName().'`';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Returns all active parcel terminals

     * @return array
     */
    public static function getTerminals(){

        $sql = new DbQuery();
        $sql->from(self::getTableName(false));
        $sql->where('active = 1');
        $sql->orderBy('city');

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Filters given data array and replaces all terminals data with new data.
     * @param array $terminalsData Data array, containing parcel terminals data
     */
    public static function refreshTerminals(&$terminalsData){

        // Format data array, ready to be used in model's hydrate method
        // Remove boxes property and create a property for each box size
        $boxKeys = array(
            'Small'  => 'boxes_s',
            'Medium' => 'boxes_m',
            'Large'  => 'boxes_l',
            'XLarge' => 'boxes_xl',
        );
        foreach(array_keys($terminalsData) as $key){
            $boxes = $terminalsData[$key]['boxes'];
            unset($terminalsData[$key]['boxes']);

            foreach($boxes as $box){
                $bpBoxKey = $box['size'];
                if(array_key_exists($bpBoxKey, $boxKeys)){
                    $boxKey = $boxKeys[$bpBoxKey];
                    $terminalsData[$key][$boxKey] = $box['available'];
                }
            }
        }

        // Filter out invalid terminals
        foreach($terminalsData as $key => $terminalData){
            $isDropRow = false;
            if(empty($terminalData['machineid'])){
                $isDropRow = true;
            }

            if(empty($terminalData['address'])){
                $isDropRow = true;
            }

            if(empty($terminalData['comment'])){
                $isDropRow = true;
            }

            if($isDropRow){
                unset($terminalsData[$key]);
            }
        }

        if(is_array($terminalsData) && !empty($terminalsData)){
            LpExpressTerminal::deleteAllTerminals();
            foreach($terminalsData as $terminalData){
                $obj = new LpExpressTerminal();
                $obj->hydrate($terminalData);
                $obj->save();
            }
        }
    }

    /**
     * Returns parcel terminal ID by given machine ID. Returns false if not found.
     * @param string $machineid
     * @return int|false
     */
    public static function getIdByMachineId($machineid){
        if(empty($machineid)){
            return false;
        }
        $sql = new DbQuery();
        $sql->select('id_lp_express_terminal')->from(self::getTableName(false));
        $sql->where('machineid = '.$machineid);
        return Db::getInstance()->getValue($sql);
    }

    /**
     * Get parcel terminals count
     * @return int|false
     */
    public static function getCount(){
        $sql = new DbQuery();
        $sql->select('COUNT(*)')->from(self::getTableName(false))->where('active = 1');
        return Db::getInstance()->getValue($sql);
    }

    /**
     * Creates model's table
     * @return bool
     */
    public static function createTable(){
        $sql =
            'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
            `id_lp_express_terminal` int(11)      NOT NULL AUTO_INCREMENT,
            `machineid`              varchar(255) NOT NULL,
            `name`                   varchar(255) NOT NULL,
            `address`                varchar(255) NOT NULL,
            `zip`                    varchar(255) NOT NULL,
            `city`                   varchar(255) NOT NULL,
            `comment`                varchar(255) NOT NULL,
            `inside`                 tinyint(1)   NOT NULL,
            `boxcount`               int(10)      NOT NULL,
            `collectinghours`        varchar(255) NOT NULL,
            `workinghours`           varchar(255) NOT NULL,
            `latitude`               varchar(255) NOT NULL,
            `longitude`              varchar(255) NOT NULL,
            `boxes_s`                int(10)      NOT NULL DEFAULT 0,
            `boxes_m`                int(10)      NOT NULL DEFAULT 0,
            `boxes_l`                int(10)      NOT NULL DEFAULT 0,
            `boxes_xl`               int(10)      NOT NULL DEFAULT 0,
            `active`                 tinyint(1)   NOT NULL DEFAULT 1,
            `deleted`                tinyint(1)   NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_lp_express_terminal`)
        ) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Drops model's table
     * @return bool
     */
    public static function dropTable(){
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'.self::getTableName().'`;');
    }
}