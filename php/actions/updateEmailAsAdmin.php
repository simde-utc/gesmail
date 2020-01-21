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

  if(!isset($_POST["canGoThroughModeration"]))
    exit(json_encode(["status" => 1, "error" => "goThroughModeration state invalide ou inexistant"], JSON_UNESCAPED_UNICODE));

  //escape everything + check if setted properly
  $email = htmlspecialchars(trim($_POST["email"]));
  $list = htmlspecialchars(trim($_POST["list"]));
  $asso = htmlspecialchars(trim($_POST["asso"]));
  $newMail = htmlspecialchars(trim($_POST["newMail"]));
  $canGoThroughModeration = (bool) $_POST["canGoThroughModeration"];

  //Please don't touch to automatic lists
  if(preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $list))
    exit(json_encode(["status" => 1, "error" => "Cette liste n'est pas modifiable"], JSON_UNESCAPED_UNICODE));

  //Make sure email is valid
  if (!filter_var($newMail, FILTER_VALIDATE_EMAIL))
    exit(json_encode(["status" => 1, "error" => "Nouvelle adresse email invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!is_bool($canGoThroughModeration))
    exit(json_encode(["status" => 1, "error" => "goThroughModeration state invalide"], JSON_UNESCAPED_UNICODE));

  //Get everything before the @
  $listPart = preg_replace("/\@.*/", "", $list);

  //Check permissions (here we make sure that user is admin on the list)
  $admPerms = $permissionsManager->get($resourceOwner["email"], $listPart);
  if(!isset($admPerms["admin"]) || $admPerms["admin"] != true)
    exit(json_encode(["status" => 1, "error" => "Vous devez être administrateur pour effectuer cette action"], JSON_UNESCAPED_UNICODE));

  $user = $portailManager->getPortail(PORTAIL_API_URL . "/users/" . $newMail, $appAccessToken);
  $isPortail = (isset($user["message"]) ? false : true);

  $oldPerms = $permissionsManager->get($email, $listPart);
  if(!$oldPerms)
    $oldPerms = ["admin" => 0, "goThroughModeration" => 0];

  // If email does not change, only update permissions
  if($email == $newMail) {
    //Administrator can't change his own email because he would loose his permissions ...
    if($email == $resourceOwner["email"])
      exit(json_encode(["status" => 1, "error" => "La modification de son propre compte est désactivée pour les admins"], JSON_UNESCAPED_UNICODE));

    if($isPortail)
      $permissionsManager->update($email, $listPart, $oldPerms["admin"], $canGoThroughModeration);
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
    $permissionsManager->add($newMail, $listPart, $oldPerms["admin"], $canGoThroughModeration);

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
