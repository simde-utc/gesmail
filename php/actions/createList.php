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

  $fullName = $asso . "-" . $listname;

  //Make sure email is valid
  if (!filter_var($fullName . SUFFIXE_MAIL, FILTER_VALIDATE_EMAIL))
    exit(json_encode(["status" => 1, "error" => "Format de liste invalide"], JSON_UNESCAPED_UNICODE));

  //Do not create a -bounce mailing list
  if(preg_match("/(-bounce@)/", $fullName . SUFFIXE_MAIL))
    exit(json_encode(["status" => 1, "error" => "Bounce est un mot réservé"], JSON_UNESCAPED_UNICODE));

  try {
    $statusCreate = $sympaManager->createList([$fullName, "Description de la liste", "assos", "Mail asso", "assos"], $asso . SUFFIXE_MAIL);
  } catch (SoapFault $ex) {
    exit(json_encode(["status" => 1, "error" => ("$ex->faultstring, détail : " . utf8_decode($ex->detail) . " ($ex->faultcode)")], JSON_UNESCAPED_UNICODE));
  }

  if($statusCreate)
    exit(json_encode(["status" => 0, "success" => "Liste crée avec succès"], JSON_UNESCAPED_UNICODE));
  else
    exit(json_encode(["status" => 1, "error" => "Echec lors de la création de la liste"], JSON_UNESCAPED_UNICODE));
