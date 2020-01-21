<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/agniacum/php/required.php"); ?>
<nav class="navbar align-content-start flex-column align-items-baseline">
  <!-- First, all the mailing lists where user is from asso with specific role -->
  <p class="nav-link" href="#">Mailing listes des assos (rôle bureau restreint) :</p>
  <?php foreach ($assosAdminPortail as $key => $asso) : ?>
    <a class="nav" href="/agniacum/asso.php?asso=<?= $asso["login"] ?>">Mailing listes de <?= $asso["shortname"] ?></a>
    <?php
      $orderdList = $sympaManager->lists($asso["login"] . SUFFIXE_MAIL);
      usort($orderdList, function ($ml1, $ml2) { return strcmp($ml1->listAddress, $ml2->listAddress); });
      foreach ($orderdList as $index => $list) :

        if(preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $list->listAddress)) //TODO: update here
          continue; //Do not show automatic lists

        if(preg_match("/(-bounce@)/", $list->listAddress))
          $list->listAddress = $asso["login"] . SUFFIXE_MAIL; //Do not show bounce
        ?>
      <a class="nav-link navelem-lv-1" href="/agniacum/list.php?asso=<?= $asso["login"] ?>&list=<?= $list->listAddress ?>"><?= $list->listAddress ?></a>
    <?php endforeach ?>
    <a class="nav-link navelem-lv-1" href="/agniacum/asso.php?asso=<?= $asso["login"] ?>">Créer une liste pour <?= $asso["shortname"] ?></a>
  <?php endforeach ?>
  <!-- Display automatic lists -->
  <p class="nav">Mailing listes de <?= $asso["shortname"] ?> (pour les resps)</p>
  <?php foreach ($assosPosteAutoPortail as $key => $asso) : ?>
    <?php foreach (AUTOMATICSUFFIX as $key => $suffixe) : ?>
      <p class="nav-link navelem-lv-1 nolinks"><?= $asso["login"] . "-$suffixe" . SUFFIXE_MAIL ?></p>
    <?php endforeach ?>
  <?php endforeach ?>
</nav>
<hr/>
<nav class="navbar align-content-start flex-column align-items-baseline">
  <!-- Then, all the mailing lists where user has admin permission -->
  <p class="nav-link" >Mailing listes que vous administrez :</p>
  <?php
    usort($assosAdminSympa, function ($ml1, $ml2) { return strcmp($ml1["list"], $ml2["list"]); });
    foreach ($assosAdminSympa as $index => $list) :
      if(preg_match("/(-bounce@)/", $list["list"]))
        $list["list"] = $list["asso"] . SUFFIXE_MAIL; //Do not show bounce
      ?>
      <a class="nav-link navelem-lv-1" href="/agniacum/adminlist.php?asso=<?= $list["asso"] ?>&list=<?= $list["list"] ?>"><?= $list["list"] ?></a>
  <?php endforeach ?>
</nav>
<hr/>
<nav class="navbar align-content-start flex-column align-items-baseline">
  <!-- last, all the mailing lists where user is a subscriber -->
  <p class="nav-link" href="#">Mailing listes auxquelles vous êtes inscrit :</p>
  <?php
    $orderdList = $sympaManager->lists($resourceOwner["email"]);
    usort($orderdList, function ($ml1, $ml2) { return strcmp($ml1->listAddress, $ml2->listAddress); });
    foreach ($orderdList as $index => $list) :
      preg_match(REGEX_LOGINASSO, $list->listAddress, $loginAsso);
      $assoSub = $portailManager->getPortail(PORTAIL_API_URL . "/assos/" . $loginAsso[0], $accessToken);

      if(preg_match("/(-bounce@)/", $list->listAddress))
        $list->listAddress = $assoSub["login"] . SUFFIXE_MAIL; //Do not show bounce parti of the email
      ?>
      <a class="nav-link navelem-lv-1" href="/agniacum/sublist.php?asso=<?= $assoSub["login"] ?>&list=<?= $list->listAddress ?>"><?= $list->listAddress ?></a>
  <?php endforeach ?>
</nav>
