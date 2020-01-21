<?php
  require_once("php/required.php");

  //ensure everything is defined && setted
  if(!isset($_GET["asso"]) || empty($_GET["asso"]))
    header("Location: /agniacum/");

  if(!isset($_GET["list"]) || empty($_GET["list"]))
    header("Location: /agniacum/");

  //Check is asso exists
  $currentAsso = $portailManager->getPortail(PORTAIL_API_URL . "/assos/" . htmlspecialchars($_GET["asso"]), $accessToken);
  $listname = htmlspecialchars($_GET["list"]);

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

  //Get the part before the @
  $listPart = preg_replace("/\@.*/", "", $currentList->listAddress);

  $userPerms = $permissionsManager->get($resourceOwner["email"], $listPart);
  if(!isset($userPerms["admin"]) || !$userPerms["admin"])
    die("Vous devez être administrateur pour acceder à cette liste");

  //Get all subscribers of this list
  $listMembers = $sympaManager->review($currentList->listAddress, $currentAsso["login"] . SUFFIXE_MAIL);
  $permissionsList = $permissionsListManager->get($listPart);

  //Get all permissions on this list
  $permissions = $permissionsManager->getList($listPart);

  if($listMembers[0] == "no_subscribers") unset($listMembers[0]);

  require_once("php/frags/header.php");
?>
<div class="col-md-10 d-md-block" id="content">
  <div class="container bloc">
    <h1 class="text-center text-break">Accueil de <?= $currentList->listAddress ?></h1>
    <p>Bonjour, bienvenue sur l'accueil de la mailing liste <?= $currentList->listAddress ?>, tu peux ici modifier la mailing liste.</p>
    <p>Tu peux ici ajouter / supprimer des membres et gérer leurs droits.</p>
    <p>L'ajout / suppression / modification d'une adresse mail peut prendre jusqu'à 5 minutes.</p>
    <p>La mailing liste est modérée ? <?= (isset($permissionsList["send"]) && $permissionsList["send"]) ? "Non, tous les membres peuvent envoyer un mail" : "Oui, les admin doivent accepter les messages" ?></p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Liste des membres de <?= $currentList->listAddress ?></h1>
    <p><?= (empty($listMembers)) ? "Aucun membre n'est inscrit à cette liste." : "Liste des membres : " ?></p>
    <ul id="listOfEmails">
      <?php foreach ($listMembers as $key => $mail) :
        $canGoThroughModeration = (array_key_exists($mail, $permissions) && $permissions[$mail][0]["goThroughModeration"]);

        $user = $portailManager->getPortail(PORTAIL_API_URL . "/users/" . $mail, $appAccessToken);
        $isPortail = (isset($user["message"]) ? false : true);
        ?>
        <li class="input-group rowsEmail">
          <input type="email" value="<?= $mail ?>" class="form-control"></input>
          <div class="input-group-append" role="group">
            <select class="form-control permissionsSelects d-<?= (!$isPortail) ? "none" : "block" ?>">
              <option <?= ($canGoThroughModeration) ? "selected" : "" ?> value="1">Peut outrepasser la modération</option>
              <option <?= ($canGoThroughModeration) ? "" : "selected" ?> value="0">Ne peut pas outrepasser la modération</option>
            </select>
            <button class="btn btn-primary updateOnEmail" email="<?= $mail ?>" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>" action="update">Modifier</button>
            <button class="btn btn-danger deleteOnEmail" email="<?= $mail ?>" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>" action="delete">Supprimer</button>
          </div>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Ajouter un membre à <?= $currentList->listAddress ?></h1>
    <form id="addEmailForm" class="input-group">
      <input class="form-control" type="text" id="addEmail" type="email"></input>
      <input type="submit" class="btn btn-primary" id="addEmailBtn" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>" value="Ajouter" />
    </form>
  </div>
</div>
<li id="skeletonEmailRow" class="input-group rowsEmail">
  <input type="email" value="" class="form-control"></input>
  <div class="input-group-append" role="group">
    <select class="form-control permissionsSelects">
      <option value="1">Peut outrepasser la modération</option>
      <option selected value="0">Ne peut pas outrepasser la modération</option>
    </select>
    <button class="btn btn-primary updateOnEmail" email="" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>" action="update">Modifier</button>
    <button class="btn btn-danger deleteOnEmail" email="" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>" action="delete">Supprimer</button>
  </div>
</li>
<script>
  // Manage Update / Delete
  document.getElementById("listOfEmails").addEventListener("click", deleteOrUpdateListHandler);
  function deleteOrUpdateListHandler(evt) {
    var btn = evt.target;
    if (evt.target.matches('.deleteOnEmail')) {
      var list = btn.getAttribute("list");
      var asso = btn.getAttribute("asso");
      var email = btn.getAttribute("email");
      var block = btn.closest(".rowsEmail");

      makeRequest("POST", "php/actions/deleteEmailAsAdmin.php", {"list": list, "asso": asso, "email": email}, function(response) {
        showMessage("success", "Succès", "L'adresse a été supprimée avec succès");
        block.remove();
      });
    } else if (evt.target.matches('.updateOnEmail')) {
      var list = btn.getAttribute("list");
      var asso = btn.getAttribute("asso");
      var email = btn.getAttribute("email");
      var block = btn.closest(".rowsEmail");
      var newMail = block.children[0].value;
      var canGoThroughModeration = block.children[1].children[0].value;

      makeRequest("POST", "php/actions/updateEmailAsAdmin.php", {"list": list, "asso": asso, "email": email, "newMail": newMail, "canGoThroughModeration": canGoThroughModeration}, function(response) {
        showMessage("success", "Succès", "L'adresse a été modifiée avec succès");
        block.children[0].value = newMail;
        block.children[1].children[1].setAttribute("email", newMail);
        block.children[1].children[2].setAttribute("email", newMail);

        block.children[1].children[0].classList.remove("d-none", "d-block");
        if(!response.data.isPortail) {
          block.children[1].children[0].value = 0;
          block.children[1].children[0].classList.add("d-none");
        } else
          block.children[1].children[0].classList.add("d-block");

      });
    }
  }

  //Manage Add
  document.getElementById("addEmailForm").addEventListener("submit", function(evt) {
    evt.preventDefault();
    var addMail = document.getElementById("addEmail").value.toLowerCase();
    var list = document.getElementById("addEmailBtn").getAttribute("list");
    var asso = document.getElementById("addEmailBtn").getAttribute("asso");

    makeRequest("POST", "php/actions/addEmailAsAdmin.php", {"email": addMail, "list": list, "asso": asso}, function(response) {
      showMessage("success", "Succès", "L'adresse a été ajoutée avec succès");
      let domRowNewEmail = document.getElementById("skeletonEmailRow").cloneNode(true);
      domRowNewEmail.children[0].value = addMail;
      domRowNewEmail.children[1].children[1].setAttribute("email", addMail);
      domRowNewEmail.children[1].children[2].setAttribute("email", addMail);
      if(!response.data.isPortail)
        domRowNewEmail.getElementsByClassName("permissionsSelects")[0].classList.add("d-none");

      domRowNewEmail.removeAttribute("id");
      document.getElementById("listOfEmails").append(domRowNewEmail);
      document.getElementById("addEmail").value = "";
    });
  });
</script>
<?php
  require_once("php/frags/footer.php");
?>
