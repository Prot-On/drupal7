<?php
namespace Drupal\prot_on\OAuth;

use Drupal\prot_on\Util as Util;

//error_reporting(E_ERROR);

require_once('PHP-OAuth2/Client.php');
require_once('PHP-OAuth2/GrantType/IGrantType.php');
require_once('PHP-OAuth2/GrantType/AuthorizationCode.php');
require_once('PHP-OAuth2/GrantType/RefreshToken.php');

class OAuth {
    
    const AUTHORIZATION_ENDPOINT	= '/external/oauth/authorize';
    const TOKEN_ENDPOINT			= '/external/oauth/token';
	
    private static function OAuth($redirect_url){
    	
    	if(!empty($_SESSION['protOnOAuth']['access_token'])){
    		return;
    	}
    	 
    	define("PROTON_URL", variable_get('prot_on_url', ""));
    	define("PROTON_OAUTH_CLIENT_ID", variable_get('prot_on_dnd_client_id', ""));
    	define("PROTON_OAUTH_SECRET", variable_get('prot_on_dnd_secret', ""));
    	define("REDIRECT_URL", $redirect_url!=NULL ? $redirect_url : self::getBaseRedirectUrl());
    	 
    	$client = new \OAuth2\Client(PROTON_OAUTH_CLIENT_ID, PROTON_OAUTH_SECRET, \OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
    	
    	$redirect_url = REDIRECT_URL;
    	
    	if(isset($_GET['uid']) && self::isOAuthTokens($_GET['uid'])){
    		$params = array('refresh_token' => $_SESSION['protOnOAuth']['refresh_token'], 'redirect_uri' => $redirect_url);
    		$response = $client->getAccessToken(PROTON_URL.self::TOKEN_ENDPOINT, \OAuth2\Client::GRANT_TYPE_REFRESH_TOKEN, $params);
    		$token = self::parseOAuthTokenResponse($response);
    		 
    		$_SESSION['protOnOAuth'] = $token;
    		 
    		$pest = Util::getPest(false);
    		$pest->setupAuth($token['access_token'], '', 'bearer');
    		try {
    			$thing = $pest->get('/users/userIdentity');
    		} catch (\Pest_Exception $e) {
    			echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
    			die();
    		}
    		 
    		$info = json_decode($thing, true);
    		$_SESSION['protOnUser'] = $info;
    		
    		header('Location: ' . $redirect_url);
    	} elseif (isset($_GET['code'])) {
    		$params = array('code' => $_GET['code'], 'redirect_uri' => $redirect_url);
    		$response = $client->getAccessToken(PROTON_URL.self::TOKEN_ENDPOINT, \OAuth2\Client::GRANT_TYPE_AUTH_CODE, $params);
    		$token = self::parseOAuthTokenResponse($response);
    			
    		$_SESSION['protOnOAuth'] = $token;
    			
    		$pest = Util::getPest(false);
    		$pest->setupAuth($token['access_token'], '', 'bearer');
    		try {
    			$thing = $pest->get('/users/userIdentity');
    		} catch (\Pest_Exception $e) {
    			echo 'Excepcion capturada: ',  $e->getMessage(), "\n";
    			die();
    		}
    	
    		$info = json_decode($thing, true);
    		$_SESSION['protOnUser'] = $info;
    		
    		header('Location: ' . $redirect_url);
    	} else {
    		$endpoint = PROTON_URL.self::AUTHORIZATION_ENDPOINT;
    		
    		$auth_url = $client->getAuthenticationUrl($endpoint, $redirect_url, array());
    		header('Location: ' . $auth_url);
    		die('Redirect');
    	}
    	
    }
    
    public static function OAuthSSO(){
    	
    	$base_redirect_url = self::getBaseRedirectUrl();
    	$redirect_url = $base_redirect_url.'prot_on/sso';
    	
    	self::OAuth($redirect_url);
    	
    }
    
    public static function OAuthWithNid($nid){
    	
    	$base_redirect_url = self::getBaseRedirectUrl();
    	$redirect_url = $nid!=NULL ? $base_redirect_url."/node/".$nid : $base_redirect_url;
    	
    	self::OAuth($redirect_url);
    	
    }
    
    public static function isOAuthTokens($uid){
    	$isTokens = false;
    	$result = db_query("SELECT access_token, refresh_token FROM authmap_prot_on WHERE uid = :uid", array('uid' => $uid));
    	$record = $result->fetchObject();
    	if(!empty($record)){
    		$_SESSION['protOnOAuth']['access_token'] = $record->access_token;
    		$_SESSION['protOnOAuth']['refresh_token'] = $record->refresh_token;
    		$isTokens = true;
    	}
    	return $isTokens;
    }
    
    public static function parseOAuthTokenResponse($response) {
    	if ($response['code'] == 200) {
    		$token = $response['result'];
    		$date = new \DateTime("now");
    		$token['expiration'] = $date->add(new \DateInterval('PT'.$token['expires_in'].'S'));
    		return $token;
    	}
    	return null;
    }
    
    private static function getBaseRedirectUrl(){
    	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    	$redirect_url = $protocol.$_SERVER['HTTP_HOST'].base_path();
    	
    	return $redirect_url;
    }
}
	
?>