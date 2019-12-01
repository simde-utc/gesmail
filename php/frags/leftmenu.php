<nav class="navbar align-content-start">
  <!-- First, all the mailing lists where user is from restrained -->
  <?php foreach ($assosAdminPortail as $key => $asso) : ?>
    <a class="nav" href="/agniacum/asso.php?asso=<?= $asso["login"] ?>">Mailing listes de <?= $asso["shortname"] ?></a>
    <?php
      $orderdList = $sympaManager->lists($asso["login"] . SUFFIXE_MAIL);
      usort($orderdList, function ($ml1, $ml2) { return strcmp($ml1->listAddress, $ml2->listAddress); });
      foreach ($orderdList as $index => $list) :
        if(preg_match("/(-bounce@)/", $list->listAddress))
          $list->listAddress = $asso["login"] . SUFFIXE_MAIL; //Do not show bounce
        ?>
      <a class="nav-link navelem-lv-1" href="/agniacum/list.php?asso=<?= $asso["login"] ?>&list=<?= $list->listAddress ?>"><?= $list->listAddress ?></a>
    <?php endforeach ?>
    <a class="nav-link navelem-lv-1" href="/agniacum/asso.php?asso=<?= $asso["login"] ?>">Créer une liste pour <?= $asso["shortname"] ?></a>
  <?php endforeach ?>
</nav>
<hr/>
<nav class="navbar align-content-start">
  <!-- Then, all the mailing lists where user has admin permission -->
  <a class="nav-link" href="#">Mailing listes que vous administrez</a>
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
<nav class="navbar align-content-start">
  <!-- last, all the mailing lists where user is a subscriber -->
  <a class="nav-link" href="#">Mailing listes auxquelles vous êtes inscrit</a>
  <?php
    $orderdList = $sympaManager->lists($resourceOwner["email"]);
    usort($orderdList, function ($ml1, $ml2) { return strcmp($ml1->listAddress, $ml2->listAddress); });
    foreach ($orderdList as $index => $list) :
      preg_match(REGEX_LOGINASSO, $list->listAddress, $loginAsso);
      $assoSub = $portailManager->getPortail(PORTAIL_API_URL . "/assos/" . $loginAsso[0], $accessToken);

      if(preg_match("/(-bounce@)/", $list->listAddress))
        $list->listAddress = $assoSub["login"] . SUFFIXE_MAIL; //Do not show bounce
      ?>
      <a class="nav-link navelem-lv-1" href="/agniacum/sublist.php?asso=<?= $assoSub["login"] ?>&list=<?= $list->listAddress ?>"><?= $list->listAddress ?></a>
  <?php endforeach ?>
</nav>
