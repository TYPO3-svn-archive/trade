<?php 
 /* 
    +----------------------------------------------------------------------+ 
    | Copyright (c) 2000 J.A.Greant (zak@nucleus.com)                      | 
    | All rights reserved.                                                 | 
    +----------------------------------------------------------------------+ 
    | Redistribution and use in source and binary forms, with or without   | 
    | modification, is permitted provided that the following conditions    | 
    | are met:                                                             | 
    +----------------------------------------------------------------------+ 
    | Redistributions of source code must retain the above copyright       | 
    | notice, this list of conditions and the following disclaimer.        | 
    |                                                                      | 
    | Redistributions in binary form must reproduce the above copyright    | 
    | notice, this list of conditions and the following disclaimer in the  | 
    | documentation and/or other materials provided with the distribution. | 
    |                                                                      | 
    | Neither the name of the author nor the names of any contributors to  | 
    | this software may be used to endorse or promote products derived     | 
    | from this software without specific prior written permission.        | 
    |                                                                      | 
    | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  | 
    | ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT  | 
    | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    | 
    | FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE       | 
    | AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,           | 
    | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, | 
    | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;     | 
    | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER     | 
    | CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT   | 
    | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN    | 
    | ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE      | 
    | POSSIBILITY OF SUCH DAMAGE.                                          | 
    +----------------------------------------------------------------------+ 
*/  
 
 
class credit_card  {
	function clean_no ($cc_no)	{
		// Remove non-numeric characters from $cc_no 
		return ereg_replace ('[^0-9]+', '', $cc_no); 
	}

	function identify ($cc_no) {
		$cc_no = credit_card::clean_no ($cc_no);

		// Get card type based on prefix and length of card number
		if (ereg ('^4(.{12}|.{15})$', $cc_no))
			return 'Visa';
			if (ereg ('^5[1-5].{14}$', $cc_no))
			return 'Mastercard';
		if (ereg ('^(560|561)', $cc_no))
			return 'Bankcard';
		if (ereg ('^3[47].{13}$', $cc_no))
			return 'American Express';
		if (ereg ('^3(0[0-5].{11}|[68].{12})$', $cc_no))
			return 'Diners Club/Carte Blanche';
		if (ereg ('^6011.{12}$', $cc_no))
			return 'Discover Card';
		if (ereg ('^(3.{15}|(2131|1800).{11})$', $cc_no))
			return 'JCB';
		if (ereg ('^2(014|149).{11})$', $cc_no))
			return 'enRoute';

		return 'unknown';
	}

	function validate ($cc_no) {
		// disable validation
		return true;
		
		// Reverse and clean the number
		$cc_no = strrev (credit_card::clean_no ($cc_no));

		// VALIDATION ALGORITHM 
		// Loop through the number one digit at a time 
		// Double the value of every second digit (starting from the right) 
		// Concatenate the new values with the unaffected digits 
		for ($ndx = 0; $ndx < strlen ($cc_no); ++$ndx) {
			$digits .= ($ndx % 2) ? $cc_no[$ndx] * 2 : $cc_no[$ndx];
		}

		// Add all of the single digits together
		for ($ndx = 0; $ndx < strlen ($digits); ++$ndx) {
			$sum += $digits[$ndx];
		}
		//debug(array('credit card sum',$sum));
		// Valid card numbers will be transformed into a multiple of 10
		return ($sum % 10) ? FALSE : TRUE;
	}

	function check ($cc_no, $month, $year)
	{ 
		$valid = credit_card::validate ($cc_no);
		$type = credit_card::identify ($cc_no);
		$date = credit_card::checkDate ($month, $year);
		$cardnumber=credit_card::clean_no ($cc_no);
		return array ($valid, $type, $date, $cardnumber, 'valid' => $valid, 'type' => $type, 'date' => $date, 'cardnumber' => $cardnumber);
	}
	
	function checkDate ($month, $year) {
		if ( checkdate( $month, 1, $year)) {
			$tstamp=time();
			//debug(array('credit card date',date($tstamp)));
			$tstamp_date=mktime(0, 0, 0, $month+1, 1, $year);
			if ($tstamp < $tstamp_date)
				return true;
		}
		return false;
	}
}
?>