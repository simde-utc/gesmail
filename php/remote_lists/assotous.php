<?php
        if(!isset($_GET["asso"]) || empty($_GET["asso"]))
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
                'scopes'                  => "client-get-users-active",
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
                ASSO_SERV_URL . "/api/v1/assos/" . $_GET["asso"], //Url to call
                $appAccessToken,
                !isset($params) ? [] : $params //parameters (not mendatory)
        );
        $assotarget = $oauthClientProvider->getParsedResponse($request); //Let's go !

        if(isset($assotarget["message"]))
                die();

        //get all members of asso and keep only matching roles
        $request = $oauthClientProvider->getAuthenticatedRequest(
                'GET', //Protocol
                PORTAIL_API_URL . "/assos/" . $_GET["asso"] . "/members", //Url to call
                $appAccessToken,
                !isset($params) ? [] : $params //parameters (not mendatory)
        );
        $members = $oauthClientProvider->getParsedResponse($request); //Let's go !
        //var_dump($members);
        foreach($members as $member) {
          
          //Prevent users not accepted in the association from viewing anything (portail will fix this)
          if(!isset($member["pivot"]["validated_by_id"]) || is_null($member["pivot"]["validated_by_id"]))
            continue;

          $request = $oauthClientProvider->getAuthenticatedRequest(
                  'GET', //Protocol
                   PORTAIL_API_URL . "/users/" . $member["id"], //Url to call
                   $appAccessToken,
                   !isset($params) ? [] : $params //parameters (not mendatory)
          );
          $user = $oauthClientProvider->getParsedResponse($request); //Let's go !
          echo $user["email"] . "\n";
      }
?>
