<?php
  require_once("php/required.php");

  //ensure everything is defined && setted
  if(!isset($_GET["asso"]) || empty($_GET["asso"]))
    header("Location: /agniacum/");

  //Get the asso and ensure user is a member
  $currentAsso = $portailManager->getPortail(PORTAIL_API_URL . "/user/assos/" . htmlspecialchars($_GET["asso"]), $accessToken);

  //Ensure list & asso exists
  if(array_key_exists("message", $currentAsso))
    die($currentAsso["message"]);

  require_once("php/frags/header.php");
?>
<div class="col-10">
  <div class="container bloc">
    <h1 class="text-center">Accueil de <?= $currentAsso["name"] ?></h1>
    <p>Bonjour, bienvenu sur l'Accueil de l'asso <?= $currentAsso["name"] ?>, tu peux ici modifier / créer / supprimer des mailing listes.</p>
    <p>Tu peux aussi modifier les droits des membres de ces listes</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center">Liste des mailing listes <?= $currentAsso["name"] ?></h1>
    <p>Bonjour, bienvenu sur le Gesmail, tu peux ici gérer toutes les mailing listes des assos auxquelles tu est inscrit sur le portail.</p>
    <p>Pour commencer, tu peux sélectionner une asso dans le menu de gauche et gérer les redirections ou ajouter / modifier les mailing listes</p>
    <p>Pour chaque mailing liste, tu pourra ajouter / supprimer des membres, gérer les droits (notamment concernant l'envoi de mails et la modération).</p>
    <p>Tu peux aussi administrer les message directement depuis cette interface.</p>
    <ul id="listOfMailingLists" suffix="<?= SUFFIXE_MAIL ?>">
      <?php
        $orderdList = $sympaManager->lists($currentAsso["login"] . SUFFIXE_MAIL);
        usort($orderdList, function ($ml1, $ml2) { return strcmp($ml1->listAddress, $ml2->listAddress); });
        foreach ($orderdList as $index => $list) :
          if(preg_match("/(-bounce@)/", $list->listAddress))
            continue; //Just keep redirections as we don't want them to be deleted
        ?>
        <li class="input-group rowsEmail">
          <input class="form-control" value="<?= $list->listAddress ?>" disabled></input>
          <div class="input-group-append" role="group">
            <a class="btn btn-primary" href="/agniacum/list.php?asso=<?= $currentAsso["login"] ?>&list=<?= $list->listAddress ?>" role="button">Détails</a>
            <button class="btn btn-danger deleteListBtn" asso="<?= $currentAsso["login"] ?>" list="<?= $list->listAddress ?>">Supprimer</button>
          </div>
        </li>
        <?php endforeach ?>
    </ul>
  </div>
  <div class="container bloc">
    <h1 class="text-center">Créer une mailing liste pour <?= $currentAsso["name"] ?></h1>
    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text" id="basic-addon1"><?= $currentAsso["login"] ?>-</span>
      </div>
      <input type="text" id="createListTxt" class="form-control" placeholder="Mailing list name">
      <div class="input-group-append">
        <span class="input-group-text" id="basic-addon2"><?= SUFFIXE_MAIL ?></span>
      </div>
      <button class="btn btn-primary" id="createListBtn" asso="<?= $currentAsso["login"] ?>">Créer</button>
    </div>
  </div>
</div>
<li id="skeletonMLRow" class="input-group rowsEmail">
  <input class="form-control" value="" disabled></input>
  <div class="input-group-append" role="group">
    <a class="btn btn-primary" href="/agniacum/list.php?asso=<?= $currentAsso["login"] ?>" role="button">Détails</a>
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
      });
    }
  }

  //Manage Add
  document.getElementById("createListBtn").addEventListener("click", function(evt) {
    var asso = this.getAttribute("asso");
    var listname = document.getElementById("createListTxt").value.toLowerCase()

    makeRequest("POST", "php/actions/createList.php", {"asso": asso, "listname": listname}, function(response) {
        showMessage("success", "Succès", "La liste a été crée avec succès");

        //Build new line and append it
        let newDOMRow = document.getElementById("skeletonMLRow").cloneNode(true);
        let mlFullName = asso + "-" + listname + document.getElementById("listOfMailingLists").getAttribute("suffix");
        newDOMRow.children[0].value = mlFullName;
        newDOMRow.children[1].children[0].setAttribute("href", newDOMRow.children[1].children[0].getAttribute("href") + "&list=" + mlFullName);
        newDOMRow.children[1].children[1].setAttribute("list", mlFullName);
        newDOMRow.removeAttribute("id");
        document.getElementById("listOfMailingLists").append(newDOMRow);
        document.getElementById("createListTxt").value = "";
    });
  });
</script>
<?php
  require_once("php/frags/footer.php");
?>
