<?php
/**
 *  Helpers
 * 
 *  Just a handful of functions I didn't know where else to put.
 * 
 *  fsst_set_domain_unverified
 *  fsst_get_domain_verification_status
 * 
 **/

// Sets the flag indicating that we should show the upgrade page menu item
function fsst_set_domain_unverified($set = true) {
  if($set) {
    set_transient('fsst_domain_unverified', true);
  } else {
    delete_transient('fsst_domain_unverified');
  }
}

function fsst_get_domain_verification_status() {
	return get_transient('fsst_domain_unverified');
}