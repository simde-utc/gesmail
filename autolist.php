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

  //Ensure this is a automatic list
  if(!preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $listname))
    header("Location: /gesmail/");

  //Ensure list & asso exists
  if(array_key_exists("message", $currentAsso))
    die($currentAsso["message"]);

  $alreadyCreated = false;
  try {
    $currentList = $sympaManager->info($listname, $currentAsso["login"] . SUFFIXE_MAIL)[0];
    if(!empty($currentList))
       $alreadyCreated = true;
  } catch (SoapFault $ex) {
      //ignore SoapFaults, ml is just not created yet
  }

  //Check if user is bureau restreint
  $isBureauRestreint = false;
  foreach ($assosAdminPortail as $key => $ml) {
    if($ml["login"] == $currentAsso["login"])
      $isBureauRestreint = true;
  }

  //Get all members of this list
  $manualListMembers = $alreadyCreated ? $sympaManager->review($currentList->listAddress, $currentAsso["login"] . SUFFIXE_MAIL) : [];

  $newListPart = preg_replace("/\@.*/", "", $listname);
  $autoSuffix = end(explode("-", $newListPart));
  if(!empty($manualListMembers) && $listMembers[0] == "no_subscribers") unset($manualListMembers[0]);

  require_once("php/frags/header.php");
?>
<div class="col-md-9 d-md-block" id="content">
  <div class="container bloc">
    <h1 class="text-center text-break">Accueil de <?= $newListPart ?></h1>
    <p>Bienvenue sur l'accueil de la mailing liste automatique <?= $newListPart ?>, tu peux ici visionner les informations concernant la mailing liste.</p>
    <p>L'affichage de certains membres peut prendre jusqu'à 1 heure après modification sur le portail.</p>
    <span class="badge badge-pill badge-primary">Mailing liste automatique</span>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Liste des membres de <?= $newListPart ?> inclus depuis le portail</h1>
    <?php
      // Generate auth context to get users included in list
      $auth = base64_encode(GESMAIL_USER_REMOTE_LIST . ":" . GESMAIL_PASSWD_REMOTE_LIST);
      $context = stream_context_create([
        "http" => [
          "header" => "Authorization: Basic $auth"
        ]
      ]);
      $portailUsersString = file_get_contents("https://assos.utc.fr/gesmail/php/remote_lists/users.php?target=$currentAsso[id]&range=$autoSuffix", false, $context);
      $portailUsers = explode(PHP_EOL, $portailUsersString);
      $manualMembers = array_diff($manualListMembers, $portailUsers);
    ?>
    <p><?= (empty($portailUsersString)) ? "Aucun membre sur le portail n'est inscrit à cette liste." : "Liste des membres inclus depuis le portail : " ?></p>
    <ul id="listOfEmails">
      <?php
      foreach ($portailUsers as $key => $mail) :
        if(empty($mail))
          continue;
      ?>
        <li class="input-group rowsEmail">
          <input type="email" value="<?= $mail ?>" class="form-control" disabled></input>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Liste des membres de <?= $newListPart ?> ajoutés manuellement</h1>
    <p><?= (empty($manualMembers)) ? "Aucun membre n'a été ajouté manuellement à cette liste." : "Liste des membres ajoutés manuellement : " ?></p>
    <ul id="listOfEmails">
      <?php foreach ($manualMembers as $key => $mail) : ?>
        <li class="input-group rowsEmail">
          <input type="email" value="<?= $mail ?>" class="form-control" disabled></input>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
</div>
<?php
  require_once("php/frags/footer.php");
?>
