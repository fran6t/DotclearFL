<?php
/************************************************************************/
/* Version 0.9															*/
/* Fichier des parametres du blog et des elements pour connection BDD 	*/


// La racine du site exemple pendant mes tests j'etais en url http://blog.passion-tarn-et-garonne.info/V2
// Voici donc la configuration que j'avais mise pour y correspondre

$PARAM_domaine	= "http://blog.passion-tarn-et-garonne.info";
$PARAM_domtitle	= "Tarn-Et-Garonne, Photo Rando...";
$PARAM_copyright ="www.passion-tarn-et-garonne.info";
$PARAM_contact = "info@passion-tarn-et-garonne.info";
$PARAM_nom = "Francis Trautmann";
$PARAM_script = "index.php";
$PARAM_racine 	= "/V3/";
$PARAM_urltot = $PARAM_domaine.$PARAM_racine;
$PARAM_geo_position		="44.04264037148951; 1.4389729499816894";
$PARAM_geo_placename	="SAINT ETIENNE DE TULMONT";
$PARAM_geo_region		="FR-fr";

// Uniquement ce qui s'affiche en page d'accueil, lors de l'affichage page billet c'est écrasé par ce qui vient de la BDD
// Partie meta dans le Header de la ou des pages générées
$PARAM_title			= "Tarn-Et-Garonne, Photo Rando..."; // par defaut
$PARAM_description		= "Tarn et garonne ce département qui m'accueille, venez lire de nombreuses choses sur le Tarn-Et-Garonne.";


/****************************************************************************************************/
/* Parametre de connection BDD																		*/
/* L'usage de la BDD est fait via PDO en théorie cela rend compatible le code avec plusieurs BDD	*/
/* j'ai seulement testé via Mysql																	*/

$PARAM_hote='localhost'; // Host du serveur BDD
$PARAM_port='3306'; // Port de votre serveur (Mysql est souvent le 3306 en standard)
$PARAM_nom_bd='nom de la bdd'; // le nom de votre base de données
$PARAM_utilisateur='nom utilisateur BDD'; // nom d'utilisateur pour se connecter
$PARAM_mot_passe='Mot de passe de la BDD'; // mot de passe de l'utilisateur pour se connecter


/************************************************************************************************/
/* Liste de lien ou ce que vous voulez qui apparaissent dans la sidebar ( Colonne à droite)		*/
/* Il y a deux variables, car entre les deux j'affiche les 5 derniers commentaires du blog		*/

$PARAM_sidebar1	='
			<div class="text">
				<h2>Edito :</h2>
				<div class="follow">
					<span class="follow-title">Abonnez-vous&nbsp;!</span>
					<a class="follow-rss external" href="http://blog.passion-tarn-et-garonne.info/index.php/feed/rss2" target="_blank">par RSS</a>
					<a class="follow-twitter external" href="http://twitter.com/fran6t" target="_blank">par Twitter</a>
				</div>
				<p>Je suis ici pour partager ma passion de la photo, mais aussi du département qui m\'accueille maintenant depuis plusieurs années à savoir le Tarn-Et-Garonne (Sud Ouest de la France).</p>
			</div>
			<h2>Mon Web :</h2>
				<a href="http://www.passion-tarn-et-garonne.info/galerie/index.php">Tarn-Et-Garonne en photos ou images</a><br />
				<a href="http://www.passion-tarn-et-garonne.info/galflash/Montauban-Notre-Dame.php"> Panoramique du Tarn-Et-Garonne</a><br />
				<a href="http://video.passion-tarn-et-garonne.info"> Vidéos du Tarn-Et-Garonne</a><br />
				<a href="http://www.myouaibe.com/">P\'tit blog Informatique</a><br />
				<a href="http://www.photographes-anthologie.fr/"> Photographes Anthologie</a><br />';
				
$PARAM_sidebar2	='				
			<h2>Petit rappel :</h2>
				<a href="http://blog.passion-tarn-et-garonne.info/index.php/pages/Mentions-legales">Mentions légales</a><br />
			';

