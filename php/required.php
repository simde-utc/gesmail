<?php
  require_once("env.php");
  session_start();

  //Connect to db
  $db = new PDO("mysql:host=localhost;dbname=mails;charset=utf8", USER_DB, PASSWD_DB); //best password
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Include Oauth Php client
  require_once($_SERVER["DOCUMENT_ROOT"] . "/agniacum/vendor/autoload.php");

  // Define a few constants
  const PORTAIL_API_URL = ASSO_SERV_URL . "/api/v1";
  const AUTOMATICSUFFIX = ["bureau", "bureaurestreint", "com", "info", "log", "partenariat", "anim"];

  //Oauth user provider --> portail des assos
  $oauthProvider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => PORTAIL_CLIENT_ID,
    'clientSecret'            => PORTAIL_CLIENT_PASSWD,
    'redirectUri'             => PORTAIL_RETURN_URI,
    'scopes'                  => "user-get-info user-get-assos user-get-roles",
    'urlAuthorize'            => ASSO_SERV_URL . "oauth/authorize",
    'urlAccessToken'          => ASSO_SERV_URL . "oauth/token",
    'urlResourceOwnerDetails' => ASSO_SERV_URL . "api/v1/user"
  ]);

  //OauthClient provider --> portail des assos (this one is for the application itself)
  $oauthClientProvider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => PORTAIL_CLIENT_ID,
    'clientSecret'            => PORTAIL_CLIENT_PASSWD,
    'redirectUri'             => PORTAIL_RETURN_URI,
    'scopes'                  => "client-get-users-active",
    'urlAuthorize'            => ASSO_SERV_URL . "oauth/authorize",
    'urlAccessToken'          => ASSO_SERV_URL . "oauth/token",
    'urlResourceOwnerDetails' => ASSO_SERV_URL . "api/v1/user"
  ]);

  //include php classes
  $classesPath = $_SERVER["DOCUMENT_ROOT"] . "/agniacum/php/classes/";
  require($classesPath . "PortailManager.class.php");
  require($classesPath . "SympaManager.class.php");
  require($classesPath . "PermissionsManager.class.php");
  require($classesPath . "PermissionsListManager.class.php");

  //Declare managers
  $portailManager = new PortailManager($oauthProvider, ASSO_SERV_URL);
  $sympaManager = new SympaManager(SERV_SOAP_SYMPA, SYMPA_REMOTE_APP_USER, SYMPA_REMOTE_APP_PASSWD);
  $permissionsManager = new PermissionsManager($db);
  $permissionsListManager = new PermissionsListManager($db);

  //If no access token, get one
  if(isset($_SESSION["access_token"]) && !empty($_SESSION["access_token"])) {
    $accessToken = $portailManager->getAccessTokenOrRenew($_SESSION["access_token"]);
  } elseif($_SERVER['PHP_SELF'] != "/agniacum/retour_portail_oauth.php") { //If user is connecting
    $authorizationUrl = $portailManager->getAuthUrl();
    header('Location: ' . $authorizationUrl);
  }

  // Get the access token for the application
  try {
      $appAccessToken = $oauthClientProvider->getAccessToken('client_credentials');
  } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
      // Failed to get the access token
      exit($e->getMessage());
  }

  // if user is connected, get his personal infos + all lists where he has rights / is a subscriber
  if(isset($accessToken) && !empty($accessToken)) {
    // Get personnal info
    $resourceOwner = $portailManager->_provider->getResourceOwner($accessToken)->toArray();

    //Get all user's assos
    $userAssos = $portailManager->getPortail(PORTAIL_API_URL . "/user/assos/", $accessToken);

    //Get all possible roles (to check privileges on each),
    $roles = $portailManager->getPortail(PORTAIL_API_URL . "/roles/", $accessToken);
    $bureauRestreintRoles = [];
    $postesAutoRoles = [];
    foreach ($roles as $key => $role) {
      if($role["position"] <= 10 && !is_null($role["position"]))
        $bureauRestreintRoles[$role["id"]] = $role;
      if($role["owned_by"]["model"] != "group")
        $postesAutoRoles[$role["id"]] = $role;
    }

    // All assos where user is bureau restreint
    $assosAdminPortail = [];
    $assosPosteAutoPortail = [];
    foreach ($userAssos as $key => $asso) {
      if(array_key_exists($asso["pivot"]["role_id"], $bureauRestreintRoles))
        $assosAdminPortail[$asso["login"]] = $asso;
      else if(array_key_exists($asso["pivot"]["role_id"], $postesAutoRoles))
        $assosPosteAutoPortail[$asso["login"]] = $asso;
    }

    // All lists on which user is admin and separate those where he is not member of corresponding asso
    $assosAdminSympa = array();
    $assosOnlySubOrAdmin = array();
    foreach ($permissionsManager->getWhereAdmin($resourceOwner["email"]) as $key => $ml) {
      preg_match("/^(\w)*/", $ml["list"], $assoName);
      if(array_key_exists($assoName[0], array_merge($assosAdminPortail, $assosPosteAutoPortail)))
        $assosAdminSympa[$assoName[0]][] = $ml["list"] . SUFFIXE_MAIL;
      else
        $assosOnlySubOrAdmin[$assoName[0]]["admin"][] = $ml["list"] . SUFFIXE_MAIL;
    }

    //Get all lists of user, remove those where he only is admin and separate those where he is not member of corresponding asso
    $assosSubSympa = [];
    $allListsUser = $sympaManager->lists($resourceOwner["email"]);
    foreach ($allListsUser as $key => $list) {
      preg_match("/^(\w)*/", $list->listAddress, $assoName);
      if($list->isSubscriber) //Anciennement if(!$list->isEditor), en test
        if(array_key_exists($assoName[0], array_merge($assosAdminPortail, $assosPosteAutoPortail)))
          $assosSubSympa[$assoName[0]][] = $list->listAddress;
        else
          $assosOnlySubOrAdmin[$assoName[0]]["subscriber"][] = $list->listAddress;
    }
  }
