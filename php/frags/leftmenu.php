<?php require_once(BASE_ROOT_GESMAIL . "/php/required.php"); ?>
<!-- New version of left bar hopefully easier to understand !-->
<nav class="navbar align-content-start flex-column align-items-baseline">
  <!-- First assos where user is bureau restreint -->
  <p href="#">Mailing listes par association :</p>
  <ul class="navbar list-unstyled">
  <?php
  foreach ($assosAdminPortail as $index => $asso) : //Show all lists (including automatic lists)?>
      <li class="navitem expandbtn" <?= (isset($_GET["asso"]) && $asso["login"] == htmlspecialchars($_GET["asso"])) ? "" : "unexpanded"; ?>>
        <span class="selectorExpandBtn">></span>
        <a href="/gesmail/asso.php?asso=<?= $asso["login"] ?>"><?= $asso["shortname"] ?></a> : (accès complets)
      </li>
      <ul class="navbar expandable">
        <li><a href="/gesmail/asso.php?asso=<?= $asso["login"] ?>">Créer une liste pour <?= $asso["shortname"] ?></a></li>
        <!-- We show all possile automatic lists -->
        <li>Listes automatiques : </li>
        <ul class="navbar list-unstyled">
          <?php foreach (AUTOMATICSUFFIX as $key => $suffixe) : ?>
            <li><?= $asso["login"] . "-$suffixe" . SUFFIXE_MAIL ?></li>
          <?php endforeach ?>
        </ul>
        <!--All lists where asso is owner -->
        <li>Listes de l'association : </li>
        <ul class="navbar list-unstyled">
          <?php
	    $assoSubscribedToList = array();
            $allListAsso = $sympaManager->lists($asso["login"] . SUFFIXE_MAIL);
            usort($allListAsso, function ($ml1, $ml2) { return strcmp($ml1->listAddress, $ml2->listAddress); });
            foreach ($allListAsso as $index => $list) :

              if(preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $list->listAddress))
                continue; //Do not show automatic lists

	      //If asso is subscribed to another asso list
	      if(!$list->isOwner) {
		$assoSubscribedToList[] = $list;
		continue;
	      }

              if(preg_match("/(-bounce@)/", $list->listAddress))
                $list->listAddress = $asso["login"] . SUFFIXE_MAIL; //Do not show the bounce part of email
            ?>
            <li><a href="/gesmail/list.php?asso=<?= $asso["login"] ?>&list=<?= $list->listAddress ?>"><?= $list->listAddress ?></a></li>
          <?php endforeach; ?>
        </ul>
	<?php
	if(!empty($assoSubscribedToList)) : ?>
	<li>Listes auxquelles votre association est inscrite :</li>
	<ul class="navbar list-unstyled">
          <?php foreach ($assoSubscribedToList as $key => $list) :
	    if(preg_match("/(-bounce@)/", $list->listAddress))
                $list->listAddress = $asso["login"] . SUFFIXE_MAIL; //Do not show the bounce part of email
	    ?>
            <li><?= $list->listAddress ?></li>
          <?php endforeach ?>
        </ul>
        <?php
	endif;
        if(!empty($assosSubSympa[$asso["login"]])) :
          ?><li>Listes où vous êtes inscrit : </li>
          <ul class="navbar list-unstyled">
            <?php
              usort($assosSubSympa[$asso["login"]], function ($ml1, $ml2) { return strcmp($ml1, $ml2); });
              foreach ($assosSubSympa[$asso["login"]] as $list) :
		if(preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $list))
                  continue; //Do not show automatic lists
                if(preg_match("/(-bounce@)/", $list))
                  $list = $asso["login"] . SUFFIXE_MAIL; //Do not show bounce (should not be here anyway)
                ?><li><a href="/gesmail/sublist.php?asso=<?= $asso["login"] ?>&list=<?= $list ?>"><?= $list ?></a></li><?php
              endforeach;
            ?>
          </ul>
          <?php
        endif;
      ?>
      </ul>
    <?php
  endforeach;
  ?>
  <div class="border-top my-3"></div>
  <!-- Now show lists for all members -->
  <?php
  foreach ($assosPosteAutoPortail as $index => $asso) : ?>
    <li class="navitem expandbtn" unexpanded>
      <span class="selectorExpandBtn">></span>
      <?= $asso["shortname"] ?> : (membre de l'asso)
    </li>
    <ul class="navbar expandable">
      <li>Listes automatiques : </li>
      <ul class="navbar list-unstyled">
      <?php
      foreach (AUTOMATICSUFFIX as $key => $suffixe) :
        ?><li><?= $asso["login"] . "-$suffixe" . SUFFIXE_MAIL ?></li><?php
      endforeach;
      ?>
    </ul>
    <?php
      if(!empty($assosAdminSympa[$asso["login"]])) :
        ?>
        <li>Listes que vous administrez : </li>
        <ul class="navbar list-unstyled">
          <?php
        usort($assosAdminSympa[$asso["login"]], function ($ml1, $ml2) { return strcmp($ml1, $ml2); });
          foreach ($assosAdminSympa[$asso["login"]] as $list) :
            if(preg_match("/(-bounce@)/", $list))
              $list = $asso["login"] . SUFFIXE_MAIL; //Do not show bounce (should not be here anyway)
            ?><li><a href="/gesmail/adminlist.php?asso=<?= $asso["login"] ?>&list=<?= $list ?>"><?= $list ?></a></li><?php
          endforeach;
          ?>
        </ul>
        <?php
      endif;

      if(!empty($assosSubSympa[$asso["login"]])) :
        ?><li>Listes où vous êtes inscrit : </li>
        <ul class="navbar list-unstyled">
          <?php
          usort($assosSubSympa[$asso["login"]], function ($ml1, $ml2) { return strcmp($ml1, $ml2); });
          foreach ($assosSubSympa[$asso["login"]] as $list) :
	    if(preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $list))
                continue; //Do not show automatic lists
            if(preg_match("/(-bounce@)/", $list))
              $list = $asso["login"] . SUFFIXE_MAIL; //Do not show bounce (should not be here anyway)
            ?><li><a href="/gesmail/sublist.php?asso=<?= $asso["login"] ?>&list=<?= $list ?>"><?= $list ?></a></li><?php
          endforeach;
          ?>
        </ul>
      <?php
      endif;
      ?>
    </ul>
    <?php
  endforeach;
  ?>
  <div class="border-top my-3"></div>
  <!-- Here, we show lists where user is subscribed / admin but not member of the asso -->
  <?php
  foreach ($assosOnlySubOrAdmin as $asso => $lists) :
    ?><li class="navitem expandbtn" unexpanded>
      <span class="selectorExpandBtn">></span>
      <?= $asso ?> : (membre / admin d'une liste)
    </li>
    <ul class="navbar expandable">
      <li>Admin</li>
      <ul class="navbar list-unstyled">
        <?php
        foreach ($lists["admin"] as $index => $list) :
          if(preg_match("/(-bounce@)/", $list))
            $list = $asso . SUFFIXE_MAIL; //Do not show bounce
            ?><li><a href="/gesmail/adminlist.php?asso=<?= $asso ?>&list=<?= $list ?>"><?= $list ?></a></li><?php
        endforeach; ?>
      </ul>
      <li>Subscriber</li>
      <ul class="navbar list-unstyled">
        <?php
        foreach ($lists["subscriber"] as $index => $list) :
	  if(preg_match("/[[:<:]](". implode('|', AUTOMATICSUFFIX) .")[[:>:]]/", $list))
                continue; //Do not show automatic lists
          if(preg_match("/(-bounce@)/", $list))
            $list = $asso . SUFFIXE_MAIL; //Do not show bounce
              ?><li><a href="/gesmail/sublist.php?asso=<?= $asso ?>&list=<?= $list ?>"><?= $list ?></a></li><?php
        endforeach;
        ?>
      </ul>
      <?php
    endforeach;
    ?>
  </ul>
</nav>
