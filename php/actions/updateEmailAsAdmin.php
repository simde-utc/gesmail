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

  if(!isset($_POST["isMailer"]))
    exit(json_encode(["status" => 1, "error" => "Mailer state invalide ou inexistant"], JSON_UNESCAPED_UNICODE));


  //escape everything + check if setted properly
  $email = htmlspecialchars(trim($_POST["email"]));
  $list = htmlspecialchars(trim($_POST["list"]));
  $asso = htmlspecialchars(trim($_POST["asso"]));
  $newMail = htmlspecialchars(trim($_POST["newMail"]));
  $isMailer = (bool) $_POST["isMailer"];

  //Make sure email is valid
  if (!filter_var($newMail, FILTER_VALIDATE_EMAIL))
    exit(json_encode(["status" => 1, "error" => "Nouvelle adresse email invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!is_bool($isMailer))
    exit(json_encode(["status" => 1, "error" => "Mailer state invalide"], JSON_UNESCAPED_UNICODE));

  //Get everything before the @
  $listPart = preg_replace("/" . SUFFIXE_MAIL . "/", "", $list);

  //Check permissions (here we make sure that user is admin on the list)
  $admPerms = $permissionsManager->get($resourceOwner["email"], $listPart);
  if(!isset($admPerms["admin"]) || $admPerms["admin"] != true)
    exit(json_encode(["status" => 1, "error" => "Vous devez être administrateur pour effectuer cette action"], JSON_UNESCAPED_UNICODE));

  try {
    //Save all permissions, delete permission and subscrbption then add again
    $oldPerms = $permissionsManager->get($email, $listPart);
    $permissionsManager->delete($email, $listPart);
    $statusDel = $sympaManager->del($list, $email, true, $asso . SUFFIXE_MAIL);

    //Do not add permissions if it's a redirection
    if(!preg_match("/(-bounce@)/", $list))
      $permissionsManager->add($newMail, $listPart, $oldPerms["admin"], $isMailer);

    $statusAdd = $sympaManager->add($list, $newMail, true, $asso . SUFFIXE_MAIL);
  } catch (SoapFault $ex) {
    exit(json_encode(["status" => 1, "error" => ("$ex->faultstring, détail : " . utf8_decode($ex->detail) . " ($ex->faultcode)")], JSON_UNESCAPED_UNICODE));
  }

  if($statusDel && $statusAdd)
    exit(json_encode(["status" => 0, "success" => "Email modifé avec succès"], JSON_UNESCAPED_UNICODE));
  else
    exit(json_encode(["status" => 1, "error" => "Echec lors de la mise à jour de l'email (conseil: rafraichir la page)"], JSON_UNESCAPED_UNICODE));
