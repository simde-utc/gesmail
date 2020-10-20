<?php
  require_once("php/required.php");

  //ensure everything is defined && setted
  if(!isset($_GET["asso"]) || empty($_GET["asso"]))
    header("Location: /gesmail/");

  //Get the asso and ensure user is a member
  $currentAsso = $portailManager->getPortail(PORTAIL_API_URL . "/user/assos/" . htmlspecialchars($_GET["asso"]), $accessToken);

  //Ensure list & asso exists
  if(array_key_exists("message", $currentAsso))
    die($currentAsso["message"]);

  //check if user is bureau restreint
  $isBureauRestreint = false;
  foreach ($assosAdminPortail as $key => $ml) {
    if($ml["login"] == $currentAsso["login"])
      $isBureauRestreint = true;
  }
  if(!$isBureauRestreint)
    die("Vous n'avez pas les droits nécessaires pour effectuer cette action");

  require_once("php/frags/header.php");
?>
<div class="col-md-9 d-md-block" id="content">
  <div class="container bloc">
    <h1 class="text-center text-break">Accueil de <?= $currentAsso["name"] ?></h1>
    <p>Bienvenue sur l'Accueil de l'association <?= $currentAsso["name"] ?>, tu peux ici modifier / créer / supprimer des mailing listes.</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Liste des mailing listes <?= $currentAsso["name"] ?></h1>
    <p>Pour chaque mailing liste, tu peux ajouter / supprimer des membres, gérer les droits (notamment concernant l'envoi de mails et la modération) en cliquant sur "détails".</p>
    <ul id="listOfMailingLists" suffix="<?= SUFFIXE_MAIL ?>">
      <?php
        $specificLists = [];
        $currAssoSubscribedToList = [];
        $allListsOfAsso = $sympaManager->lists($currentAsso["login"] . SUFFIXE_MAIL);
        usort($allListsOfAsso, function ($ml1, $ml2) { return strcmp($ml1->listAddress, $ml2->listAddress); });
        foreach ($allListsOfAsso as $index => $list) :
          //Do not show automatic lists nor automatic lists
          if(preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $list->listAddress))
            continue;

      	  if(!$list->isOwner) {
            $currAssoSubscribedToList[] = $list;
            continue;
          }

          // If list is bounce, remove the -bounce part
          $isBounce = false;
          if(preg_match("/(-bounce)/", $list->listAddress)) {
            $list->listAddress = $currentAsso["login"] . SUFFIXE_MAIL;
            $isBounce = true;
          }

          $listPart = preg_replace("/\@.*/", "", $list->listAddress);
          $default = $permissionsListManager->get($listPart);
        ?>
        <li class="input-group rowsEmail">
          <input class="form-control" value="<?= $list->listAddress ?>" disabled></input>
          <div class="input-group-append d-flex flex-wrap flex-lg-nowrap" role="group">
            <select class="form-control <?= ($isBounce) ? "d-none" : "" ?>">
              <option <?= (isset($default["send"]) && $default["send"]) ? "selected" : "" ?> value="1">Tous les membres peuvent envoyer un mail</option>
              <option <?= (isset($default["send"]) && $default["send"]) ? "" : "selected" ?> value="0">Liste modérée</option>
            </select>
            <button class="btn btn-primary updateListBtn <?= ($isBounce) ? "d-none" : "" ?>" asso="<?= $currentAsso["login"] ?>" list="<?= $list->listAddress ?>">Modifier</button>
            <a class="btn btn-secondary noColor" href="/gesmail/list.php?asso=<?= $currentAsso["login"] ?>&list=<?= $list->listAddress ?>" role="button">Détails</a>
            <button class="btn btn-danger deleteListBtn <?= ($isBounce) ? "d-none" : "" ?>" asso="<?= $currentAsso["login"] ?>" list="<?= $list->listAddress ?>">Supprimer</button>
          </div>
        </li>
        <?php endforeach ?>
    </ul>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Créer une mailing liste pour <?= $currentAsso["name"] ?></h1>
    <form method="post" action="" id="fromAddML" class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text" id="basic-addon1"><?= $currentAsso["login"] ?>-</span>
      </div>
      <input type="text" id="createListTxt" class="form-control" placeholder="Mailing list name">
      <div class="input-group-append">
        <span class="input-group-text" id="basic-addon2"><?= SUFFIXE_MAIL ?></span>
      </div>
      <input type="submit" class="btn btn-primary" id="createListBtn" asso="<?= $currentAsso["login"] ?>" value="Créer" />
    </form>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Listes automatiques de l'association : <?= $currentAsso["name"] ?></h1>
    <ul class="navbar list-unstyled">
      <?php foreach (AUTOMATICSUFFIX as $key => $suffixe) : ?>
        <li>
          <input class="form-control" value="<?= $currentAsso["login"] . "-$suffixe" . SUFFIXE_MAIL ?>" disabled></input>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
  <?php
  if(!empty($currAssoSubscribedToList)) : ?>
  <div class="container bloc">
    <h1>Listes auxquelles votre association est inscrite :</h1>
    <ul class="navbar list-unstyled">
      <?php foreach ($currAssoSubscribedToList as $key => $list) :
        if(preg_match("/(-bounce@)/", $list->listAddress))
          $list->listAddress = $currentAsso["login"] . SUFFIXE_MAIL; //Do not show the bounce part of email
      ?>
        <li>
          <input class="form-control" value="<?= $list->listAddress ?>" disabled></input>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
  <?php
  endif;
  ?>
