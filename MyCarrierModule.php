<?php

class MyCarrierModule extends CarrierModule {

    /**
     * @var Automatically assigned by CartController, use when calculating costs
     */
    public $id_carrier;

    public function __construct(){}
    public function install(){}
    public function uninstall(){}

    /**
     * Disable installed carrier here
     * @param bool $forceAll
     */
    public function disable($forceAll = false) {}

    /**
     * Re-enable installed carrier here
     * @param bool $forceAll
     */
    public function enable($forceAll = false) {}

    /**
     * Recalculates shipping cost. Called if need_range=1; AND method getPackageShippingCost is_callable.
     * Doesn't need to be implemented
     * @param $cart
     * @param $shipping_cost Shipping cost that is calculated using price ranges set for the carrier in back-office
     * @param $products
     * @return float
     */
    public function getPackageShippingCost($cart, $shipping_cost, $products){
        return (float) $shipping_cost;
    }

    /**
     * Recalculates shipping cost. Called if need_range=1 AND method getPackageShippingCost not is_callable.
     * Abstract function, must be implemented in CarrierModule class
     * @param $cart
     * @param $shipping_cost Shipping cost that is calculated using price ranges set for the carrier in back-office
     * @return float
     */
    public function getOrderShippingCost($cart, $shipping_cost) {
        return (float) $shipping_cost;
    }

    /**
     * Recalculates shipping cost. Called if need_range=0 (else).
     * Use external services to calculate shipping costs
     * Abstract function, must be implemented in CarrierModule class
     * @param $cart
     * @return float
     */
    public function getOrderShippingCostExternal($cart) {
        return (float) 12;
    }

    /**
     * Installs a module carrier
     * @param array $carrierProperties
     * @return bool|int Returns installed carrier ID or false if failed to install
     */
    public function installModuleCarrier($carrierProperties) {

        /* Carrier() object properties:

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

        // Example of $carrierProperties

        $properties = array(
            'name'                 => 'LP Express 24 pristatymas į dydžio S dėžę siuntų terminale',
            // Convert 'delay' into a language array
            'delay'                => 'Pristatymas per 1-2  d.d. Maksimalūs matmenys 610mm × 80mm × 350mm.',
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
            'max_weight'           => 30
            'max_width'            => 61
            'max_depth'            => 35
            'max_height'           => 8,

            'img'                  => dirname(__FILE__).'/img/lpexpress24.jpg',
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
            ),
        );
        */

        $carrier = new Carrier();
        $carrier->hydrate($carrierProperties);

        if ($carrier->add())
        {
            $id_carrier = (int) $carrier->id;

            // Assign carrier to all groups
            $groupIDs = MyTools::getGroupsIDs();
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

