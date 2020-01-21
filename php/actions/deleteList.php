<?php
  require_once("../required.php");

  //ensure everything is defined && setted
  if(!isset($_POST["listname"]) || empty(trim($_POST["listname"])))
    exit(json_encode(["status" => 1, "error" => "Liste invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  if(!isset($_POST["asso"]) || empty(trim($_POST["asso"])))
    exit(json_encode(["status" => 1, "error" => "Asso invalide ou inexistante"], JSON_UNESCAPED_UNICODE));

  //escape everything + check if setted properly
  $listname = htmlspecialchars(trim($_POST["listname"]));
  $asso = htmlspecialchars(trim($_POST["asso"]));

  //Make sure email is valid
  if (!filter_var($listname, FILTER_VALIDATE_EMAIL))
    exit(json_encode(["status" => 1, "error" => "Format de liste incorrect"], JSON_UNESCAPED_UNICODE));

  //Do not delete a -bounce mailing list
  if(preg_match("/(-bounce@)/", $listname))
    exit(json_encode(["status" => 1, "error" => "Bounce est un mot réservé"], JSON_UNESCAPED_UNICODE));

  //Please don't touch to automatic lists
  if(preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $listname))
    exit(json_encode(["status" => 1, "error" => "Cette liste n'est pas modifiable"], JSON_UNESCAPED_UNICODE));

  //Ensure user has enough rights
  $isBureauRestreint = false;
  foreach ($assosAdminPortail as $key => $ml) {
    if($ml["login"] == $asso)
      $isBureauRestreint = true;
  }
  if(!$isBureauRestreint)
    exit(json_encode(["status" => 1, "error" => "Vous n'avez pas les droits nécessaires pour effectuer cette action"], JSON_UNESCAPED_UNICODE));

  try {
    $statusCreate = $sympaManager->closeList($listname, $asso . SUFFIXE_MAIL);
  } catch (SoapFault $ex) {
    exit(json_encode(["status" => 1, "error" => ("$ex->faultstring, détail : " . utf8_decode($ex->detail) . " ($ex->faultcode)")], JSON_UNESCAPED_UNICODE));
  }

  $listPart = preg_replace("/\@.*/", "", $listname);
  $permissionsManager->deleteList($listPart);
  $permissionsListManager->delete($listPart, true);

  if($statusCreate)
    exit(json_encode(["status" => 0, "success" => "Liste supprimée avec succès"], JSON_UNESCAPED_UNICODE));
  else
    exit(json_encode(["status" => 1, "error" => "Echec lors de la suppression de la liste"], JSON_UNESCAPED_UNICODE));
