<?php
  require_once("php/required.php");

  //ensure everything is defined && setted
  if(!isset($_GET["asso"]) || empty($_GET["asso"]))
    header("Location: /gesmail/");

  if(!isset($_GET["list"]) || empty($_GET["list"]))
    header("Location: /gesmail/");

  //Ensure the asso exists
  $currentAsso = $portailManager->getPortail(PORTAIL_API_URL . "/assos/" . htmlspecialchars($_GET["asso"]), $accessToken);
  $listname = htmlspecialchars($_GET["list"]);

  //If this is a redirection, we don't want to show the "-bounce" part to the user
  $isRedirection = false;
  $listPart = preg_replace("/(-bounce)*\@.*/", "", $listname);
  if($listPart == $currentAsso["login"]) {
    $isRedirection = true;
    $listname = $listPart . "-bounce" . SUFFIXE_MAIL;
  }

  //Ensure list & asso exists
  if(array_key_exists("message", $currentAsso))
    die($currentAsso["message"]);

  if(!preg_match("/^$currentAsso[login]/", $listname))
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

  $newListPart = preg_replace("/\@.*/", "", $listname);
  $rights = $permissionsManager->get($resourceOwner["email"], $newListPart);
  $permissionsList = $permissionsListManager->get($newListPart);


  $isAdmin = (isset($rights["admin"])) ? $rights["admin"] : false;
  $canGoThroughModeration = (isset($rights["goThroughModeration"])) ? $rights["goThroughModeration"] : false;

  //If this is a redirection, change the display name
  $displayAdress = ($isRedirection) ? $currentAsso["login"] . SUFFIXE_MAIL : $currentList->listAddress;

  require_once("php/frags/header.php");
?>
<div class="col-md-9 d-md-block" id="content">
  <div class="container bloc">
    <h1 class="text-center text-break">Vous êtes actuellement abonné à <?= $displayAdress ?></h1>
    <p>Bienvenue sur l'accueil de la mailing liste <?= $displayAdress ?></p>
    <p>Tu peux ici voir tes droits sur cette mailing liste et te désinscrire</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Droits sur <?= $displayAdress ?></h1>
    <?php if(isset($permissionsList["send"]) && $permissionsList["send"] == "2"): ?>
        <span class="badge badge-pill badge-primary">Mailing liste publique</span>
    <?php else: ?>
        <span class="badge badge-pill badge-primary"><?= (isset($permissionsList["send"]) && $permissionsList["send"]) ? "Mailing liste non modérée" : "Mailing liste modérée" ?></span>
    <?php endif; ?>
    <span class="badge badge-pill badge-primary"><?= ($isAdmin) ? "Administrateur" : "Non administrateur" ?></span>
    <span class="badge badge-pill badge-primary"><?= ($canGoThroughModeration) ? "Droit de passer outre la modération" : "Pas le droit de passer outre la modération" ?></span>
  </div>
  <div class="container bloc">
  <?php if(!preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $listPart)) : ?>
    <h1 class="text-center text-break">Se désinscrire de <?= $displayAdress ?></h1>
    <div class="input-group">
      <input type="text" class="form-control" type="email" value="<?= $resourceOwner["email"] ?>" disabled></input>
      <div class="input-group-append" role="group">
        <button class="btn btn-danger" id="unsubListBtn" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>">Se désinscire</button>
      </div>
    </div>
  <?php else : ?>
    <h1 class="text-center text-break">Vous ne pouvez pas vous désinscrire de la mailing liste <?= $displayAdress ?></h1>
    <p>Cette mailing liste est automatique, vous êtes inscrit en raison de votre rôle dans une association, vous serez désinscrit automatiquement lorsque vous n'aurez plus ce rôle.</p>
    <div class="input-group">
      <input type="text" class="form-control" type="email" value="<?= $resourceOwner["email"] ?>" disabled></input>
      <div class="input-group-append" role="group">
        <button class="btn btn-danger" disabled>Se désinscire</button>
      </div>
    </div>
  <?php endif ?>
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
