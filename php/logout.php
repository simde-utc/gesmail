<?php
  require_once("required.php");

  //Remove session
  unset($_SESSION["access_token"]);
  unset($_SESSION["refresh_token"]);

  echo "Vous avez correctement été déconnecté de l'application";
