<?php
  require_once("../required.php");

  //ensure everything is defined && setted
  if(!isset($_POST["email"]) || empty(trim($_POST["email"])))
    exit(json_encode(["status" => 1, "error" => "Email invalide ou inexistant"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["list"]) || empty(trim($_POST["list"])))
    exit(json_encode(["status" => 1, "error" => "Liste invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["asso"]) || empty(trim($_POST["asso"])))
    exit(json_encode(["status" => 1, "error" => "Asso invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["newMail"]) || empty(trim($_POST["newMail"])))
    exit(json_encode(["status" => 1, "error" => "Nouvelle adresse email invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["isAdmin"]))
    exit(json_encode(["status" => 1, "error" => "Admin state invalide ou inexistant"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["canGoThroughModeration"]))
    exit(json_encode(["status" => 1, "error" => "goThroughModeration state invalide ou inexistant"], JSON_UNESCAPED_UNICODE));

  //escape everything + check if setted properly
  $email = htmlspecialchars(trim($_POST["email"]));
  $list = htmlspecialchars(trim($_POST["list"]));
  $asso = htmlspecialchars(trim($_POST["asso"]));
  $newMail = htmlspecialchars(trim($_POST["newMail"]));
  $isAdmin = (bool) $_POST["isAdmin"];
  $canGoThroughModeration = (bool) $_POST["canGoThroughModeration"];

  //Make sure email is valid
  if (!filter_var($newMail, FILTER_VALIDATE_EMAIL))
    exit(json_encode(["status" => 1, "error" => "Nouvelle adresse email invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!is_bool($isAdmin))
    exit(json_encode(["status" => 1, "error" => "Admin state invalide"], JSON_UNESCAPED_UNICODE));

  if(!is_bool($canGoThroughModeration))
    exit(json_encode(["status" => 1, "error" => "goThroughModeration state invalide"], JSON_UNESCAPED_UNICODE));

  //Get everything before the @
  $listPart = preg_replace("/" . SUFFIXE_MAIL . "/", "", $list);

  //Check permissions (here we make sure that user is part of restricted)
  $isBureauRestreint = false;
  foreach ($assosAdminPortail as $key => $ml) {
    if($ml["login"] == $asso)
      $isBureauRestreint = true;
  }
  if(!$isBureauRestreint)
    exit(json_encode(["status" => 1, "error" => "Vous n'avez pas les droits nécessaires pour effectuer cette action"], JSON_UNESCAPED_UNICODE));

  $user = $portailManager->getPortail(PORTAIL_API_URL . "/users/" . $newMail, $appAccessToken);
  $isPortail = (isset($user["message"]) ? false : true);

  // If email does not change, only update permissions
  if($email == $newMail) {
    if($isPortail)
      $permissionsManager->update($email, $listPart, $isAdmin, $canGoThroughModeration);
    exit(json_encode(["status" => 0, "success" => "Permissions modifées avec succès", "data" => ["isPortail" => $isPortail]], JSON_UNESCAPED_UNICODE));
  }

  // Else, try to add new mail
  $statusDel = $statusAdd = false;
  try {
    $statusAdd = $sympaManager->add($list, $newMail, true, $asso . SUFFIXE_MAIL);
  } catch (SoapFault $ex) {
    exit(json_encode(["status" => 1, "error" => ("$ex->faultstring, détail : " . utf8_decode($ex->detail) . " ($ex->faultcode)")], JSON_UNESCAPED_UNICODE));
  }

  //Do not add permissions if it's a redirection
  $permissionsManager->delete($email, $listPart);
  if(!preg_match("/(-bounce@)/", $list) && $isPortail)
    $permissionsManager->add($newMail, $listPart, $isAdmin, $canGoThroughModeration);

  try {
    //Then unsubscribe old one if subscribing the new mail worked
    $statusDel = $sympaManager->del($list, $email, true, $asso . SUFFIXE_MAIL);
  } catch (SoapFault $ex) {
    exit(json_encode(["status" => 1, "error" => ("$ex->faultstring, détail : " . utf8_decode($ex->detail) . " ($ex->faultcode)")], JSON_UNESCAPED_UNICODE));
  }

  if($statusDel && $statusAdd)
    exit(json_encode(["status" => 0, "success" => "Email modifé avec succès", "data" => ["isPortail" => $isPortail]], JSON_UNESCAPED_UNICODE));
  else
    exit(json_encode(["status" => 1, "error" => "Echec lors de la mise à jour de l'email (conseil: rafraichir la page)"], JSON_UNESCAPED_UNICODE));
