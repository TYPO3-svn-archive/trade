---------------------------------------------------------------
		card processing 1		
		// save user
		//if (strlen(t3lib_div::GPvar($this->saveUserButtonName]))>0) {
		//	$this->processSaveUser();
		//}
		
		// finalise checkout process and process payment
		// external payment complete
		if ($this->piVars['external_payment_complete']==1&&$this->piVars['cmd']=='checkout') {
			// PATCH STEVER
			$this->piVars['process_finalise_checkout']==1;
			$this->processFinaliseCheckout();
		}
		// cmd can only be set to 'thanks'	 after successful 
		// traversal of tests in TS config for checkout
		if ($this->piVars['finalise_checkout']==1) {
			eval($this->conf['checkout.']['confirm.']['condition']);
			if ($testResult) {
				$this->processFinaliseCheckout();
			} else {
				// TODO friendly output
				$this->errors[]=$this->pi_getLL('lost_session');
				$this->prevCmd='';
			}
		}
		
		
		---------------------------------------------------------------
		card processing 2
		
		

