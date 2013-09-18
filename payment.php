<?php 
//echo "<pre>";print_r($_POST);die;
session_start();
include_once("config.php");

$PayPalMode 			= 'sandbox'; // sandbox or live
$PayPalApiUsername 		= 'anil1_api1.sapovadiya.com'; //PayPal API Username
$PayPalApiPassword 		= '1370869928'; //Paypal API password
$PayPalApiSignature 	= 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-AV3Vs83I1ZGRJsIxEBFiSxpR6GOF'; //Paypal API Signature
$PayPalCurrencyCode 	= 'USD'; //Paypal Currency Code

// For multiple item 
$each_item_price = $_POST['p_radio'];
$ItemTotalPrice = $_POST['p_radio'];
$total_payment = $_POST['p_radio'];

$item_name = 'T-shirt';

$_SESSION['total_payment'] = $total_payment;

$ItemQty = 1;
		
$j = 0;
$padata = '&PAYMENTACTION=Sale'.                        
		'&ALLOWNOTE=1'.								   
		'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode);
		$padata .= '&L_PAYMENTREQUEST_0_QTY'.$j.'='. urlencode($ItemQty).
		'&L_PAYMENTREQUEST_0_AMT'.$j.'='.urlencode($each_item_price).
		'&L_PAYMENTREQUEST_0_NAME'.$j.'='.urlencode($item_name).
		'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($ItemTotalPrice).	
		'&PAYMENTREQUEST_0_AMT='.urlencode($total_payment). 
		'&RETURNURL='.urlencode('http://localhost/paypal_demo/demo/success_page.php').
		'&CANCELURL='.urlencode('http://localhost/paypal_demo/demo/fail_page.php');
		
	//We need to execute the "SetExpressCheckOut" method to obtain paypal token
	$paypal= new MyPayPal();
	$httpParsedResponseAr = $paypal->PPHttpPost('SetExpressCheckout', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
	
	//Respond according to message we receive from Paypal
	if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
	{
		// If successful set some session variable we need later when user is redirected back to page from paypal. 
	
		if($PayPalMode=='sandbox')
			$paypalmode = '.sandbox';
		else
			$paypalmode = '';
			
		//Redirect user to PayPal store with Token received.
		$paypalurl ='https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$httpParsedResponseAr["TOKEN"].'';
		header('Location: '.$paypalurl);
	}
	else
	{
		//Show error message
		echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
		echo '<pre>';
		print_r($httpParsedResponseAr);
		echo '</pre>';
	}
?>