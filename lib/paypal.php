<?php
if ($this->piVars['external_payment_complete']>0) {
  	// let the thanks rendering run through
  	// otherwise render autosubmit form to paypal
} else {
?>
<form name='paypalform' action="https://www.paypal.com/cgi-bin/webscr" method="post">
<!--form name='paypalform' action="callback.php" method="post"-->
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="upload" value="1" >
<input type="hidden" name="currency_code" value="<?php echo $this->conf['currencyCode']; ?>" >
<input type="hidden" name="lc" value="<?php echo $this->conf['currencyCode']; ?>" >
<input type="hidden" name="business" value="<?php echo $this->conf['payment.']['20.']['paypalEmail']; ?>">

<?php 
//debug($this->basket);
//exit;
$a=1;
foreach ($this->basket as $bK => $bV) {  
?>

<input type="hidden" name="item_name_<?php echo $a; ?>" value="<?php echo $bV['title']; ?>">
<input type="hidden" name="quantity_<?php echo $a; ?>" value="<?php echo $bV['basket_qty']; ?>">
<input type="hidden" name="amount_<?php echo $a; ?>" value="<?php echo sprintf("%01.2f",tx_trade_pricecalc::getPrice($bV,$this->user,$this)); ?>">
<?php 
	$a++;
}
if ($this->order['price_shipping']>0) {
	?>
	<input type="hidden" name="item_name_<?php echo $a; ?>" value="Shipping">
	<input type="hidden" name="amount_<?php echo $a; ?>" value="<?php echo sprintf("%01.2f",$this->order['price_shipping']); ?>">
	<?php 	
	$a++;
}       

if ($this->order['price_processing']>0) {
	?>
	<input type="hidden" name="item_name_<?php echo $a; ?>" value="Processing">
	<input type="hidden" name="amount_<?php echo $a; ?>" value="<?php echo sprintf("%01.2f",$this->order['price_processing']); ?>">
	<?php
	$a++;
}
//debug(array($this->order['price_processing'],$this->order['price_shipping']));
?>


<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="no_note" value="1">
<?php
$names=explode(' ',$this->user['name']);
?>
<input type="hidden" name="first_name" value="<?php  echo $names[0]; ?>">
<input type="hidden" name="last_name" value="<?php  echo $names[1]; ?>">
<input type="hidden" name="address1" value="<?php  echo $this->user['address']; ?>">
<input type="hidden" name="address2" value="">
<input type="hidden" name="email" value="<?php  echo $this->user['email']; ?>">
<input type="hidden" name="city" value="<?php  echo $this->user['city']; ?>">
<input type="hidden" name="state" value="<?php  echo $this->user['state']; ?>">
<input type="hidden" name="zip" value="<?php  echo $this->user['zip']; ?>">
<input type="hidden" name="country" value="AU">
<input type="hidden" name="night_phone_a" value="<?php  echo $this->user['phone']; ?>"> 

<input type="hidden" name="charset" value="utf-8"> 
 
<!-- disabled by leading _ -->
<input type="hidden" name="_image_url" value="https://www.yoursite.com/logo.gif">
<input type="hidden" name="_cancel_return" value="http://cello.homelinux.net/dummy38/index.php?id=141&tx_trade_pi1[cmd]=list&tx_trade_pi1[paypal_order_complete]=44354">
<input type="hidden" name="_undefined_quantity" value="1">
<input type="hidden" name="_custom" value="merchant_custom_value">
<input type="hidden" name="_invoice" value="merchant_invoice_12345">



<input type="submit"  value="Sending your payment request. Please click here if nothing happens in the next 10 seconds."  />
</form>
<script language='javascript'>
document.paypalform.submit(); 
</script>
<?php
	exit;
} 
?>