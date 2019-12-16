<?php
  require_once("../required.php");

  //ensure everything is defined && setted
  if(!isset($_POST["email"]) || empty(trim($_POST["email"])))
    exit(json_encode(["status" => 1, "error" => "Email vide ou inexistant"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["list"]) || empty(trim($_POST["list"])))
    exit(json_encode(["status" => 1, "error" => "Liste invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["asso"]) || empty(trim($_POST["asso"])))
    exit(json_encode(["status" => 1, "error" => "Asso invalide ou inexistante"], JSON_UNESCAPED_UNICODE));


  //escape everything + check if setted properly
  $email = htmlspecialchars(trim($_POST["email"]));
  $list = htmlspecialchars(trim($_POST["list"]));
  $asso = htmlspecialchars(trim($_POST["asso"]));

  //Get everything before the @
  $listPart = preg_replace("/" . SUFFIXE_MAIL . "/", "", $list);

  //Make sure email is valid
  if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    exit(json_encode(["status" => 1, "error" => "Format d'email incorrect"], JSON_UNESCAPED_UNICODE));

  //Check permissions (here we make sure that user is admin on the list)
  $admPerms = $permissionsManager->get($resourceOwner["email"], $listPart);
  if(!isset($admPerms["admin"]) || $admPerms["admin"] != true)
    exit(json_encode(["status" => 1, "error" => "Vous devez être administrateur pour effectuer cette action.."], JSON_UNESCAPED_UNICODE));

  // Is user a portail one ? (if not ==> no permissions)
  $user = $portailManager->getPortail(PORTAIL_API_URL . "/users/" . $email, $appAccessToken);
  $isPortail = (isset($user["message"]) ? false : true);

  try {
    //Add the mail to the list
    $statusAdd = $sympaManager->add($list, $email, true, $asso . SUFFIXE_MAIL);
  } catch (SoapFault $ex) {
    exit(json_encode(["status" => 1, "error" => ("$ex->faultstring, détail : " . utf8_decode($ex->detail) . " ($ex->faultcode)")], JSON_UNESCAPED_UNICODE));
  }

  //Do not add any permissions if it's a redirection
  if(!preg_match("/(-bounce@)/", $list) && $isPortail)
    $permissionsManager->add($email, $listPart, 0, 0);

  if($statusAdd)
    exit(json_encode(["status" => 0, "success" => "Email ajouté avec succès", "data" => ["isPortail" => $isPortail]], JSON_UNESCAPED_UNICODE));
  else
    exit(json_encode(["status" => 1, "error" => "Echec de l'ajout"], JSON_UNESCAPED_UNICODE));
