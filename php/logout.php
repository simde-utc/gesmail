<?php
  //Start session, remove session -> Done ! 
  session_start();

  //Remove session
  unset($_SESSION["access_token"]);
  unset($_SESSION["refresh_token"]);

  echo "Vous avez correctement été déconnecté de l'application";
