<?php
//session_start();

$client_id = isset(Yii::$app->cfg->social->google['app']['client_id']) ? Yii::$app->cfg->social->google['app']['client_id'] : '';

$client_secret = isset(Yii::$app->cfg->social->google['app']['client_secret']) ? Yii::$app->cfg->social->google['app']['client_secret'] : '';

$developer_key = isset(Yii::$app->cfg->social->google['app']['developer_key']) ? Yii::$app->cfg->social->google['app']['developer_key'] : 'API_KEY';

$google_client_id 		= $client_id;
$google_client_secret 	= $client_secret;
$google_redirect_url 	= \yii\helpers\Url::home(true) .'glogin' ; //path to your script  
$google_developer_key 	= $developer_key;
require_once 'Google/Google_Client.php';
require_once 'Google/contrib/Google_Oauth2Service.php';
//echo 'sss';
//exit;  
$gClient = new Google_Client();
$gClient->setApplicationName(Yii::$app->t->translate('label_login_by_google'));
$gClient->setClientId($google_client_id);
$gClient->setClientSecret($google_client_secret);
$gClient->setRedirectUri($google_redirect_url);
$gClient->setDeveloperKey($google_developer_key);

// view($client_id,1,1);

$google_oauthV2 = new Google_Oauth2Service($gClient);

//If user wish to log out, we just unset Session variable 
if (isset($_REQUEST['reset'])) 
{ 
  unset($_SESSION['token']);
  $gClient->revokeToken();
  //header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL)); //redirect user back to page
  header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL).'?site_url_redirect='.getParam('site_url_redirect'));
}
//If code is empty, redirect user to google authentication page for code.
//Code is required to aquire Access Token from google
//Once we have access token, assign token to session variable
//and we can redirect user back to page and login.
if (isset($_GET['code'])) 
{ 
	$gClient->authenticate($_GET['code']);
	$_SESSION['token'] = $gClient->getAccessToken();
	header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL).'?site_url_redirect='.getParam('site_url_redirect'));
	return;
}


if (isset($_SESSION['token'])) 
{ 
	$gClient->setAccessToken($_SESSION['token']);
}
 
if ($gClient->getAccessToken()) 
{
	  //For logged in user, get details from google using access token
	  $user 				= $google_oauthV2->userinfo->get();
	  $user_id 				= $user['id'];
	  $user_name 			= filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
	  $email 				= filter_var($user['email'], FILTER_SANITIZE_EMAIL);
	  //$profile_url 			= filter_var($user['link'], FILTER_VALIDATE_URL);
	  $profile_image_url 	= filter_var($user['picture'], FILTER_VALIDATE_URL);
	  $personMarkup 		= "$email<div><img src='$profile_image_url?sz=50'></div>";
	  $_SESSION['token'] 	= $gClient->getAccessToken();
}
else {
	//For Guest user, get google login url
	$authUrl = $gClient->createAuthUrl();
}

if(isset($authUrl)) //user is not logged in, show login button 
{
    
	header("Location: ".$authUrl.'&site_url_redirect='.getParam('site_url_redirect'));
} 
else // user logged in 
{
	if(is_array($user) && !empty($user)){
		$mem = Yii::$app->member->model->findByUsername2($user['email']);
		if(!empty($mem)){
			$f = [
					'google_id'=>$user['id']
			];
 
			Yii::$app->member->login($mem);
 
			
		}else{
		    $mem = Yii::$app->member->model->registerFromGoogle($user);
			Yii::$app->member->login($mem);
		}
		
			header("Location:/member");

	}
}
?>