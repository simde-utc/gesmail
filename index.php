<?php
  require_once("php/required.php");
  require_once("php/frags/header.php");
?>
<div class="col-md-10 d-md-block" id="content">
  <div class="container bloc">
    <h1 class="text-center text-break">Accueil du Gesmail</h1>
    <p>Bienvenue sur le Gesmail, tu peux ici gérer les mailing listes de ton asso ou celles auxquelles tu est inscrit.</p>
    <p>Dans le menu, il y a 3 catégories: les mailing listes de ton asso (si tu est membre du bureau restreint), les mailing listes que tu administre et les mailing listes auxquelles tu est inscrit.</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Menu d'asso</h1>
    <p>Pour les mailing listes de ton asso, tu peux : créer et supprimer des listes, mettre en place la modération, gérer les membres et leur droits sur la liste.</p>
    <p>Tu peux aussi gérer les personnes qui recevront les mails de l'asso.</p>
    <p>Les membres de la liste qui ne sont pas présents sur le portail ne pourront pas avoir de droits sur la mailing liste.</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Menu d'administration</h1>
    <p>Dans ce menu, tu peux voir toutes les mailing listes sur lesquelles tu as le droit d'administrateur.</p>
    <p>Ce droit te permet d'ajouter / supprimer des utilisateurs, et, si la mailing liste est modérée, de choisir qui peut envoyer un mail sans passer par la modération.</p>
    <p>Pour chaque mailing liste apparaissant dans ce menu, les messages nécessitant la modération te seront envoyés et tu pourra les accepter / les refuser.</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Menu perso</h1>
    <p>Dans ce menu, tu peux voir toutes les mailing listes auxquelles tu est inscrit.</p>
    <p>Tu recevra les messages pour toutes ces listes. Tu peux aussi, pour chaque liste, consulter tes droits et te désinscrire.</p>
  </div>
</div>
<?php
  require_once("php/frags/footer.php");
?>
