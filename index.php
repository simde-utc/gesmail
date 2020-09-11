<?php
  require_once("php/required.php");
  require_once("php/frags/header.php");
?>
<div class="col-md-9 d-md-block" id="content">
  <div class="container bloc">
    <h1 class="text-center text-break">Accueil du Gesmail</h1>
    <p>Bienvenue sur le Gesmail, tu peux ici gérer les mailing listes de ton asso ainsi que celles auxquelles tu est inscrit.</p>
    <p>La documentation pour les membres et associations est <a href="https://simde.gitlab.utc.fr/documentation/#/gesmail/">accessible ici</a> !</p>
    <p>Le menu liste les assos pour lesquelles des listes te concernent. Il existe trois types de droits.<p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Droit d'asso (Bureau restreint)</h1>
    <p>Pour les mailing listes de ton asso, tu peux : créer et supprimer des listes, mettre en place la modération, gérer les membres et leur droits sur chaque liste.</p>
    <p>Tu peux aussi gérer les personnes qui recevront les mails de l'asso.</p>
    <p>Les membres de la liste qui ne sont pas présents sur le portail ne pourront pas avoir de droits sur la mailing liste.</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Droit d'administration</h1>
    <p>Ce droit te permet d'ajouter / supprimer des utilisateurs sur des mailing listes, et, si la mailing liste est modérée, de choisir qui peut envoyer un mail sans passer par la modération.</p>
    <p>Pour chaque mailing liste où tu es administrateur, les messages nécessitant la modération te seront envoyés et tu pourras les accepter / les refuser.</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Droit personnel</h1>
    <p>Pour chaque mailing liste où tu es inscrit, tu possèdes le droit de te désinscrire.</p>
    <p>Tu recevras les messages pour toutes ces listes. Tu peux aussi, pour chaque liste, consulter tes droits.</p>
  </div>
  <div class="container bloc">
    <h1 class="text-center text-break">Les types de listes :</h1>
    <p>Il existe trois types de mailing listes : </p>
    <p>Les redirections : de la forme loginasso@assos.utc.fr, elles permettent de gérer qui reçoit les emails adressés à l'association</p>
    <p>Les listes automatiques : accessibles pour tous les membres de l'association, elles permettent de cibler un rôle donné dans l'association.</p>
    <p>Les listes classiques : des listes pour créer des groupes de membres, des mailing listes quoi !</p>
    <p>Note : la liste automatique '-tous' cible tous les membres de l'association cependant seule l'adresse de l'association peut envoyer un mail dessus.</p>
  </div>
</div>
<?php
  require_once("php/frags/footer.php");
?>