</div>
<li id="skeletonMLRow" class="input-group rowsEmail">
  <input class="form-control" value="" disabled></input>
  <div class="input-group-append d-flex flex-wrap flex-lg-nowrap" role="group">
    <select class="form-control">
      <option selected value="1">Tous les membres</option>
      <option value="0">Liste modérée</option>
    </select>
    <button class="btn btn-primary updateListBtn" asso="<?= $currentAsso["login"] ?>" list="">Modifier</button>
    <a class="btn btn-secondary noColor" href="/gesmail/list.php?asso=<?= $currentAsso["login"] ?>" role="button">Détails</a>
    <button class="btn btn-danger deleteListBtn" asso="<?= $currentAsso["login"] ?>" list="">Supprimer</button>
  </div>
</li>
<script>
  // Manage Delete
  document.getElementById("listOfMailingLists").addEventListener("click", deleteListHandler);
  function deleteListHandler(evt) {
    if (evt.target.matches('.deleteListBtn')) {
      var btn = evt.target;
      var list = btn.getAttribute("list").toLowerCase();
      var asso = btn.getAttribute("asso");
      var block = btn.closest(".rowsEmail");

      makeRequest("POST", "php/actions/deleteList.php", {"asso": asso, "listname": list}, function(response) {
          showMessage("success", "Succès", "La liste a été supprimée avec succès");
          block.remove();
          reloadLeftMenu();
      });
    } else if(evt.target.matches(".updateListBtn")) {
      var btn = evt.target;
      var list = btn.getAttribute("list").toLowerCase();
      var asso = btn.getAttribute("asso");
      var send = btn.closest(".rowsEmail").children[1].children[0].value;

      makeRequest("POST", "php/actions/updateList.php", {"asso": asso, "listname": list, "send": send}, function(response) {
          showMessage("success", "Succès", "La liste a été modifiée avec succès");
          reloadLeftMenu();
      });
    }
  }

  //Manage Add
  document.getElementById("fromAddML").addEventListener("submit", function(evt) {
    evt.preventDefault();
    var asso = document.getElementById("createListBtn").getAttribute("asso");
    var listname = document.getElementById("createListTxt").value.toLowerCase()

    makeRequest("POST", "php/actions/createList.php", {"asso": asso, "listname": listname}, function(response) {
        showMessage("success", "Succès", "La liste a été crée avec succès");

        //Build new line and append it
        let newDOMRow = document.getElementById("skeletonMLRow").cloneNode(true);
        let mlFullName = asso + "-" + listname + document.getElementById("listOfMailingLists").getAttribute("suffix");
        newDOMRow.children[0].value = mlFullName;
        newDOMRow.children[1].children[1].setAttribute("list", mlFullName);
        newDOMRow.children[1].children[2].setAttribute("href", newDOMRow.children[1].children[2].getAttribute("href") + "&list=" + mlFullName);
        newDOMRow.children[1].children[3].setAttribute("list", mlFullName);
        newDOMRow.removeAttribute("id");
        document.getElementById("listOfMailingLists").append(newDOMRow);
        document.getElementById("createListTxt").value = "";

        reloadLeftMenu();
    });
  });
</script>
<?php
  require_once("php/frags/footer.php");
?>
