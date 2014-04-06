<?php
/* Fichier des parametres du blog et des elements pour connection BDD */

// La racine du site exemple pendant mes tests j'etais en url http://blog.passion-tarn-et-garonne.info/V2
$PARAM_domaine	= "http://blog.passion-tarn-et-garonne.info";
$PARAM_racine 	= "/V2/";
$PARAM_geo_position		="44.04264037148951; 1.4389729499816894";
$PARAM_geo_placename	="SAINT ETIENNE DE TULMONT";
$PARAM_geo_region		="FR-fr";

// Uniquement ce qui s'affiche en page d'accueil, lors de l'affichage page billet c'est écrasé par ce qui vient de la BDD
$PARAM_title			= "Tarn-Et-Garonne, Photo Rando...";
$PARAM_description		= "Tarn et garonne ce département qui m'accueille, venez lire de nombreuses choses sur le Tarn-Et-Garonne.";

// Parametre de connection BDD
// L'usage de la BDD est fait via PDO en théorie cela rend compatible le code avec plusieurs BDD
// j'ai seulement testé via Mysql

$PARAM_hote='localhost'; // Host du serveur BDD
$PARAM_port='3306'; // Port de votre serveur (Mysql est souvent le 3306 en standard)
$PARAM_nom_bd='NomBdd'; // le nom de votre base de données
$PARAM_utilisateur='NomUtilisateur'; // nom d'utilisateur pour se connecter
$PARAM_mot_passe='MotDePasse'; // mot de passe de l'utilisateur pour se connecter

?>
