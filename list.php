<?php
  require_once("php/required.php");

  //ensure everything is defined && setted
  if(!isset($_GET["asso"]) || empty($_GET["asso"]))
    header("Location: /gesmail/");

  if(!isset($_GET["list"]) || empty($_GET["list"]))
    header("Location: /gesmail/");

  //Get the asso and ensure user is a member
  $currentAsso = $portailManager->getPortail(PORTAIL_API_URL . "/user/assos/" . htmlspecialchars($_GET["asso"]), $accessToken);
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

  if(!preg_match("/$currentAsso[login]/", $listname))
    die("La liste ne corresponds pas à l'association");

  try {
    $currentList = $sympaManager->info($listname, $currentAsso["login"] . SUFFIXE_MAIL)[0];
    if(!$currentList)
      die("Exception : Liste restreinte.");
  } catch (SoapFault $ex) {
    die("$ex->faultstring, <strong>Detail:</strong> $ex->detail $ex->faultcode Exception");
  }

  //Check if user is bureau restreint
  $isBureauRestreint = false;
  foreach ($assosAdminPortail as $key => $ml) {
    if($ml["login"] == $currentAsso["login"])
      $isBureauRestreint = true;
  }
  if(!$isBureauRestreint)
    die("Vous n'avez pas les droits nécessaires pour effectuer cette action");


  //Get all members of this list
  $listMembers = $sympaManager->review($currentList->listAddress, $currentAsso["login"] . SUFFIXE_MAIL);

  $newListPart = preg_replace("/\@.*/", "", $listname);
  $permissions = $permissionsManager->getList($newListPart);
  $permissionsList = $permissionsListManager->get($newListPart);

  if($listMembers[0] == "no_subscribers") unset($listMembers[0]);

  //If this is a redirection, change the display name
  $displayAdress = ($isRedirection) ? $currentAsso["login"] . SUFFIXE_MAIL : $currentList->listAddress;

  require_once("php/frags/header.php");
?>
<div class="col-md-9 d-md-block" id="content">
  <div class="container bloc">
    <h1 class="text-center text-break">Accueil de <?= $displayAdress ?></h1>
    <p>Bienvenue sur l'accueil de la mailing liste <?= $displayAdress ?>, tu peux ici modifier toutes les informations concernant la mailing liste.</p>
    <p>Tu peux ajouter / supprimer des membres et gérer leurs droits.</p>
    <p>Certaines tâches d'administration peuvent prendre jusqu'à 5 minutes.</p>
    <p>Note: Les membres de la liste <?= $currentAsso["login"] . SUFFIXE_MAIL ?> auront le droit d'administrer les messages de cette liste.<p>
    <span class="badge badge-pill badge-primary"><?= (isset($permissionsList["send"]) && $permissionsList["send"]) ? "Mailing liste non modérée" : "Mailing liste modérée" ?></span>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Liste des membres de <?= $displayAdress ?></h1>
    <p><?= (empty($listMembers)) ? "Aucun membre n'est inscrit à cette liste." : "Liste des membres : " ?></p>
    <ul id="listOfEmails">
      <?php foreach ($listMembers as $key => $mail) :
        $isAdmin = (array_key_exists($mail, $permissions) && $permissions[$mail][0]["admin"]);
        $canGoThroughModeration = (array_key_exists($mail, $permissions) && $permissions[$mail][0]["goThroughModeration"]);
        $user = $portailManager->getPortail(PORTAIL_API_URL . "/users/" . $mail, $appAccessToken);
        $isPortail = (isset($user["message"]) ? false : true);
      ?>
        <li class="input-group rowsEmail">
          <input type="email" value="<?= $mail ?>" class="form-control"></input>
          <div class="input-group-append d-flex flex-wrap flex-lg-nowrap" role="group">
            <select class="form-control permissionsSelects d-<?= ($isRedirection || !$isPortail) ? "none" : "block" ?>" >
              <option <?= ($isAdmin) ? "selected" : "" ?> value="1">Admin</option>
              <option <?= ($isAdmin) ? "" : "selected" ?> value="0">Non admin</option>
            </select>
            <select class="form-control permissionsSelects d-<?= ($isRedirection || !$isPortail) ? "none" : "block" ?>">
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
    <h1 class="text-center text-break">Ajouter un membre à <?= $displayAdress ?></h1>
    <form id="addMailForm" class="input-group">
      <input class="form-control" type="text" id="addEmail" type="email"></input>
      <input type="submit" class="btn btn-primary" id="addEmailBtn" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>" value="Ajouter" />
    </form>
  </div>
