<?php
/* DotclearFL se veut un essai d'affichage de blog basé sur le moteur DOTCLEAR 
 * en se passant dans l'immédiat de fonctionalité majeur tel que le ping, les 
 * TAG 
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

/*
$dossier_cache = getcwd().'/cache/';
$secondes_cache = 60*60*12; // 12 heures
 
$url_cache = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$fichier_cache = $dossier_cache . md5($url_cache) . '.cache';
 
$fichier_cache_existe = ( @file_exists($fichier_cache) ) ? @filemtime($fichier_cache) : 0;
 
if ($fichier_cache_existe > time() - $secondes_cache ) {
  @readfile($fichier_cache);
  exit();
  }
ob_start();
*/

$timestart=microtime(true);
$page = 0;
// Voir pour rassembler les includes en 1 seul, pour gagner en temps à mesurer 
include("inc_config.php");
include("inc_fonctions.php");

/*
Blog ultra light
*/
$monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 

$url = feclateURL($_SERVER['REQUEST_URI'],$PARAM_racine);
//echo "<br />url[0]".$url[0];
//echo "<br />url[1]".$url[1]."<br />";

if ($url[0] == "404"){
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

// si c'est le sitemap qui est demandé
//echo "url=".$url[0];
if ($url[0] == "sitemap"){
	$query = "SELECT * FROM dc_BETA1post 
					WHERE post_status=1 
					AND blog_id = 'default'
					AND post_type = 'post'
					AND post_password IS NULL;";
	//echo "<br />".$query."<br />";
	$resultats=$connexion->query($query); // on va chercher tous les membres de la table qu'on trie par ordre croissant
	$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
	echo '<?xml version="1.0" encoding="UTF-8"?>'.chr(13);
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.chr(13);
	echo '<url>'.chr(13);
	echo '<loc>'.$PARAM_domaine.$PARAM_racine.'index.php/</loc>'.chr(13);
	echo '<priority>1.0</priority>'.chr(13);
	echo '<changefreq>daily</changefreq>'.chr(13);
	echo '</url>'.chr(13);
	echo '<url>'.chr(13);
	echo '<loc>'.$PARAM_domaine.$PARAM_racine.'index.php/feed/rss2</loc>'.chr(13);
	echo '<priority>1.0</priority>'.chr(13);
	echo '<changefreq>daily</changefreq>'.chr(13);
	echo '</url>'.chr(13);
	while( $ligne = $resultats->fetch() )
	{
		echo '<url>'.chr(13);
		echo '<loc>'.$PARAM_domaine.$PARAM_racine.'index.php/post'.$ligne->post_url.'</loc>'.chr(13);
		echo '<priority>1.0</priority>'.chr(13);
		echo '<changefreq>daily</changefreq>'.chr(13);
		echo '<lastmod>'.str_replace(" ","T",$ligne->post_upddt).'+00:00</lastmod>'.chr(13);
		echo '</url>'.chr(13);
	}
	$query = "SELECT * FROM dc_BETA1category 
					WHERE  
					blog_id = 'default';";
	//echo "<br />".$query."<br />";
	$resultats=$connexion->query($query); // on va chercher tous les membres de la table qu'on trie par ordre croissant
	$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
	while( $ligne = $resultats->fetch() )
	{
		echo '<url>'.chr(13);
		echo '<loc>'.$PARAM_domaine.$PARAM_racine.'index.php/category/'.$ligne->cat_url.'</loc>'.chr(13);
		echo '<priority>0.6</priority>'.chr(13);
		echo '<changefreq>weekly</changefreq>'.chr(13);
		echo '</url>'.chr(13);
	}
	echo '</urlset>'.chr(13);
	$resultats->closeCursor();
	return;
}

$msgerreur = "";
// Si nous sommes en presence d'une publication de commentaire
if (isset($_POST['bval'])){
	if ($_POST['bval'] == "Valider"){
		if (rtrim($_POST['c_name'])==""){
			$msgerreur .= "Nom ou pseudo obligatoire !<br />";
		}
		if (rtrim($_POST['c_mail'])==""){
			$msgerreur .= "Mail obligatoire !<br />";
		} 
		if (rtrim($_POST['c_content'])==""){
			$msgerreur .= "Contenu Obligatoire !<br />";	
		}
		if (strtoupper(rtrim($_POST['c_ctrl']))!="A"){
			$msgerreur .= "Code contrôle incorrect !<br />";
		}
		echo $msgerreur;
		// Si pas d'erreur nous pouvons faire le insert
		if ($msgerreur == ""){
			// Trouver le nombre de billet
			// Avant de faire l'insert il faut trouver le n° d'id
			$query = "SELECT max(comment_id)+1 FROM dc_BETA1comment";
			$resultats = $connexion->query($query);
			$maxId = (integer) $resultats->fetch(PDO::FETCH_COLUMN);
			
			$query = "INSERT INTO `tarnetgatest`.`dc_BETA1comment` 
						(`comment_id`, 
						`post_id`, 
						`comment_dt`, 
						`comment_tz`, 
						`comment_upddt`, 
						`comment_author`, 
						`comment_email`, 
						`comment_site`, 
						`comment_content`, 
						`comment_words`, 
						`comment_ip`, 
						`comment_status`, 
						`comment_spam_status`, 
						`comment_spam_filter`, 
						`comment_trackback`) 
						VALUES 
						('".$maxId."',
						 '802',
						 '".date("Y-m-d H:i:s")."',
						 'UTC', 
						 '".date("Y-m-d H:i:s")."', 
						 '".$_POST['c_name']."', 
						 '".$_POST['c_mail']."', 
						 '".$_POST['c_site']."', 
						 '".$_POST['c_content']."', 
						 NULL, 
						 '".$_SERVER["REMOTE_ADDR"]."', 
						 '1', 
						 '0', 
						 NULL, 
						 '0');";
			//echo $query;
			// on doit pourvoir faire un prepa et un select max dans la requete insert je pense à creuser. 
			$resultats=$connexion->prepare($query); // on va chercher tous les membres de la table qu'on trie par ordre croissant
			$resultats->execute();
		}
	} 
}


// On remonte de la BDD ce qu'il faut pour la colonne de droite
$last_comment = '<h2>Derniers commentaires</h2>';
$query = "SELECT * FROM dc_BETA1comment,dc_BETA1post 
					WHERE 
						dc_BETA1comment.post_id=dc_BETA1post.post_id 
						AND comment_status = 1 
						ORDER BY comment_dt DESC LIMIT 0,5";
$resultats=$connexion->query($query); // on va chercher tous les membres de la table qu'on trie par ordre croissant
$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
while( $comment = $resultats->fetch() ) // on récupère la liste des membres
{
		$last_comment .= "De ".$comment->comment_author." :<br />";
		$last_comment .= "<a href=\"".$PARAM_racine."index.php/post/".$comment->post_url."#c".$comment->comment_id."\">";
		//$droite .= $comment->comment_content;
		$last_comment .= substr(strip_tags($comment->comment_content),0,150)."...";
        $last_comment .= "<a><br /><br />";
}
$resultats->closeCursor(); // on ferme le curseur des résultats

// OK on remonte maintenant le contenu
$pourh4  = "";
$content = "";
$comment = "";
// trois sortes de contenu soit index soit post ou pages
// Cas par exemples pages static mentions legales
if ($url[0] == "pages"){
		
	$query = "SELECT * FROM dc_BETA1post 
					WHERE post_status=1 
					AND post_url='$url[1]' 
					AND blog_id = 'default'
					AND post_type = 'pages'
					AND post_password IS NULL LIMIT 0,1;";
	echo "<br />".$query."<br />";
	$resultats=$connexion->query($query); // on va chercher tous les membres de la table qu'on trie par ordre croissant
	$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
	while( $ligne = $resultats->fetch() ) // on récupère la liste des membres
	{
			$content .= "<div class=\"description\">";
			$content .= "	<h1 class=\"post-title\">";
			$content .= "		<a href=\"".$PARAM_racine."index.php/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .=  	$ligne->post_content_xhtml;
			$content .= "</div>";

	}
	$resultats->closeCursor(); // on ferme le curseur des résultats
}


if ($url[0] == "index" || $url[0] == "page"){
	
	// Trouver le nombre de billet
	$query = "SELECT post_id FROM dc_BETA1post 
					WHERE post_status=1
					AND blog_id = 'default'
					AND post_password IS NULL
					AND post_type = 'post';";
	//echo $query; 
	$resultats=$connexion->prepare($query); // on va chercher tous les membres de la table qu'on trie par ordre croissant
	$resultats->execute();
	$countbillet = $resultats->rowCount();
	
	// Comme je vais m'en servir deux fois je ne refais pas deux fois ->getLinks()
	$afflienpage = getPaginationString($url[1], $countbillet, 10, 1,$PARAM_domaine.$PARAM_racine, "index.php/page/");
	$content = $afflienpage;
	
	$query = "SELECT * FROM dc_BETA1post 
					WHERE post_status=1 
					AND blog_id = 'default'
					AND post_type = 'post'
					AND post_password IS NULL
					ORDER BY post_dt 
					DESC LIMIT ".(($url[1]-1)*10).",10;";
	//echo "<br />".$query."<br />";
	$resultats=$connexion->query($query); // on va chercher tous les membres de la table qu'on trie par ordre croissant
	$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
	$pub = 0;
	while( $ligne = $resultats->fetch() ) // on récupère la liste des membres
	{
		if ($pub == 0) {
			$content .= $PARAM_pubhome;
		}
		if ($pub == 1) {
			$content .= $PARAM_pubhome;
		}
		if ($pub == 3) {
			$content .= $PARAM_pubhome;
		}
		if ($pub == 8) {
			$content .= $PARAM_pubhome;
		}
		if ($pub == 9) {
			$content .= $PARAM_pubhome;
		}
		$pub++;
		if (rtrim($ligne->post_excerpt_xhtml)!=""){
			$content .= "<div class=\"description post-excerpt\">";
			$content .= "	<h1 class=\"post-title\">";
			$content .= "		<a href=\"".$PARAM_racine."index.php/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .= "	<div>";
			$content .= 		$ligne->post_excerpt_xhtml;
			$content .= "		<p class=\"read-it\">";
			$content .= "			<a title=\"Lire la suite ".$ligne->post_title."\" href=\"".$PARAM_racine."index.php/post/".$ligne->post_url."\">Lire la suite</a>";
			$content .= "		</p>";
			$content .= "	</div>";
			$content .= "</div>";
			$pourh4	.= "<div class=\"col_3\">";
			$pourh4	.= "<h4 class=\"post-title\"><a href=\"".$PARAM_racine."index.php/post/".$ligne->post_url."\">".$ligne->post_title."</a></h4>";
			$pourh4	.= $ligne->post_excerpt_xhtml;;
			$pourh4	.= "</div>";
		} else {
			$content .= "<div class=\"description\">";
			$content .= "	<h1 class=\"post-title\">";
			$content .= "		<a href=\"".$PARAM_racine."index.php/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .=  	$ligne->post_content_xhtml;
			$content .= "</div>";
		}
	}
	// Et je remet aussi la selection de page en bas
	$content .= $afflienpage;
	$resultats->closeCursor(); // on ferme le curseur des résultats
}

if ($url[0] == "post"){
	$query = "SELECT * FROM dc_BETA1post WHERE 
					blog_id = 'default'
					AND post_url='".$url[1]."'";
	//echo $query; 
	$resultats=$connexion->query($query); // on va chercher tous les membres de la table qu'on trie par ordre croissant
	$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
	while( $ligne = $resultats->fetch() ) // on récupère la liste des membres
	{
		$PARAM_title = $ligne->post_title;
		$PARAM_description = substr(strip_tags($ligne->post_content_xhtml),0,150);
		$content .= $PARAM_pubpost;
		$content .= "<article lang=\"fr-FR\">";
		$content .= "	<h1 class=\"post-title\">";
		$content .= 		$ligne->post_title;
		$content .= "	</h1>";
		$content .= "	<span class=\"description\">".$ligne->post_content_xhtml."</span>";
		$content .= "</article>";
		$content .= $PARAM_pubpost;
        //$content .= "<hr />";
	}
	// On recup les commentaires aussi
	$query = "SELECT * FROM dc_BETA1post,dc_BETA1comment 
					WHERE 
						dc_BETA1post.post_id=dc_BETA1comment.post_id 
						AND post_url='$url[1]' 
						AND comment_status = 1 
						ORDER BY comment_dt ASC";
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet
	$i = 0;
	$comment .=  "<h2>Commentaire(s) :</h2>";
	$comment .=  '	<div id="comment">';
	while( $ligne = $resultats->fetch() ) // on récupère la liste des membres
	{
		$i++;
		$comment .=  "<div id=\"c".$ligne->comment_id."\">";
		$comment .=  	$i.". Le ".$ligne->comment_dt." par ".$ligne->comment_author."<br />";
		$comment .=  	$ligne->comment_content;
		$comment .=  "</div>";
        $comment .=  "<hr />";
	}
	$resultats->closeCursor(); // on ferme le curseur des résultats
	
	$comment .= '
	    <form action="'.$PARAM_domaine.$PARAM_racine.'index.php/post/'.$url[1].'#pr" method="post" id="comment-form">
			<h2>Ajouter un commentaire</h2>
			<fieldset>
				<p class="field"><label for="c_name">Nom ou pseudo&nbsp;:</label>
					<input name="c_name" id="c_name" type="text" size="30" maxlength="255" value="" />
				</p>
				<p class="field"><label for="c_mail">Adresse email&nbsp;:</label>
					<input name="c_mail" id="c_mail" type="text" size="30" maxlength="255" value="" />
				</p>
				<p class="field"><label for="c_site">Site web (facultatif)&nbsp;:</label>
					<input name="c_site" id="c_site" type="text" size="30" maxlength="255" value="" />
				</p>
				<p style="display:none"><input name="f_mail" type="text" size="30" maxlength="255" value="" /></p>
				<p class="field"><label for="c_content">Commentaire&nbsp;:</label>
					<textarea name="c_content" id="c_content" cols="35" rows="7"></textarea>
				</p>
			</fieldset>      
			<p class="form-help">Le code HTML est affiché comme du texte et les adresses web sont automatiquement transformées.</p>
			<p class="field"><label for="c_site">Saisissez la première lettre de l\'alphabet&nbsp;:</label>
					<input name="c_ctrl" id="c_ctrl" type="text" size="5" maxlength="5" value="" />
				</p>
			<p><input type="submit" class="submit" name="bval" value="Valider" /></p>
		</form>';
	$comment .=  "	</div>";
}

// Trouver le nombre de billet
$query = "SELECT post_id FROM dc_BETA1post WHERE post_status=1";
//echo $query; 
$resultats=$connexion->prepare($query); // on va chercher tous les membres de la table qu'on trie par ordre croissant
$resultats->execute();
$countbillet = $resultats->rowCount();

?>
<!DOCTYPE html>
<html><head>
<title><?php echo $PARAM_title; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="" />
<meta name="copyright" content="" />
<link rel="stylesheet" type="text/css" href="<?php echo $PARAM_domaine.$PARAM_racine; ?>css/kickstart-grid.css" media="all" />
<!-- <link rel="stylesheet" type="text/css" href="<?php echo $PARAM_domaine.$PARAM_racine; ?>style.css" media="all" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $PARAM_domaine.$PARAM_racine; ?>js/kickstart.js"></script>  -->
    <style type="text/css">
	html {
		font-family: Ubuntu, Verdana, Tahoma, sans-serif;
		font-weight: 300;
		background-color: #fff;
		color: blue;
	}		
		
	h1 {
		/*
		margin-top: 0.8em;
		margin-top: 1vw;
		font-size: 12em;
		font-size: 12vw;
		
		line-height: 1;
		font-weight:normal;
		color:#aaa;
		text-shadow: 0 1px 1px #777,
					 1px 2px 1px #777,
					 1px 3px 1px #666,
					 2px 4px 1px #666,
					 2px 5px 1px #555,
					 2px 5px 6px #666,
					 2px 5px 12px #666;
		*/
	}
	
	#top h1 { 
		padding-top: 15px;
		padding-bottom: 5px;
		color: #09F;
		font-size : 2em;
		font-family : comic sans ms,Verdana,Arial,Geneva,Helvetica,sans-serif;
		line-height: 1;
		font-weight: normal;
		text-shadow: 0 1px 1px #777,
					 1px 2px 1px #777,
					 1px 3px 1px #666,
					 2px 4px 1px #666,
					 2px 5px 1px #555,
					 2px 5px 6px #666,
					 2px 5px 12px #666;
	}
	#top h1 a{
		text-decoration: none; 
	}	
	#top h1 a:visited{
		text-decoration: none;
		color: #09F; 
	}
	h1.post-title {
		font-size: 1.5em;
		color: #C00;
	}
	
		
	.post-title a {
		color: #C00;
	}	
	.post-excerpt p {
		margin-left: 120px;
	}
	.post-excerpt img {
		height: 100px;
		width: 100px;
		float: left;
	}
	.description {
		margin: 0px;
		padding: 0px;
		border: 1px solid #E8E8E8;
		min-height: 150px;
		background: #fff;
		clear: left;
		margin-bottom: 2em;
	}
	.read-it a {
		text-decoration: none;
	}
	.read-it a {
		background: none repeat scroll 0% 0% #09F;
		font-family: Georgia,serif;
		font-size: 12px;
		font-style: italic;
		font-weight: bold;
		height: 20px;
		line-height: 20px;
		text-align: center;
		width: 100px;
		/* z-index: 1200; */
		color: #FFF;
	}
	.sidebar, #comment {
		font-family: Verdana,Arial,Geneva,Helvetica,sans-serif;
		font-size: 0.8em;
		color: #000;
	}
	.sidebar a {
		color: black;
		text-decoration: none;
	}
	.sidebar a:visited {
		color: gray;
	}
	.sidebar a:hover {
		text-decoration: underline;
	}
	.sidebar h2, #sidebar h2 {
		font-size: 1.5em;
		margin: 0.83em 0px;
		font-weight: normal;
		font-family: Georgia,"Times New Roman",serif;
		color: #00008B;
		background: url('img/bg-title-sidebar.png') no-repeat scroll 100% 100% transparent;
	}
	#comment {
		font-size: 0.9em;
		color: #000;
	}
	
	
	/* pour la pagination home */
	div.pagination {
		padding: 3px;
		margin: 3px;
	}
	
	div.pagination a {
		padding: 2px 5px 2px 5px;
		margin: 2px;
		border: 1px solid #AAAADD;
		zoom: 100%;
		text-decoration: none; /* no underline */
		color: #000099;
	}
	div.pagination a:hover, div.pagination a:active {
		border: 1px solid #000099;

		color: #000;
	}
	div.pagination span.current {
		padding: 2px 5px 2px 5px;
		margin: 2px;
		border: 1px solid #000099;
		
		* zoom: 100%; 
		
		font-weight: bold;
		background-color: #000099;
		color: #FFF;
	}
	div.pagination span.disabled {
		padding: 2px 5px 2px 5px;
		margin: 2px;
		border: 1px solid #EEE;
		
		* zoom: 100%;
		
		color: #DDD;
	}
	
	* span.elipsis {zoom:100%}
	
    </style>


