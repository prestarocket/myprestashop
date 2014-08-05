$(document).ready(function(){

    //var $opcPayment = $('#opc_payment_methods');

    /**
     * For standard 5 step PrestaShop checkout
     */
    $('#form').on('submit', function(e){
        var obj = validateLpExpressCarriers();
        if(obj.stopSubmit){
            if (e.preventDefault){
                e.preventDefault();
            } else {
                /* for IE */
                e.returnValue = false;
            }
            alert(obj.msg);
        }

        return !obj.stopSubmit;
    });

    /**
     * For OPC - default PrestaShop one page checkout
     * Also will trigger opc module link clicks, but wrong ID will be retrieved
     */
    $(document).on('click', '#opc_payment_methods .payment_module a', function(e){
        var obj = validateLpExpressCarriers();
        if(obj.stopSubmit){
            if (e.preventDefault){
                e.preventDefault();
            } else {
                e.returnValue = false; /* for IE */
            }
            alert(obj.msg);
        }

        return !obj.stopSubmit;
    });

    /**
     * For OPC - onepagecheckout module by Zelarg
     */
    $('#order-opc .confirm_button').unbind().attr('onclick','').on('click', function(e){

        var valID      = parseInt($('#carrierTable').find('input[name="id_carrier"]').filter(':checked').first().val()),
            strID      = valID.toString(),
            countZeros = parseInt(strID.charAt(0)) + 1,
            cutLength  = strID.length - countZeros - 1;

        var idCarrierSelected = strID.substr(1, cutLength);

        var obj = validateLpExpressCarriers(idCarrierSelected);
        if(obj.stopSubmit){
            if (e.preventDefault){
                e.preventDefault();
            } else {
                e.returnValue = false; /* for IE */
            }
            alert(obj.msg);
        } else {
            paymentModuleConfirm();
        }

        return !obj.stopSubmit;
    });


});

/**
 * Validates LP Express 24 carriers
 * @param idCarrierSelectedVal Carrier ID, processed
 * @returns {{stopSubmit: boolean, msg: string}}
 */
function validateLpExpressCarriers(idCarrierSelectedVal){

    // DOM elements where LP Express 24 carrier IDs are saved
    var $lp_postoffice_id = $('input[name="lp_postoffice_id"]'),
        $lp_terminal_carrier_ids  = $('input[name="lp_terminal_carrier_ids"]');

    // Set Post Office ID
    var lp_postoffice_id = null,
        msgEmptyPostOffice = 'Could not determine a post office. Change ZIP code or select a different delivery method.';
    if($lp_postoffice_id.length){
        lp_postoffice_id = $lp_postoffice_id.val();
        msgEmptyPostOffice = $lp_postoffice_id.data('msg-empty');
    }

    // Set terminals IDs
    var lp_terminal_carrier_ids = [],
        msgEmptyParcelTerminal = 'No parcel terminal selected. Select a parcel terminal or a different delivery method.';
    if($lp_terminal_carrier_ids.length){
        lp_terminal_carrier_ids = $lp_terminal_carrier_ids.val().split(',');
        msgEmptyParcelTerminal = $lp_terminal_carrier_ids.data('msg-empty');
    }

    // Selected carrier ID
    var idCarrierSelected = parseInt($('input.delivery_option_radio').filter(':checked').first().val()),
        agreeTermsExists  = $('input[name="cgv"]').length,
        stopSubmit        = false,
        msg               = '';

    // Overwrite carrier ID if it was set in params
    if(typeof idCarrierSelectedVal != 'undefined'){
        idCarrierSelected = parseInt(idCarrierSelectedVal);
    }

    var isAgreeTerms = false;
    if(!agreeTermsExists){
        isAgreeTerms = true;
    } else {
        isAgreeTerms = $('input[name="cgv"]').prop('checked') ? true : false;
    }

    if(isAgreeTerms){
        if(idCarrierSelected == lp_postoffice_id){

            var $postOffice = $('input[name="lp_postoffice_address"]');
            if($postOffice.length){
                if(!$postOffice.val()){
                    stopSubmit = true;
                }
            } else {
                stopSubmit = true;
            }
            msg = msgEmptyPostOffice;

        } else if (in_array(idCarrierSelected, lp_terminal_carrier_ids)) {

            var $parcelTerminal = $('select[name="lp_terminal_machineid"]');
            if($parcelTerminal.length){
                if(!$parcelTerminal.val()){
                    stopSubmit = true;
                }
            } else {
                stopSubmit = true;
            }
            msg = msgEmptyParcelTerminal;
        }
    }

    return {
        'stopSubmit' : stopSubmit,
        'msg'        : msg
    };
}

/**
 * PHP style in_array function
 * @param needle
 * @param haystack
 * @returns {boolean}
 */
function in_array(needle, haystack){
    for (var i=0; i<haystack.length; i++) {
        if(needle== haystack[i]){
            return true;
        }
    }
    return false;
}

// One step checkout;
function saveTerminal($select){

    var param1Val = $select.val();

    $.ajax({
        dataType : 'json',
        type     : 'POST',
        url      : baseDir + 'modules/mymodule/ajax/saveData.php',
        data: {
            param1  : param1Val
        }
    }).done(function(data){
        $select.val( data.machineid );
        //console.log(data);
    }).fail(function(jqXHR, textStatus) {
        console.log(jqXHR);
        console.log(textStatus);
    });
}