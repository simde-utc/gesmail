<?php
  if(!isset($_GET["target"]) || empty($_GET["target"]))
    die();

  if(!isset($_GET["range"]) || empty($_GET["range"]))
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

  if(isset($assotarget["message"]))
    die();

  if(!array_key_exists($_GET["range"], $allroles))
    die();

  //get all members of asso and keep only matching roles
  $request = $oauthClientProvider->getAuthenticatedRequest(
    'GET', //Protocol
    PORTAIL_API_URL . "/assos/" . $_GET["target"] . "/members", //Url to call
    $appAccessToken,
    !isset($params) ? [] : $params //parameters (not mendatory)
  );
  $members = $oauthClientProvider->getParsedResponse($request); //Let's go !

  foreach($members as $member) {
    
    //Prevent users not accepted in the association from viewing anything (portail will fix this)
    if(!isset($member["pivot"]["validated_by_id"]) || is_null($member["pivot"]["validated_by_id"]))
      continue;

    if(in_array($member["pivot"]["role_id"], $allroles[$_GET["range"]])) {
      $request = $oauthClientProvider->getAuthenticatedRequest(
        'GET', //Protocol
        PORTAIL_API_URL . "/users/" . $member["id"], //Url to call
        $appAccessToken,
        !isset($params) ? [] : $params //parameters (not mendatory)
      );
      $user = $oauthClientProvider->getParsedResponse($request); //Let's go !
      echo $user["email"] . "\n";
    }
  }
?>