</head>
<body>

<!-- Menu Horizontal -->
<!--
<ul class="menu">
<li class="current"><a href="">Item 1</a></li>
<li><a href="">Item 2</a></li>
<li><a href=""><span class="icon" data-icon="R"></span>Item 3</a>
	<ul>
	<li><a href=""><span class="icon" data-icon="G"></span>Sub Item</a></li>
	<li><a href=""><span class="icon" data-icon="A"></span>Sub Item</a>
		<ul>
		<li><a href=""><span class="icon" data-icon="Z"></span>Sub Item</a></li>
		<li><a href=""><span class="icon" data-icon="k"></span>Sub Item</a></li>
		<li><a href=""><span class="icon" data-icon="J"></span>Sub Item</a></li>
		<li><a href=""><span class="icon" data-icon="="></span>Sub Item</a></li>
		</ul>
	</li>
	<li class="divider"><a href=""><span class="icon" data-icon="T"></span>li.divider</a></li>
	</ul>
</li>
<li><a href="">Item 4</a></li>
</ul>
-->
<div class="grid">
	
<!-- ===================================== END HEADER ===================================== -->
	 
<div class="col_12">
	<div id="top" class="col_12">
		<h1><a href="<?php echo $PARAM_domaine.$PARAM_racine; ?>"><?php echo $PARAM_domtitle;?></a></h1>
	</div>
	<div class="col_9">
		<?php echo $content; ?>
		<br />
		<?php echo $comment; ?>
	</div>
	
	<div id="sidebar" class="col_3">
		<div class="sidebar">
			<?php
			 echo $PARAM_sidebar1;
			 echo $last_comment;
			 echo $PARAM_sidebar2; 
			?>
		</div>
	</div>
	
	<hr />
		
</div>

</div><!-- END GRID -->

<!-- ===================================== START FOOTER ===================================== -->
<div class="clear"></div>
<div id="footer">
	<?php 
		$timeend=microtime(true);
		$time=$timeend-$timestart;
		echo "<br />Script execute en " . $time . " secondes"; 
	?>
</div>
</body>
</html>
<?php
/*
$pointeur = fopen($fichier_cache, 'w+');
fwrite($pointeur, ob_get_contents());
fclose($pointeur);
ob_end_flush();
*/
?>
