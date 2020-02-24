<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>SiMDE - Gestion des mails assos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" >
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/dark.css">
  </head>
  <body>
    <div class="container-fluid barresup">
      <div class="navbar navbar-fixed-top d-flex flex-column flex-md-row">
          <button id="toggleMenuButton" type="button" class="btn btn-default d-block d-md-none position-absolute" style="left: 0;margin-top: 4%;">
            <i class="fas fa-bars fa-2x iconmenu"></i>
          </button>
          <a class="navbar-brand" href="/agniacum/">SiMDE|Gesmail</a>
          <p class="navbar-text text-center" style="margin: auto 0;">Connecté en tant que <?= $resourceOwner["name"] ?> <a href="php/logout.php">Déconnexion</a></p>
      </div>
    </div>
    <hr>
    <div class="container-fluid overflow-auto">
      <div class="row">
        <div class="col-md-3 d-none d-md-block" style="overflow: hidden;" id="leftMenu">
          <?php require_once("php/frags/leftmenu.php"); ?>
        </div>
