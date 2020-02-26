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
    'redirectUri'             => PORTAIL_RETURN_URI,
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

  //Is role ok ?
  $allroles = [
    "bureaurestreint" => ["a347df90-ecf5-11e9-a1d3-5b039a7c2310", "a35ec3f0-ecf5-11e9-997e-7d44387c7b6c", "a3813a00-ecf5-11e9-9098-57747bda2c15", "a3b88080-ecf5-11e9-b25b-0fb45927582b"],
    "bureau" => ["a347df90-ecf5-11e9-a1d3-5b039a7c2310", "a35ec3f0-ecf5-11e9-997e-7d44387c7b6c", "a3813a00-ecf5-11e9-9098-57747bda2c15", "a3b88080-ecf5-11e9-b25b-0fb45927582b", "a39d2db-ecf5-11e9-99b7-d9227bbc9e07", "a3d5a1b0-ecf5-11e9-8f73-859f26d88a66", "a3faf580-ecf5-11e9-b70e-b9fb92994793"],
    "com" => ["a4b2de80-ecf5-11e9-bb2d-a1d30b7fed65", "a45e71d0-ecf5-11e9-96e7-4946c92e5919"],
    "info" => ["a424c190-ecf5-11e9-ba66-b1c46b164afb", "a4467d80-ecf5-11e9-aec0-63e9012f6896"],
    "anim" => ["a4caac60-ecf5-11e9-913c-9bfb38486500", "a4e90340-ecf5-11e9-93cf-d3ab10f5b0a6"],
    "partenariat" => ["a4fe4380-ecf5-11e9-b0e5-cd3458b2d86f", "a521b440-ecf5-11e9-8b3e-1b6708f52286"],
    "log" => ["a54ad760-ecf5-11e9-ae1e-c5a1c871ed1d", "a56e4540-ecf5-11e9-b84c-a199c8cf06ac"],
  ];


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
