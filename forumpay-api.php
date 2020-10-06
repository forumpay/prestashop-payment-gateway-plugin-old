<?php

include dirname(__FILE__) . '/../../config/config.inc.php';
include dirname(__FILE__) . '/../../init.php';
include dirname(__FILE__) . '/forumpay.php';

$forumpay = new Forumpay();
$pos_id = Configuration::get('FORUMPAY_POSID');

if ($_REQUEST['act'] == 'webhook') {
    $ipnres = file_get_contents('php://input');
    $ipnrear = json_decode($ipnres, true);

    if (!$ipnrear) {
        echo "Invalid JSON body payload.";
        exit;
    }

    $apiurl = 'https://pay.limitlex.com/api/v2/CheckPayment/';
    $currency_code = $currency->iso_code;

    $ForumPayParam = array(
        "pos_id" => $pos_id,
        "payment_id" => $ipnrear['payment_id'],
        "address" => $ipnrear['address'],
        "currency" => $ipnrear['currency'],
    );

    $payres = $forumpay->api_call($apiurl, $ForumPayParam);
    $data['status'] = $payres['status'];

    if (($payres['status'] == 'Confirmed') || ($payres['status'] == 'Cancelled')) {
        $payres['orderid'] = $payres['reference_no'];
        $forumpay->returnsuccess($payres);
    }

    echo "OK";
    exit;
}

$order_id = Context::getContext()->cookie->forumpaycartid;
$order = new Order((int) Order::getOrderByCartId($order_id));

if ($_REQUEST['act'] == 'getrate') {
    $apiurl = 'https://pay.limitlex.com/api/v2/GetRate/';

    $currency = new Currency((int) ($order->id_currency));
    $currency_code = $currency->iso_code;

    $total = $order->total_paid;

    $ForumPayParam = array(
        "pos_id" => $pos_id,
        "invoice_currency" => $currency_code,
        "invoice_amount" => $total,
        "currency" => $_REQUEST['currency'],
        "reference_no" => $order->id,
    );

    $payres = $forumpay->api_call($apiurl, $ForumPayParam);

    if ($payres['err']) {
        $data['errmgs'] = $payres['err'];
        $data['status'] = 'No';
    } else {
        $data['status'] = 'Yes';
        $data['ordamt'] = $payres['invoice_amount'] . ' ' . $payres['invoice_currency'];
        $data['exrate'] = '1 ' . $payres['currency'] . ' = ' . $payres['rate'] . ' ' . $payres['invoice_currency'];
        $data['examt'] = $payres['amount_exchange'];
        $data['netpfee'] = $payres['network_processing_fee'];
        $data['amount'] = $payres['amount'] . ' ' . $payres['currency'];
        $data['payment_id'] = $payres['payment_id'];
        $data['txfee'] = $payres['fast_transaction_fee'] . ' ' . $payres['fast_transaction_fee_currency'];
        $data['waittime'] = $payres['wait_time'];
        $data['orderid'] = $order->id;
    }

    echo json_encode($data, true);
}

if ($_REQUEST['act'] == 'getqr') {

    $apiurl = 'https://pay.limitlex.com/api/v2/StartPayment/';

    $currency = new Currency((int) ($order->id_currency));
    $currency_code = $currency->iso_code;

    $total = $order->total_paid;

    $ForumPayParam = array(
        "pos_id" => $pos_id,
        "invoice_currency" => $currency_code,
        "invoice_amount" => $total,
        "currency" => $_REQUEST['currency'],
        "reference_no" => $order->id,
    );

    $payres = $forumpay->api_call($apiurl, $ForumPayParam);

    if ($payres['err']) {
        $data['errmgs'] = $payres['err'];
        $data['status'] = 'No';
    } else {
        $data['status'] = 'Yes';
        $data['ordamt'] = $payres['invoice_amount'] . ' ' . $payres['invoice_currency'];
        $data['exrate'] = '1 ' . $payres['currency'] . ' = ' . $payres['rate'] . ' ' . $payres['invoice_currency'];
        $data['examt'] = $payres['amount_exchange'];
        $data['netpfee'] = $payres['network_processing_fee'];

        $data['addr'] = $payres['address'];
        $data['qr_img'] = $payres['qr_img'];
        $data['amount'] = $payres['amount'] . ' ' . $payres['currency'];
        $data['payment_id'] = $payres['payment_id'];
        $data['txfee'] = $payres['fast_transaction_fee'] . ' ' . $payres['fast_transaction_fee_currency'];
        $data['waittime'] = $payres['wait_time'];
        $data['orderid'] = $order->id;
    }
    echo json_encode($data, true);
}
//status check
if ($_REQUEST['act'] == 'getst') {

    $apiurl = 'https://pay.limitlex.com/api/v2/CheckPayment/';

    $currency_code = $currency->iso_code;
    $total = $order->total_paid;

    $ForumPayParam = array(
        "pos_id" => $pos_id,
        "payment_id" => $_REQUEST['paymentid'],
        "address" => $_REQUEST['addr'],
        "currency" => $_REQUEST['currency'],
    );

    $payres = $forumpay->api_call($apiurl, $ForumPayParam);

    $data['status'] = $payres['status'];
    $data['orderid'] = $order->id;

    if (($payres['status'] == 'Confirmed') || ($payres['status'] == 'Cancelled')) {
        $payres['orderid'] = $order->id;
        $rurl = $forumpay->returnsuccess($payres);

        $data['status'] = 'Yes';
        $data['purl'] = $rurl;
    }

    echo json_encode($data, true);
}

exit;
