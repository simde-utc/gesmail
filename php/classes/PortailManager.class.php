<?php

  class PortailManager {
    public $_provider;
    public $_portailURL;

    function __construct($provider, $portailUrl) {
      $this->_provider = $provider;
      $this->_portailURL = $portailUrl;
    }

    //Ensure we have a valid access token
    public function getAccessTokenOrRenew($sessAccessToken) {
      $accessToken = new \League\OAuth2\Client\Token\AccessToken($sessAccessToken);
      if ($accessToken->hasExpired()) {
        //If expired, get a new access token with the refresh token
        $newAccessToken = $this->_provider->getAccessToken('refresh_token', [
            'refresh_token' => $accessToken->getRefreshToken()
        ]);

        // Delete old access token and update with the new one
        $accessToken = $newAccessToken;
        $_SESSION["access_token"] = $newAccessToken->jsonSerialize();
      }
      return $accessToken;
    }

    //Return a Url to the portail needed to login the user
    public function getAuthUrl() {
      //Generate login url
      $authorizationUrl = $this->_provider->getAuthorizationUrl();

      //Generate a token to prevent CSRF
      $_SESSION['oauth2state'] = $this->_provider->getState();
      return $authorizationUrl;
    }

    // Make a get call on the portail
    public function getPortail($route, $token, $params = null) {
      $request = $this->_provider->getAuthenticatedRequest(
        'GET', //Protocol
        $route, //Url to call
        $token,
        is_null($params) ? [] : $params //parameters (not mendatory)
      );
      return $this->_provider->getParsedResponse($request);
    }
  }
