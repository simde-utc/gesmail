<?php
  if(!isset($_GET["target"]) || empty($_GET["target"]))
    die();

  if(!isset($_GET["range"]) || empty($_GET["range"]) || $_GET["range"] != "assostous")
    die();
  // Include Oauth Php client
  require_once("../../vendor/autoload.php");
  require_once("../env.php");
  const PORTAIL_API_URL = ASSO_SERV_URL . "/api/v1";

  //OauthClient provider --> portail des assos
  $oauthClientProvider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => PORTAIL_CLIENT_ID,
    'clientSecret'            => PORTAIL_CLIENT_PASSWD,
    'redirectUri'             => PORTAIL_CLIENT_RETURN_URI,
    'scopes'                  => "",
    'urlAuthorize'            => ASSO_SERV_URL . "oauth/authorize",
    'urlAccessToken'          => ASSO_SERV_URL . "oauth/token",
    'urlResourceOwnerDetails' => ASSO_SERV_URL . "api/v1/user"
  ]);

  // Get the access token for the application
  try {
    $appAccessToken = $oauthClientProvider->getAccessToken('client_credentials');
  } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    // Failed to get the access token
    exit($e->getMessage()); //TODO: Gérer ces erreurs
  }

  //Is asso ok ?
  $request = $oauthClientProvider->getAuthenticatedRequest(
    'GET', //Protocol
    PORTAIL_API_URL . "/assos/" . $_GET["target"], //Url to call
    $appAccessToken,
    !isset($params) ? [] : $params //parameters (not mendatory)
  );
  $assotarget = $oauthClientProvider->getParsedResponse($request); //Let's go !

  //Is asso ok ?
  $request = $oauthClientProvider->getAuthenticatedRequest(
    'GET', //Protocol
    PORTAIL_API_URL . "/assos/", //Url to call
    $appAccessToken,
    !isset($params) ? [] : $params //parameters (not mendatory)
  );
  $allAssos = $oauthClientProvider->getParsedResponse($request); //Let's go !

  if(isset($assotarget["message"]))
    die();

  foreach($allAssos as $asso) {
    if($asso["parent"]["login"] != $assotarget["login"])
      continue;
    echo $asso["login"] . SUFFIXE_MAIL  . "\n";
  }
?>

