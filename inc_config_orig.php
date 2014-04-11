<?php
/* Fichier des parametres du blog et des elements pour connection BDD */

// La racine du site exemple pendant mes tests j'etais en url http://blog.passion-tarn-et-garonne.info/V2
$PARAM_domaine	= "http://blog.passion-tarn-et-garonne.info";
$PARAM_domtitle	= "Tarn-Et-Garonne, Photo Rando...";
$PARAM_racine 	= "/V2/";
$PARAM_geo_position		="44.04264037148951; 1.4389729499816894";
$PARAM_geo_placename	="SAINT ETIENNE DE TULMONT";
$PARAM_geo_region		="FR-fr";

// Uniquement ce qui s'affiche en page d'accueil, lors de l'affichage page billet c'est écrasé par ce qui vient de la BDD
$PARAM_title			= "Tarn-Et-Garonne, Photo Rando..."; // par defaut
$PARAM_description		= "Tarn et garonne ce département qui m'accueille, venez lire de nombreuses choses sur le Tarn-Et-Garonne.";

// Parametre de connection BDD
// L'usage de la BDD est fait via PDO en théorie cela rend compatible le code avec plusieurs BDD
// j'ai seulement testé via Mysql

$PARAM_hote='localhost'; // Host du serveur BDD
$PARAM_port='3306'; // Port de votre serveur (Mysql est souvent le 3306 en standard)
$PARAM_nom_bd='nom de la bdd'; // le nom de votre base de données
$PARAM_utilisateur='nom utilisateur BDD'; // nom d'utilisateur pour se connecter
$PARAM_mot_passe='Mot de passe de la BDD'; // mot de passe de l'utilisateur pour se connecter

// Liste de lien ou de ce qu vous souhaitez qui apparaissent dans la sidebar
$PARAM_sidebar1	='
			<div class="text">
				<h2>Edito</h2>
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
			<h2>Petit rappel</h2>
				<a href="http://blog.passion-tarn-et-garonne.info/index.php/pages/Mentions-legales">Mentions légales</a><br />
			';
			
			
// Pour la publicite ici celle qui s'insere dans la home
$PARAM_pubhome ='<div align="right">
					<script type="text/javascript"><!--
						google_ad_client = "pub-";
						google_ad_width = 234;
						google_ad_height = 60;
						google_ad_format = "234x60_as";
						google_ad_type = "text";
						//2007-10-07: home blog
						google_ad_channel = "5716270810";
						google_color_border = "E6E6E6";
						google_color_bg = "FFFFFF";
						google_color_link = "0000FF";
						google_color_text = "341473";
						google_color_url = "008000";
						google_ui_features = "rc:10";
						//-->
					</script>
					<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
				</div>';

// Pour la publicite ici celle qui s'insere dans en entete de billet et fin de billet
$PARAM_pubpost ='<div style="margin-left:auto;margin-right:auto;">  
					<script type="text/javascript">
						<!--
						google_ad_client = "pub-";
						google_ad_width = 728;
						google_ad_height = 90;
						google_ad_format = "728x90_as";
						google_ad_type = "text";
						//2007-07-15: Billet
						google_ad_channel = "5497637139";
						google_color_border = "E6E6E6";
						google_color_bg = "FFFFFF";
						google_color_link = "0000FF";
						google_color_text = "341473";
						google_color_url = "008000";
						google_ui_features = "rc:10";
						//-->
					</script>
					<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
				</div>';
?>