/********************************************************************************************************/
/* le marqueur stat ou ce vous souhaitez qui sera ne pied de page 										*/
/*																										*/

$PARAM_stat = '';
			
/********************************************************************************************************/			
/* Pour ceux qui ont un compte adsence chez google il suffit de remplacer par le code obtenu sur votre  */
/* interface google j'ai mis en remarque mon code google pour ceux qui ne comprendrais pas 				*/
/* Ce premier paramètre permet l'affichage de pub sur la page d'accueil 5 fois entre les billets		*/

$PARAM_pubhome ='';
/*
$PARAM_pubhome ='<div align="right">
					<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
					<!-- New-photo-inter-billet -->
					<ins class="adsbygoogle"
					style="display:inline-block;width:234px;height:60px"
					data-ad-client="ca-pub-0563218833339308"
					data-ad-slot="3028204284"></ins>
					<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
				</div>';
*/

/**********************************************************************************************************/
/* Ce deuxieme parametre est pour afficher une pub au dessus et au dessus lorsque l'on est dans un billet */

$PARAM_pubpost = '';
/*
$PARAM_pubpost ='<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
				<!-- Responsive pour billet -->
				<ins class="adsbygoogle"
					style="display:block"
					data-ad-client="ca-pub-0563218833339308"
					data-ad-slot="2842402345"
					data-ad-format="auto"></ins>
				<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
*/




/****************************************************************************************/
/* Partie pour la configuration du flux rss 										 	*/
/* Rien à modifier ici car les variables utilisées sont celles initialisées au dessus 	*/
$PARAM_xml = '<?xml version="1.0" encoding="utf-8"?>'.chr(13);
$PARAM_xml .= '<rss version="2.0"
 xmlns:dc="http://purl.org/dc/elements/1.1/"
 xmlns:wfw="http://wellformedweb.org/CommentAPI/"
 xmlns:content="http://purl.org/rss/1.0/modules/content/"
 xmlns:atom="http://www.w3.org/2005/Atom">'.chr(13);
$PARAM_xml .= '<channel>'.chr(13);
$PARAM_xml .= ' <title>'.$PARAM_domtitle.'</title>'.chr(13);
$PARAM_xml .= ' <link>'.$PARAM_domaine.$PARAM_racine.'index.php/</link>'.chr(13);
$PARAM_xml .= '<atom:link href="'.$PARAM_domaine.$PARAM_racine.'index.php/feed/rss2" rel="self" type="application/rss+xml" />'.chr(13);
$PARAM_xml .= ' <description>'.$PARAM_description.'</description>'.chr(13);
/* Si vous disposez d'une image representative de votre flux remplissez et decommentez */
//$PARAM_xml .= ' <image>'.chr(13);
//$PARAM_xml .= '   <title>Titre de l\'image</title>'.chr(13);
//$PARAM_xml .= '   <url>http://www.craym.eu/logo.png</url> '.chr(13);
//$PARAM_xml .= '   <link>http:///www.craym.eu/tutoriels.html</link> '.chr(13);
//$PARAM_xml .= '   <description>Toute nos tutoriels sur Craym.eu !</description>'.chr(13);
//$PARAM_xml .= '   <width>80</width>'.chr(13);
//$PARAM_xml .= '   <height>80</width>'.chr(13);
//$PARAM_xml .= ' </image>'.chr(13);
$PARAM_xml .= ' <language>fr</language>'.chr(13);
$PARAM_xml .= ' <copyright>'.$PARAM_copyright.'</copyright>'.chr(13);
$PARAM_xml .= ' <managingEditor>'.$PARAM_contact.' ('.$PARAM_nom.')</managingEditor>'.chr(13);
$PARAM_xml .= ' <category>Tutoriel</category>'.chr(13);
//$PARAM_xml .= ' <generator>PHP/MySQL</generator>'.chr(13);
$PARAM_xml .= ' <docs>http://www.rssboard.org</docs>'.chr(13);
?>
