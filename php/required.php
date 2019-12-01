<?php
  //Include all classes, define db, ensure user is connected ....
  session_start();

  //Connect to db
  $db = new PDO("mysql:host=localhost;dbname=mails;charset=utf8", "USER", "PASSWORD"); //best password
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Include Oauth Php client
  require_once($_SERVER["DOCUMENT_ROOT"] . "/agniacum/vendor/autoload.php");

  // Define a few constants
  const ASSO_SERV_URL = "http://localhost:8000/";
  const PORTAIL_API_URL = "http://localhost:8000/api/v1";
  const SERV_SOAP_SYMPA = "https://noeamiot.fr/wws/wsdl";
  const SUFFIXE_MAIL = "@noeamiot.fr";
  const REGEX_LOGINASSO = "/^(\w)*/";

  //Oauth provider --> portail des assos
  $oauthProvider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => '53616d79-206a-6520-7427-61696d652021',
    'clientSecret'            => 'password',
    'redirectUri'             => 'http://localhost/agniacum/retour_portail_oauth.php',
    'scopes'                  => "user-get-info user-get-assos user-get-roles",
    'urlAuthorize'            => ASSO_SERV_URL . "oauth/authorize",
    'urlAccessToken'          => ASSO_SERV_URL . "oauth/token",
    'urlResourceOwnerDetails' => ASSO_SERV_URL . "api/v1/user"
  ]);


  $classesPath = $_SERVER["DOCUMENT_ROOT"] . "/agniacum/php/classes/";
  //include php classes
  require($classesPath . "PortailManager.class.php");
  require($classesPath . "SympaManager.class.php");
  require($classesPath . "PermissionsManager.class.php");

  //Declare classes
  $portailManager = new PortailManager($oauthProvider, ASSO_SERV_URL);
  $sympaManager = new SympaManager(SERV_SOAP_SYMPA, 'USERAPP', 'PASSWORD');
  $permissionsManager = new PermissionsManager($db);

  //If no access token, get one
  if(isset($_SESSION["access_token"]) && !empty($_SESSION["access_token"])) {
    $accessToken = $portailManager->getAccessTokenOrRenew($_SESSION["access_token"]);

  } elseif($_SERVER['PHP_SELF'] != "/agniacum/retour_portail_oauth.php") { //If user is connecting
    $authorizationUrl = $portailManager->getAuthUrl();
    header('Location: ' . $authorizationUrl);
  }

  if(isset($accessToken) && !empty($accessToken)) {
    //Define here elements needed everywhere
    $resourceOwner = $portailManager->_provider->getResourceOwner($accessToken)->toArray();

    //Get all user's assos
    $userAssos = $portailManager->getPortail(PORTAIL_API_URL . "/user/assos/", $accessToken);

    //Get all possible roles (to check privileges on each)
    $roles = $portailManager->getPortail(PORTAIL_API_URL . "/roles/", $accessToken);
    $organizedRoles = [];
    foreach ($roles as $key => $role) {
      if($role["position"] <= 10 && !is_null($role["position"]))
        $organizedRoles[$role["id"]] = $role;
    }

    // All assos where user is restrained
    $assosAdminPortail = [];
    foreach ($userAssos as $key => $asso) {
      if(array_key_exists($asso["pivot"]["role_id"], $organizedRoles))
        $assosAdminPortail[] = $asso;
    }

    // All lists on which user is admin
    $assosAdminSympa = [];
    foreach ($permissionsManager->getWhereAdmin($resourceOwner["email"]) as $key => $ml) {
      preg_match(REGEX_LOGINASSO, $ml["list"], $assoName);
      $assosAdminSympa[] = ["asso" => $assoName[0], "list" => $ml["list"] . SUFFIXE_MAIL];
    }
  }
