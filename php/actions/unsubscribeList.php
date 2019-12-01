<?php
  require_once("../required.php");

  //ensure everything is defined && setted
  if(!isset($_POST["asso"]) || empty(trim($_POST["asso"])))
    exit(json_encode(["status" => 1, "error" => "Asso invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["listname"]) || empty(trim($_POST["listname"])))
    exit(json_encode(["status" => 1, "error" => "Liste invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  //escape everything + check if setted properly
  $asso = htmlspecialchars(trim($_POST["asso"]));
  $listname = htmlspecialchars(trim($_POST["listname"]));

  //Get everything before the @
  $listPart = preg_replace("/" . SUFFIXE_MAIL . "/", "", $listname);

  //Delete the permissions
  $permissionsManager->delete($resourceOwner["email"], $listPart);

  try {
    $statusDel = $sympaManager->del($listname, $resourceOwner["email"], true, $asso . SUFFIXE_MAIL);
  } catch (SoapFault $ex) {
    exit(json_encode(["status" => 1, "error" => ("$ex->faultstring, détail : " . utf8_decode($ex->detail) . " ($ex->faultcode)")], JSON_UNESCAPED_UNICODE));
  }
  if($statusDel)
    exit(json_encode(["status" => 0, "success" => "Email supprimée avec succès"], JSON_UNESCAPED_UNICODE));
  else
    exit(json_encode(["status" => 1, "error" => "Echec lors de la suppression de l'email"], JSON_UNESCAPED_UNICODE));
