<?php
  require_once("../required.php");

  //ensure everything is defined && setted
  if(!isset($_POST["email"]) || empty(trim($_POST["email"])))
    exit(json_encode(["status" => 1, "error" => "Email invalide ou inexistant"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["list"]) || empty(trim($_POST["list"])))
    exit(json_encode(["status" => 1, "error" => "Liste invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["asso"]) || empty(trim($_POST["asso"])))
    exit(json_encode(["status" => 1, "error" => "Asso invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  //escape everything + check if setted properly
  $email = htmlspecialchars(trim($_POST["email"]));
  $list = htmlspecialchars(trim($_POST["list"]));
  $asso = htmlspecialchars(trim($_POST["asso"]));

  //Get everything before the @
  $listPart = preg_replace("/\@.*/", "", $list);

  //Please don't touch to automatic lists
  if(preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $list))
    exit(json_encode(["status" => 1, "error" => "Cette liste n'est pas modifiable"], JSON_UNESCAPED_UNICODE));

  //Check permissions (here we make sure that user is admin on the list)
  $admPerms = $permissionsManager->get($resourceOwner["email"], $listPart);
  if(!isset($admPerms["admin"]) || $admPerms["admin"] != true)
    exit(json_encode(["status" => 1, "error" => "Vous devez être administrateur pour effectuer cette action"], JSON_UNESCAPED_UNICODE));

  try {
    $status = $sympaManager->del($list, $email, true, $asso . SUFFIXE_MAIL);
  } catch (SoapFault $ex) {
    exit(json_encode(["status" => 1, "error" => ("$ex->faultstring, détail : " . utf8_decode($ex->detail) . " ($ex->faultcode)")], JSON_UNESCAPED_UNICODE));
  }

  //Delete the permissions
  $permissionsManager->delete($email, $listPart);

  if($status)
    exit(json_encode(["status" => 0, "success" => "Email supprimée avec succès"], JSON_UNESCAPED_UNICODE));
  else
    exit(json_encode(["status" => 1, "error" => "Echec lors de la suppression de l'email"], JSON_UNESCAPED_UNICODE));
