<?php

if (!defined('_PS_VERSION_')){
    exit;
}

if(!function_exists('getLangIDs')){
    function getLangIDs($active = false, $id_shop = false){
        $langIDs = array();
        foreach(Language::getLanguages($active, $id_shop) as $lang){
            $langIDs[] = (int) $lang['id_lang'];
        }
        return $langIDs;
    }
}

if(!function_exists('makeValueLangArray')){
    function makeValueLangArray($value = ''){
        return array_fill_keys( getLangIDs(), $value );
    }
}

/*
'id_reference'
'name'                  - shipping name (company, eg. DHL), same for all languages
'active'
'is_free'               - is set to true returns getShippingMethod SHIPPING_METHOD_FREE
'url'                   - parcel tracking url, eg. lpexpress.lt/track/parcel/@   @ - tracking number
'shipping_handling'     - include the shipping and handling costs in the carrier price.
                          If true adds (float)$configuration['PS_SHIPPING_HANDLING'] to shipping cost
'shipping_external'     - is carrier an external carrier (installed by module?)
'range_behavior'        - when customer's cart is out of max defined range. 0 - apply highest range cost; 1- disable carrier
'shipping_method'       - billing according to : 1 - weight, 2 - price, 0 - default, 4 - free
'max_width'             - max package width
'max_height'            - max package height
'max_depth'             - max package depth
'max_weight'            - max package weight
'grade'                 - shipping speed rating 0-9 (9 is fastest)
'external_module_name'  - name of the CarrierModule
'is_module'             - used in getCarriers() method to distinguish carriers installed by modules

'need_range'            - is shipping price dependent on range ? Range can be price or weight
                        - If =1, calls these methods getPackageShippingCost (if exists), if not -> getOrderShippingCost
                        - else getOrderShippingCostExternal

'position'		        - position on the list
'deleted'		        - if carrier is deleted, but still must be referenced in old orders

'delay'                 - lang array(), Text description of estimated delivery time (delay)
*/

$lpExpressCarrierDefaultProperties = array(
    'active'               => false,
    'deleted'              => false,
    'is_module'            => true,
    'shipping_external'    => true,
    'external_module_name' => 'lpexpress24',
    'is_free'              => false,
    'shipping_handling'    => false,
    'need_range'           => 1,
    'range_behavior'       => true,
    'shipping_method'      => Carrier::SHIPPING_METHOD_WEIGHT,
    'grade'                => 6,

    /* Custom properties */
    'img'                  => dirname(__FILE__).'/img/lpexpress24.jpg',
);

/*
 * Overwrite any property value in carrier arrays below
 */
$lpExpressCarriers = array(

    'LP_EXPRESS_TERMINAL_S' => array(
        'name'       => 'LP Express 24 pristatymas į dydžio S dėžę siuntų terminale',
        'delay'      => 'Pristatymas per 1-2  d.d. Maksimalūs matmenys 610mm × 80mm × 350mm.',
        'max_weight' => 30,
        'max_width'  => 61,
        'max_depth'  => 35,
        'max_height' => 8,
        'ranges' => array(
            array(
                'delimiter1' => 0.00,
                'delimiter2' => 30.0,
                'price'      => 7.99,
            ),
        ),
    ),
    'LP_EXPRESS_TERMINAL_M' => array(
        'name'       => 'LP Express 24 pristatymas į dydžio M dėžę siuntų terminale',
        'delay'      => 'Pristatymas per 1-2  d.d. Maksimalūs matmenys 610mm × 175mm × 350mm.',
        'max_weight' => 30,
        'max_width'  => 61,
        'max_depth'  => 35,
        'max_height' => 17,
        'ranges' => array(
            array(
                'delimiter1' => 0.00,
                'delimiter2' => 30.0,
                'price'      => 9.99,
            ),
        ),
    ),
    'LP_EXPRESS_TERMINAL_L'  => array(
        'name'       => 'LP Express 24 pristatymas į dydžio L dėžę siuntų terminale',
        'delay'      => 'Pristatymas per 1-2  d.d. Maksimalūs matmenys 610mm × 365mm × 350mm.',
        'max_weight' => 30,
        'max_width'  => 61,
        'max_depth'  => 35,
        'max_height' => 36,
        'ranges' => array(
            array(
                'delimiter1' => 0.00,
                'delimiter2' => 30.0,
                'price'      => 11.99,
            ),
        ),
    ),
    'LP_EXPRESS_TERMINAL_XL' => array(
        'name'       => 'LP Express 24 pristatymas į dydžio XL dėžę siuntų terminale',
        'delay'      => 'Pristatymas per 1-2 d.d. Maksimalūs matmenys 610mm × 745mm × 350mm.',
        'max_weight' => 30,
        'max_width'  => 61,
        'max_depth'  => 35,
        'max_height' => 74,
        'ranges' => array(
            array(
                'delimiter1' => 0.00,
                'delimiter2' => 30.0,
                'price'      => 15.99,
            ),
        ),
    ),
    'LP_EXPRESS_POST_OFFICE' => array(
        'name'  => 'LP Express 24 pristatymas į artimiausią pašto skyrių',
        'delay' => 'Pristatymas per 1-2 d.d.',
        'max_weight' => 10.0,
        'max_width'  => 150,
        'max_depth'  => 150,
        'max_height' => 150,
        'ranges' => array(
            array(
                'delimiter1' => 0.00,
                'delimiter2' => 30.0,
                'price'      => 0.00,
            ),
        ),
    ),
    'LP_EXPRESS_HOME' => array(
        'name'  => 'LP Express 24 pristatymas į namus',
        'delay' => 'Pristatymas per 1-2 d.d.',
        'max_weight' => 31.5,
        'max_width'  => 150,
        'max_depth'  => 150,
        'max_height' => 150,
        'ranges' => array(
            array(
                'delimiter1' => 0.00,
                'delimiter2' => 0.05,
                'price'      => 13.0,
            ),
            array(
                'delimiter1' => 0.05,
                'delimiter2' => 0.50,
                'price'      => 13.5,
            ),
            array(
                'delimiter1' => 0.50,
                'delimiter2' => 1.00,
                'price'      => 15.0,
            ),
            array(
                'delimiter1' => 1.00,
                'delimiter2' => 2.00,
                'price'      => 15.5,
            ),
            array(
                'delimiter1' => 2.00,
                'delimiter2' => 3.00,
                'price'      => 16.0,
            ),
            array(
                'delimiter1' => 3.00,
                'delimiter2' => 5.00,
                'price'      => 17.0,
            ),
            array(
                'delimiter1' => 5.00,
                'delimiter2' => 10.0,
                'price'      => 23.0,
            ),
            array(
                'delimiter1' => 10.0,
                'delimiter2' => 15.0,
                'price'      => 28.0,
            ),
            array(
                'delimiter1' => 15.0,
                'delimiter2' => 20.0,
                'price'      => 30.0,
            ),
            array(
                'delimiter1' => 20.0,
                'delimiter2' => 30.0,
                'price'      => 38.5,
            ),
        ),
    ),

);

foreach($lpExpressCarriers as $carrierKey => $lpExpressCarrier){
    /* Carrier properties array: first copy default properties, then add or overwrite any properties */
    $lpExpressCarriers[$carrierKey] = array_merge($lpExpressCarrierDefaultProperties, $lpExpressCarrier);

    /* Carrier delivery time(delay) text : set the same for all language IDs (assign a newly created language ID array) */
    $lpExpressCarriers[$carrierKey]['delay'] = makeValueLangArray( $lpExpressCarriers[$carrierKey]['delay'] );
}

// Install $lpExpressCarriers