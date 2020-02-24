<?php
  /* Cette page est appellée lorsque l'utilisateur vient d'autoriser votre apllication sur le serveur des assos
  * Si il refuse d'autoriser votre application il n'arrivera pas sur cette page.
  * Cette page permet de convertir le code renvoyé par oauth2 en access token, qui devra être renseigné pour chaque requête sur le portail
  * Le seul élément à sauvegarder est l'access token. On le sauvegarde dans la session de l'utilisateur et non dans ses cookies car c'est le serveur des assos qui gère
  * la connexion permanente (à la demande de l'utilisateur)
  */

  require_once("php/required.php");

  //Si le state enregistré précedemment est différent ou n'existe pas, soit la session a expiré soit l'utilisateur est malveillant
  if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
      //On détruit le state (on peut tenter de le faire se reconnecter)
      if (isset($_SESSION['oauth2state'])) {
          unset($_SESSION['oauth2state']);
      }

      exit('Invalid state');
  } else {
      //On essaye de convertir le code retourné par oauth en access_token (qui ne sont à stocker que dans la session)
      try {
          // En utilisant la méthode d'autorisation d'oauth2, on génère le token avec le code
          $accessToken = $oauthProvider->getAccessToken('authorization_code', [
              'code' => $_GET['code']
          ]);

          //On sauvegarde l'access token dans la session
          $_SESSION["access_token"] = $accessToken->jsonSerialize();

          //On redirige l'utillisateur
          header("Location: /agniacum/");
          exit;
      } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
          //Une erreur a eu lieu pendant la récupération du token.
          exit($e->getMessage());
      }

  }
?>
