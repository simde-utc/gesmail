<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>SiMDE - Gestion des mails assos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" >
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="css/all.css">
  </head>
  <body>
    <div class="container-fluid">
      <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
          <div class="container-fluid">
            <a class="navbar-brand" href="/agniacum/">SiMDE|Gesmail</a>
            <p class="navbar-text pull-right">Connecté en tant que <?= $resourceOwner["name"] ?> <a href="php/logout.php">Déconnexion</a></p>
          </div>
        </div>
      </div>
    </div>
    <hr>
    <div class="container-fluid overflow-auto">
      <!-- Left block (menu) -->
      <div class="row">
        <div class="col-2">
          <?php require_once("php/frags/leftmenu.php"); ?>
        </div>
