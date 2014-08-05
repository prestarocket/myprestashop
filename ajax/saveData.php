<?php

// Extra dots because ajax file is in ajax/ folder
require_once(dirname(__FILE__).'../../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../../init.php');

/* Include model class file if needed */
require_once(dirname(__FILE__).'../../models/MyObjectModel.php');

$response = null;
$id_cart = (int) Context::getContext()->cart->id;

$id_obj = MyObjectModule::getIdByCart($id_cart);

$data = array(
    'param1' => 'val1',
);

$obj = new LpExpressCarrierData($id_obj);
$obj->hydrate($data);
if($obj->save()){
    $response = array(
        'response' => 'success',
    );
} else {
    $response = array(
        'response' => 'error',
        'message'  => 'Could not save',
    );
}

// Return current value as it is in Database and show it in page
$response['value'] = MyObjectModel::getValue();

echo json_encode($response);

die();