<?php

class MyCarrierModule extends CarrierModule {

    /**
     * @var Automatically assigned by CartController, use when calculating costs
     */
    public $id_carrier;

    /**
     * Everything else is the same as Module class
     */

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

}