</div>
<li id="skeletonEmailRow" class="input-group rowsEmail">
  <input type="email" value="" class="form-control"></input>
  <div class="input-group-append d-flex flex-wrap flex-lg-nowrap" role="group">
    <select class="form-control permissionsSelects <?= $isRedirection ? "d-none" : "" ?>">
      <option value="1">Admin</option>
      <option selected value="0">Non admin</option>
    </select>
    <select class="form-control permissionsSelects <?= $isRedirection ? "d-none" : "" ?>">
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

      makeRequest("POST", "php/actions/deleteEmail.php", {"list": list, "asso": asso, "email": email}, function(response) {
        showMessage("success", "Succès", "L'adresse a été supprimée avec succès");
        block.remove();
      });
    } else if (evt.target.matches('.updateOnEmail')) {
      var list = btn.getAttribute("list");
      var asso = btn.getAttribute("asso");
      var email = btn.getAttribute("email");
      var block = btn.closest(".rowsEmail");
      var newMail = block.children[0].value;
      var isAdmin = block.children[1].children[0].value;
      var canGoThroughModeration = block.children[1].children[1].value;

      makeRequest("POST", "php/actions/updateEmail.php", {"list": list, "asso": asso, "email": email, "newMail": newMail, "isAdmin": isAdmin, "canGoThroughModeration": canGoThroughModeration}, function(response) {
        showMessage("success", "Succès", "L'adresse a été modifiée avec succès");
        block.children[0].value = newMail;
        block.children[1].children[2].setAttribute("email", newMail);
        block.children[1].children[3].setAttribute("email", newMail);

        block.children[1].children[0].classList.remove("d-none", "d-block");
        block.children[1].children[1].classList.remove("d-none", "d-block");
        if(!response.data.isPortail) {
          block.children[1].children[0].value = 0;
          block.children[1].children[1].value = 0;
          block.children[1].children[0].classList.add("d-none");
          block.children[1].children[1].classList.add("d-none");
        } else {
          block.children[1].children[0].classList.add("d-block");
          block.children[1].children[1].classList.add("d-block");
        }
      });
    }
  }

  //Manage Add
  document.getElementById("addMailForm").addEventListener("submit", function(evt) {
    evt.preventDefault();
    var addMail = document.getElementById("addEmail").value.toLowerCase();
    var list = document.getElementById("addEmailBtn").getAttribute("list");
    var asso = document.getElementById("addEmailBtn").getAttribute("asso");

    makeRequest("POST", "php/actions/addEmail.php", {"email": addMail, "list": list, "asso": asso}, function(response) {
      showMessage("success", "Succès", "L'adresse a été ajoutée avec succès");
      let domRowNewEmail = document.getElementById("skeletonEmailRow").cloneNode(true);
      domRowNewEmail.children[0].value = addMail;
      domRowNewEmail.children[1].children[2].setAttribute("email", addMail);
      domRowNewEmail.children[1].children[3].setAttribute("email", addMail);

      if(!response.data.isPortail) {
        domRowNewEmail.getElementsByClassName("permissionsSelects")[0].classList.add("d-none");
        domRowNewEmail.getElementsByClassName("permissionsSelects")[1].classList.add("d-none");
      }

      domRowNewEmail.removeAttribute("id");
      document.getElementById("listOfEmails").append(domRowNewEmail);
      document.getElementById("addEmail").value = "";
    });
  });
</script>
<?php
  require_once("php/frags/footer.php");
?>
