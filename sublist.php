<?php
  require_once("php/required.php");

  //ensure everything is defined && setted
  if(!isset($_GET["asso"]) || empty($_GET["asso"]))
    header("Location: /agniacum/");

  if(!isset($_GET["list"]) || empty($_GET["list"]))
    header("Location: /agniacum/");

  //Ensure the asso exists
  $currentAsso = $portailManager->getPortail(PORTAIL_API_URL . "/assos/" . htmlspecialchars($_GET["asso"]), $accessToken);
  $listname = htmlspecialchars($_GET["list"]);

  //If this is a redirection, we don't want to show the "-bounce" part to the user
  $isRedirection = false;
  $listPart = preg_replace("/" . SUFFIXE_MAIL . "/", "", $listname);
  if($listPart == $currentAsso["login"]) {
    $isRedirection = true;
    $listname = $listPart . "-bounce" . SUFFIXE_MAIL;
  }

  //Ensure list & asso exists
  if(array_key_exists("message", $currentAsso))
    die($currentAsso["message"]);

  if(!preg_match("/$currentAsso[login]/", $listname))
    die("La liste ne corresponds pas à l'association");

  try {
    $currentList = $sympaManager->info($listname, $currentAsso["login"] . SUFFIXE_MAIL)[0];
  } catch (SoapFault $ex) {
    die("$ex->faultstring, <strong>Detail:</strong> $ex->detail $ex->faultcode Exception");
  }

  //Check wheter the user is subscribed to the list
  $isMemberOfList = false;
  foreach ($sympaManager->which($resourceOwner["email"]) as $key => $ml) {
    if($ml["listAddress"] == $currentList->listAddress && $ml["isSubscriber"])
      $isMemberOfList = true;
  }

  if(!$isMemberOfList)
    die("Vous n'êtes pas membre de cette liste");

  $newListPart = preg_replace("/" . SUFFIXE_MAIL . "/", "", $listname);
  $rights = $permissionsManager->get($resourceOwner["email"], $newListPart);
  $permissionsList = $permissionsListManager->get($newListPart);


  $isAdmin = (isset($rights["admin"])) ? $rights["admin"] : false;
  $canGoThroughModeration = (isset($rights["goThroughModeration"])) ? $rights["goThroughModeration"] : false;

  //If this is a redirection, change the display name
  $displayAdress = ($isRedirection) ? $currentAsso["login"] . SUFFIXE_MAIL : $currentList->listAddress;

  require_once("php/frags/header.php");
?>
<div class="col-md-10 d-md-block" id="content">
  <div class="container bloc">
    <h1 class="text-center text-break">Vous êtes actuellement abonné à <?= $displayAdress ?></h1>
    <p>Bonjour, bienvenue sur l'accueil de la mailing liste <?= $displayAdress ?></p>
    <p>Tu peux ici voir tes droits sur cette mailing liste et te désinscrire</p>
    <p>La mailing liste est modérée ? <?= (isset($permissionsList["send"]) && $permissionsList["send"]) ? "Non, tous les membres peuvent envoyer un mail" : "Oui, les admin doivent accepter les messages" ?></p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Droits sur <?= $displayAdress ?></h1>
    <p>Droits d'administrateur ? <?= ($isAdmin) ? "Oui" : "Non" ?></p>
    <p>Droit de passer outre la modération (si la liste est modérée) ? <?= ($canGoThroughModeration) ? "Oui" : "Non" ?></p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Se désinscrire de <?= $displayAdress ?></h1>
    <div class="input-group">
      <input type="text" class="form-control" type="email" value="<?= $resourceOwner["email"] ?>" disabled></input>
      <div class="input-group-append" role="group">
        <button class="btn btn-danger" id="unsubListBtn" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>">Se désinscire</button>
      </div>
    </div>
  </div>
</div>
<script>
  //Manage Add
  document.getElementById("unsubListBtn").addEventListener("click", function(evt) {
    var asso = this.getAttribute("asso");
    var listname = this.getAttribute("list");
      makeRequest("POST", "php/actions/unsubscribeList.php", {"asso": asso, "listname": listname}, function(response) {
        showMessage("success", "Succès", "Vous avez été désinscrit avec succès");
        reloadLeftMenu();
    });
  });
</script>
<?php
  require_once("php/frags/footer.php");
?>
