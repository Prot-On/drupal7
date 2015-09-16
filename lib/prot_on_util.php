<?php

namespace Drupal\prot_on;

class Util {
	
	public static function getPest($auth = true) {
		$pest = new BearerPest(variable_get('prot_on_dnd_url', "") . '/rest-api/api');
		if ($auth) {
			if (self::getPassword() != null) {
				$pest->setupAuth(self::getUser(), self::getPassword());
			} else {
				$token = self::getToken();
				if (empty($token)) {
					throw new \Exception("No authentication found");
				}
				$pest->setupAuth($token, '', 'bearer');
			}
		}
		return $pest;
	}    
    
}

?>