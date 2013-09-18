<?php
if(isset($_GET["token"]) && isset($_GET["PayerID"]))
{
	$con = mysql_connect("localhost", "root", "");
	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}
	
	$db_selected = mysql_select_db("payment", $con);
	
	if (!$db_selected)
	{
		die ("Can\'t use test_db : " . mysql_error());
	}
	
	session_start();
	include_once("config.php");
	
	
				
	//we will be using these two variables to execute the "DoExpressCheckoutPayment"
	//Note: we haven't received any payment yet.
	
	$token = $_GET["token"];
	$playerid = $_GET["PayerID"];
		
	
	$PayPalMode 			= 'sandbox'; // sandbox or live
	$PayPalApiUsername 		= 'anil1_api1.sapovadiya.com'; //PayPal API Username
	$PayPalApiPassword 		= '1370869928'; //Paypal API password
	$PayPalApiSignature 	= 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-AV3Vs83I1ZGRJsIxEBFiSxpR6GOF'; //Paypal API Signature
	$PayPalCurrencyCode 	= 'USD'; //Paypal Currency Code
	
	
	
	/*$padata1 = '&TOKEN='.urlencode($token);
	
	$paypal= new MyPayPal();
	$httpParsedResponseAr = $paypal->PPHttpPost('GetExpressCheckoutDetails', $padata1, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
	
	
	echo "<pre>";print_r($httpParsedResponseAr);die;
	*/
	
	
	
	
	//get session variables
	$total_payment = $_SESSION['total_payment'];
				
	$padata = '&TOKEN='.urlencode($token).
						'&PAYERID='.urlencode($playerid).
						'&PAYMENTACTION='.urlencode("SALE").
						'&AMT='.urlencode($total_payment).
						'&CURRENCYCODE='.urlencode($PayPalCurrencyCode);
	
	//We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
	$paypal= new MyPayPal();
	$httpParsedResponseAr = $paypal->PPHttpPost('DoExpressCheckoutPayment', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
							
	//Check if everything went ok..
	if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
	{
		$transactionID = urlencode($httpParsedResponseAr["TRANSACTIONID"]);
		$nvpStr = "&TRANSACTIONID=".$transactionID;
		$paypal= new MyPayPal();
		$httpParsedResponseAr = $paypal->PPHttpPost('GetTransactionDetails', $nvpStr, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
		
		
		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) 
		{
			/*$to = "ashishgajera011@gmail.com";
			$subject = "My subject";
			$txt = "Hello world!";
			$headers = "From: webmaster@example.com" . "\r\n" .
			"CC: somebodyelse@example.com";
			mail($to,$subject,$txt,$headers);*/
			echo "<h1>Payment Successful</h1>";
			//echo "<pre>";print_r($httpParsedResponseAr);
			foreach($httpParsedResponseAr as  $key => $value)
			{
				echo $key." => ".urldecode($value)."</br>";
			}
			$first_name = mysql_escape_string(urldecode($httpParsedResponseAr['FIRSTNAME']));
			$last_name = mysql_escape_string(urldecode($httpParsedResponseAr['LASTNAME']));
			$trans_type = mysql_escape_string(urldecode($httpParsedResponseAr['TRANSACTIONTYPE']));
			$order_time = mysql_escape_string(urldecode($httpParsedResponseAr['ORDERTIME'])); 
			$currency = mysql_escape_string(urldecode($httpParsedResponseAr['CURRENCYCODE']));
			$email = mysql_escape_string(urldecode($httpParsedResponseAr['EMAIL']));
			$status = mysql_escape_string(urldecode($httpParsedResponseAr['PAYMENTSTATUS']));
			$amount = mysql_escape_string(urldecode($httpParsedResponseAr['AMT']));
			$trans_id = mysql_escape_string(urldecode($httpParsedResponseAr['TRANSACTIONID']));
			$response = json_encode($httpParsedResponseAr);
			$sql = "insert into order_data (first_name,last_name,email,status,amount,currency,transaction_id,trans_type,response,order_time) 
			values ('".$first_name."','".$last_name."','".$email."','".$status."','".$amount."','".$currency."','".$trans_id."','".$trans_type."','".$response."','".$order_time."')";
			$qry = mysql_query($sql,$con);
		} 
		else  
		{
			echo '<div style="color:red"><b>GetTransactionDetails failed:</b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
			echo '<pre>';
			print_r($httpParsedResponseAr);
			echo '</pre>';
		}
	}
	else
	{
		echo '<pre>';
		print_r($httpParsedResponseAr);
		echo '</pre>';
	}
}
?>