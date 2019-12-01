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

  //Get all subscribers of this list
  $listMembers = $sympaManager->review($currentList->listAddress, $currentAsso["login"] . SUFFIXE_MAIL);

  //Get the part before the @
  $listPart = preg_replace("/" . SUFFIXE_MAIL . "/", "", $currentList->listAddress);

  //Get all permissions on this list
  $permissions = $permissionsManager->getList($listPart);

  if($listMembers[0] == "no_subscribers") unset($listMembers[0]);

  require_once("php/frags/header.php");
?>
<div class="col-10">
  <div class="container bloc">
    <h1 class="text-center">Accueil de <?= $currentList->listAddress ?></h1>
    <p>Bonjour, bienvenu sur l'Accueil de la mailing liste <?= $currentList->listAddress ?>, tu peux ici modifier la mailing liste.</p>
    <p>Les éléments modifiable sont : les membres, les droits des membres et les droits de la liste par défaut</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center">Liste des membres de <?= $currentList->listAddress ?></h1>
    <p><?= (empty($listMembers)) ? "Aucun membre n'est inscrit à cette liste." : "Liste des membres : " ?></p>
    <ul id="listOfEmails">
      <?php foreach ($listMembers as $key => $mail) :
        $isMailer = (array_key_exists($mail, $permissions) && $permissions[$mail][0]["mailer"]);
        ?>
        <li class="input-group rowsEmail">
          <input type="email" value="<?= $mail ?>" class="form-control"></input>
          <div class="input-group-append" role="group">
            <select class="form-control">
              <option <?= ($isMailer) ? "selected" : "" ?> value="1">Mailer</option>
              <option <?= ($isMailer) ? "" : "selected" ?> value="0">Non mailer</option>
            </select>
            <button class="btn btn-primary updateOnEmail" email="<?= $mail ?>" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>" action="update">Modifier</button>
            <button class="btn btn-danger deleteOnEmail" email="<?= $mail ?>" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>" action="delete">Supprimer</button>
          </div>
        </li>
      <?php endforeach ?>
    </ul>
    <p>Pour commencer, tu peux sélectionner une asso dans le menu de gauche et gérer les redirections ou ajouter / modifier les mailing listes</p>
    <p>Pour chaque mailing liste, tu pourra ajouter / supprimer des membres, gérer les droits (notamment concernant l'envoi de mails et la modération).</p>
    <p>Tu peux aussi administrer les message directement depuis cette interface.</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center">Ajouter un membre à <?= $currentList->listAddress ?></h1>
    <div class="input-group">
      <input class="form-control" type="text" id="addEmail" type="email"></input>
      <button class="btn btn-primary" id="addEmailBtn" list="<?= $currentList->listAddress ?>" asso="<?= $currentAsso["login"] ?>">Ajouter</button>
    </div>
  </div>
</div>
<li id="skeletonEmailRow" class="input-group rowsEmail">
  <input type="email" value="" class="form-control"></input>
  <div class="input-group-append" role="group">
    <select class="form-control">
      <option value="1">Mailer</option>
      <option selected value="0">Non mailer</option>
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
      var isMailer = block.children[1].children[0].value;

      makeRequest("POST", "php/actions/updateEmailAsAdmin.php", {"list": list, "asso": asso, "email": email, "newMail": newMail, "isMailer": isMailer}, function(response) {
        showMessage("success", "Succès", "L'adresse a été modifiée avec succès");
        block.children[0].value = newMail;
        block.children[1].children[1].setAttribute("email", newMail);
        block.children[1].children[2].setAttribute("email", newMail);
      });
    }
  }

  //Manage Add
  document.getElementById("addEmailBtn").addEventListener("click", function(evt) {
    var addMail = document.getElementById("addEmail").value.toLowerCase();
    var list = this.getAttribute("list");
    var asso = this.getAttribute("asso");

    makeRequest("POST", "php/actions/addEmailAsAdmin.php", {"email": addMail, "list": list, "asso": asso}, function(response) {
      showMessage("success", "Succès", "L'adresse a été ajoutée avec succès");
      let domRowNewEmail = document.getElementById("skeletonEmailRow").cloneNode(true);
      domRowNewEmail.children[0].value = addMail;
      domRowNewEmail.children[1].children[1].setAttribute("email", addMail);
      domRowNewEmail.children[1].children[2].setAttribute("email", addMail);
      domRowNewEmail.removeAttribute("id");
      document.getElementById("listOfEmails").append(domRowNewEmail);
      document.getElementById("addEmail").value = "";
    });
  });
</script>
<?php
  require_once("php/frags/footer.php");
?>
