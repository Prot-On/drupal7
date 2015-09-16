<?php
namespace Drupal\prot_on;

include_once(drupal_realpath(drupal_get_path('module', 'prot_on'))."/lib/Pest/Pest.php");

class BearerPest extends \Pest {

	protected $bearerHeader;
	
	public function setupAuth($user, $pass, $auth = 'basic'){
		if ($auth == 'bearer') {
			$this->bearerHeader = 'Authorization: Bearer ' . $user;
		} else {
			parent::setupAuth($user, $pass, $auth);
		}
	}
	
	protected function prepHeaders($headers) {
		$headers = parent::prepHeaders($headers);
		if (!empty($this->bearerHeader)) {
			$headers[] = $this->bearerHeader;
		}
		return $headers;
	}

}

?>
