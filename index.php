<?php
/* DotclearFL se veut un essai d'affichage de blog basé sur le moteur DOTCLEAR 
 * en se passant dans l'immédiat de fonctionalité majeur tel que le ping, les 
 * TAG, les catégories 
 * 
 * L'idée est de se servir de la BDD DOTCLEAR pour
 * 	- Affichage en mode responsive max 1140 pixels 
 *	- Afficher la page d'accueil
 *	- Afficher la page billet avec ses commentaires
 * 	- Autoriser le postage de commentaires
 * 	- Mettre un maximum de chose en cache pour limiter au maximum les acces BDD
 * 
 * 
 * */
 
$timestart=microtime(true);

// Voir pour rassembler les includes en 1 seul, pour gagner en temps à mesurer 
include("inc_config.php");
include("inc_fonctions.php");
/*
Blog ultra light
*/
$monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 

$url = feclateURL($_SERVER['REQUEST_URI'],$PARAM_racine);
if ($url == "404"){
	header("HTTP/1.0 404 Not Found");
	return;
}

try
{
	$connexion = new PDO('mysql:host='.$PARAM_hote.';dbname='.$PARAM_nom_bd, $PARAM_utilisateur, $PARAM_mot_passe);
	$connexion->exec("SET CHARACTER SET utf8");
}
 
catch(Exception $e)
{
	echo 'Erreur : '.$e->getMessage().'<br />';
	echo 'N° : '.$e->getCode();
	die();
}

// On remonte de la BDD ce qu'il faut pour la colonne de droite
$droite = "";
$resultats=$connexion->query("SELECT * FROM dc_BETA1comment WHERE comment_status = 1 ORDER BY comment_dt DESC LIMIT 0,5"); // on va chercher tous les membres de la table qu'on trie par ordre croissant
$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
while( $comment = $resultats->fetch() ) // on récupère la liste des membres
{
		$droite .= $comment->comment_content;
        $droite .= "<hr />";
}
$resultats->closeCursor(); // on ferme le curseur des résultats

// OK on remonte maintenant le contenu
$content = "";
$comment = "";
// Deux sortes de contenu soit index soit post
if ($url == "index"){
	$resultats=$connexion->query("SELECT * FROM dc_BETA1post ORDER BY post_dt DESC LIMIT 0,10"); // on va chercher tous les membres de la table qu'on trie par ordre croissant
	$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
	while( $ligne = $resultats->fetch() ) // on récupère la liste des membres
	{
		$content .= "<h2><a href=\"".$PARAM_racine."index.php/post/".$ligne->post_url."\">".$ligne->post_title."</a></h2>";
		if (rtrim($ligne->post_excerpt_xhtml)!=""){
			$content .=  $ligne->post_excerpt_xhtml;
		} else {
			$content .=  $ligne->post_content_xhtml;
		}
        $content .=  "<hr />";
	}
	$resultats->closeCursor(); // on ferme le curseur des résultats
} else {
	$resultats=$connexion->query("SELECT * FROM dc_BETA1post WHERE post_url='$url'"); // on va chercher tous les membres de la table qu'on trie par ordre croissant
	$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
	while( $ligne = $resultats->fetch() ) // on récupère la liste des membres
	{
		$PARAM_title = $ligne->post_title;
		$PARAM_description = stripTAG(TruncateHTML::truncateChars($ligne->post_content_xhtml, '150'));
		$content .= "<h2>".$ligne->post_title."</h2>";
		$content .=  $ligne->post_content_xhtml;
        $content .=  "<hr />";
	}
	// On recup les commentaires aussi
	$query = "SELECT * FROM dc_BETA1post,dc_BETA1comment 
					WHERE 
						dc_BETA1post.post_id=dc_BETA1comment.post_id 
						AND post_url='$url' 
						AND comment_status = 1 
						ORDER BY comment_dt DESC";
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
	while( $ligne = $resultats->fetch() ) // on récupère la liste des membres
	{
		$comment .=  $ligne->comment_content;
        $comment .=  "<hr />";
	}
	$resultats->closeCursor(); // on ferme le curseur des résultats
}

?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

    <title><?php echo $PARAM_title; ?></title>
    <meta name="description" content="<?php echo $PARAM_description; ?>" />
    <meta name="geo.position" content="<?php echo $PARAM_geo_position; ?>" />
	<meta name="geo.placename" content="<?php echo $PARAM_geo_placename; ?>" />
	<meta name="geo.region" content="<?php echo $PARAM_geo_region; ?>FR-fr" />
    
    <meta name="robots" content="index, follow" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="<?php echo $PARAM_domaine.$PARAM_racine; ?>css/1140.css" />
    <style type="text/css">
		body {
			background: #5bc1af;
			color: #fff;
			font-family: Ubuntu, Verdana, Tahoma, serif;
		}
		h1 {
			margin: 20px 0 30px;
			text-align:center;
		}
		a, h2 {
			color: #fff;
			margin: 14px 0;
		}
        .examples .row p {
			background: #fff;
			-webkit-border-radius: 4px;
			-moz-border-radius: 4px;
			border-radius: 4px;
            color:#777;
            padding:4px 0;
            text-align:center;
        }
    </style>
</head>
<body>
    <div class="container12 examples">
        <div class="row">
            <div class="column12 examples">
                <h1><a href="http://blog.passion-tarn-et-garonne.info<?php echo $PARAM_racine;?>">Tarn-Et-Garonne, Photo Rando...</a></h1>
            </div>
        </div>
        
        <div class="row">
            <div class="column8 examples">
				<?php echo $content; ?>
				<br />
				<?php echo $comment; ?>
            </div>
            <div class="column4">
				<?php echo $droite; ?>
			</div>
        </div>
        <!-- footer -->
		<div class="row">
            <div class="column12">
                <p>
					<?php 
					$timeend=microtime(true);
					$time=$timeend-$timestart;
					echo "<br />Script execute en " . $time . " secondes"; 
					?>
				</p>
            </div>
        </div>
    </div>
</body>
</html>
