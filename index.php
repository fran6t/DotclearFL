<?php
/*********************************************************************************************/
/* V0.9.1 du 19/04/2014
 * 
 * DotclearFL se veut un essai d'affichage de blog basé sur le moteur DOTCLEAR 
 * en se passant dans l'immédiat de fonctionalité majeur tel que le ping, les 
 * flux 
 * 
 * L'idée est de se servir de la BDD DOTCLEAR pour
 * 	- Affichage en mode responsive max 1140 pixels 
 *	- Afficher la page d'accueil
 *	- Afficher la page billet avec ses commentaires
 *  - Afficher les pages tag
 *  - Afficher les pages category
 * 	- Afficher les pages page   (pages statiques de DC)
 * 	- Autoriser le postage de commentaires
 * 	- Mettre un maximum de chose en cache pour limiter au maximum les acces BDD
 *  - Produire le sitemap
 *  - Produire le flux principal des billets
 * 
 * ********************************************************************************************
 *                  A T E N T I O N
 * 	Si votre blog DOTCLEAR est configuré en mode query ( passage des parametres avec le ? )
 *  ce n'est pas supporté par cette version il faut alors corriger la fonction f_eclate en 
 *  conséquence
 * 
 * *********************************************************************************************/

/*  Pour l'instant le cache n'est pas activé ce sera pour la version 1.0
 * 
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
define("DEBUG", false);

$noindex = "<meta name=\"robots\" content=\"index, follow\" />"; // Les pages category sont marquée NOINDEX pour eviter duplicate content.

// Voir pour rassembler les includes en 1 seul, pour gagner en temps à mesurer 
include("inc_config.php");
include("inc_fonctions.php");

$monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
$url = feclateURL($_SERVER['REQUEST_URI'],$PARAM_racine,$PARAM_script);
if (DEBUG) echo "<br />url[0]".$url[0];
if (DEBUG) echo "<br />url[1]".$url[1]."<br />";

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

/* reconstitution du feed rss */
if ($url[0] == "feed"){
	
	//on lit les 25 premiers éléments à partir du dernier ajouté dans la base de données
	$index_selection = 0;
	$limitation = 20;
	$reponse = $connexion->prepare('SELECT * FROM '.$PARAM_prefixBDD.'post ORDER BY post_upddt DESC LIMIT :index_selection, :limitation') or die(print_r($connexion->errorInfo()));
	$reponse->bindParam('index_selection', $index_selection, PDO::PARAM_INT);
	$reponse->bindParam('limitation', $limitation, PDO::PARAM_INT);
	$reponse->execute();
	while ($donnees = $reponse->fetch()){
		$PARAM_xml .= "<item>".chr(13);;
		$PARAM_xml .= "<title>".htmlspecialchars($donnees['post_title'])."</title>".chr(13);
		$PARAM_xml .= "<link>".htmlspecialchars($PARAM_domaine.$PARAM_racine.$PARAM_script."/".$donnees['post_url'])."</link>".chr(13);
		$PARAM_xml .= "<guid isPermaLink=\"true\">".htmlspecialchars($PARAM_domaine.$PARAM_racine.$PARAM_script."/".$donnees['post_url'])."</guid>".chr(13);
		$PARAM_xml .= "<pubDate>".(date("D, d M Y H:i:s O", strtotime($donnees['post_upddt'])))."</pubDate>".chr(13);
		$PARAM_xml .= "<description>".htmlspecialchars(stripcslashes($donnees['post_content_xhtml']))."</description>".chr(13);
		$PARAM_xml .= "</item>".chr(13);
	}
	//Puis on termine la requête
	$reponse->closeCursor();
 
	//Et on ferme le channel et le flux RSS.
	$PARAM_xml .= '</channel>'.chr(13);
	$PARAM_xml .= '</rss>'.chr(13);
	header("Content-Type: application/xml; charset=UTF-8");
	echo $PARAM_xml;
	return;
}
/* fin : reconstitution du feed rss */

/* reconstitution du sitemap */
if ($url[0] == "sitemap"){
	$query = "SELECT * FROM ".$PARAM_prefixBDD."post 
					WHERE post_status=1 
					AND blog_id = 'default'
					AND post_type = 'post'
					AND post_password IS NULL;";
	if (DEBUG) echo "<br />".$query."<br />";
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ);
	echo '<?xml version="1.0" encoding="UTF-8"?>'.chr(13);
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.chr(13);
	echo '<url>'.chr(13);
	echo '<loc>'.$PARAM_domaine.$PARAM_racine.$PARAM_script.'</loc>'.chr(13);
	echo '<priority>1.0</priority>'.chr(13);
	echo '<changefreq>daily</changefreq>'.chr(13);
	echo '</url>'.chr(13);
	echo '<url>'.chr(13);
	echo '<loc>'.$PARAM_domaine.$PARAM_racine.$PARAM_script.'/feed/rss2</loc>'.chr(13);
	echo '<priority>1.0</priority>'.chr(13);
	echo '<changefreq>daily</changefreq>'.chr(13);
	echo '</url>'.chr(13);
	while( $ligne = $resultats->fetch() ){
		echo '<url>'.chr(13);
		echo '<loc>'.$PARAM_domaine.$PARAM_racine.$PARAM_script.'/post'.$ligne->post_url.'</loc>'.chr(13);
		echo '<priority>1.0</priority>'.chr(13);
		echo '<changefreq>daily</changefreq>'.chr(13);
		echo '<lastmod>'.str_replace(" ","T",$ligne->post_upddt).'+00:00</lastmod>'.chr(13);
		echo '</url>'.chr(13);
	}
	$query = "SELECT * FROM ".$PARAM_prefixBDD."category 
					WHERE  
					blog_id = 'default';";
	if (DEBUG) echo "<br />".$query."<br />";
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ);
	while( $ligne = $resultats->fetch() ){
		echo '<url>'.chr(13);
		echo '<loc>'.$PARAM_domaine.$PARAM_racine.$PARAM_script.'/category/'.$ligne->cat_url.'</loc>'.chr(13);
		echo '<priority>0.6</priority>'.chr(13);
		echo '<changefreq>weekly</changefreq>'.chr(13);
		echo '</url>'.chr(13);
	}
	echo '</urlset>'.chr(13);
	$resultats->closeCursor();
	return;
}
/* fin : reconstitution du feed rss */

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
			$msgerreur.= "<br />";
			$msgerreur.= "**********************************************************<br />";
			$msgerreur.= "********* Code contrôle incorrect !            ***********<br />";
			$msgerreur.= "**********************************************************<br />";
			$msgerreur.= "<br />";

		}
		// Si pas d'erreur nous pouvons faire le insert
		if ($msgerreur == ""){
			// Trouver le nombre de billet
			// Avant de faire l'insert il faut trouver le n° d'id
			$query = "SELECT max(comment_id)+1 FROM ".$PARAM_prefixBDD."comment";
			$resultats = $connexion->query($query);
			$maxId = (integer) $resultats->fetch(PDO::FETCH_COLUMN);
			
			$query = "INSERT INTO `".$PARAM_nom_bd."`.`".$PARAM_prefixBDD."comment` 
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
						 '".$_POST['postid']."',
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
			if (DEBUG) echo "<br />".$query."<br />";
			// on doit pourvoir faire un prepa et un select max dans la requete insert je pense à creuser. 
			$resultats=$connexion->prepare($query);
			$resultats->execute();
			$msgerreur.= "<br />";
			$msgerreur.= "**********************************************************<br />";
			$msgerreur.= "********* Commentaire bien reçu merci à vous ! ***********<br />";
			$msgerreur.= "**********************************************************<br />";
			$msgerreur.= "<br />";
		}
	} 
}


/* On remonte de la BDD ce qu'il faut pour la colonne de droite */
$last_comment = '<h2>Derniers commentaires</h2>';
$query = "SELECT * FROM ".$PARAM_prefixBDD."comment,".$PARAM_prefixBDD."post 
					WHERE 
						".$PARAM_prefixBDD."comment.post_id=".$PARAM_prefixBDD."post.post_id 
						AND comment_status = 1 
						ORDER BY comment_dt DESC LIMIT 0,5";
if (DEBUG) echo "<br />".$query."<br />";
$resultats=$connexion->query($query);
$resultats->setFetchMode(PDO::FETCH_OBJ);
while( $comment = $resultats->fetch() ){
		$last_comment .= "De ".$comment->comment_author." :<br />";
		$last_comment .= "<a href=\"".$PARAM_racine.$PARAM_script."/post/".$comment->post_url."#c".$comment->comment_id."\">";
		$last_comment .= substr(strip_tags($comment->comment_content),0,150)."...";
        $last_comment .= "<a><br /><br />";
}
$resultats->closeCursor(); // on ferme le curseur des résultats
/* Fin On remonte de la BDD ce qu'il faut pour la colonne de droite */


// OK on remonte maintenant le contenu
$pourh4  = "";
$content = "";
$comment = "";

/* Traitement pour les pages de type pages (cas des pages statiques de DOTCLEAR) */
if ($url[0] == "pages"){
		
	$query = "SELECT * FROM ".$PARAM_prefixBDD."post 
					WHERE post_status=1 
					AND post_url='$url[1]' 
					AND blog_id = 'default'
					AND post_type = 'page'
					AND post_password IS NULL LIMIT 0,1;";
	if (DEBUG) echo "<br />".$query."<br />";
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ);
	while( $ligne = $resultats->fetch() ){
			$content .= "<div class=\"description\">";
			$content .= "	<h1 class=\"post-title\">";
			$content .= "		<a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .=  	$ligne->post_content_xhtml;
			$content .= "</div>";

	}
	$resultats->closeCursor(); // on ferme le curseur des résultats
}
/* Fin Traitement pour les pages de type pages (cas des pages statiques de DOTCLEAR) */


if ($url[0] == "index" || $url[0] == "page"){
	
	// Trouver le nombre de billet
	$query = "SELECT post_id FROM ".$PARAM_prefixBDD."post 
					WHERE post_status=1
					AND blog_id = 'default'
					AND post_password IS NULL
					AND post_type = 'post';";
	if (DEBUG) echo "<br />".$query."<br />";
	$resultats=$connexion->prepare($query); 
	$resultats->execute();
	$countbillet = $resultats->rowCount();
	
	// Comme je vais m'en servir deux fois je ne refais pas deux fois ->getLinks()
	$afflienpage = getPaginationString($url[1], $countbillet, 10, 1,$PARAM_domaine.$PARAM_racine, $PARAM_script."/page/");
	$content = $afflienpage;
	
	$query = "SELECT * FROM ".$PARAM_prefixBDD."post, ".$PARAM_prefixBDD."category  
					WHERE post_status=1
					AND ".$PARAM_prefixBDD."post.cat_id =  ".$PARAM_prefixBDD."category.cat_id
					AND ".$PARAM_prefixBDD."post.blog_id = 'default'
					AND post_type = 'post'
					AND post_password IS NULL
					ORDER BY post_dt 
					DESC LIMIT ".(($url[1]-1)*10).",10;";
	if (DEBUG) echo "<br />".$query."<br />";
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ);
	$pub = 0;
	while( $ligne = $resultats->fetch() ){
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
			$content .= "		<a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .= "	<div class=\"post-tags\">";
			$content .= "		Le ".date('d/m/Y',strtotime($ligne->post_creadt))." dans ";
			$content .= "		<span class=\"categ\"><a href=\"".$PARAM_domaine.$PARAM_racine.$PARAM_script."/category/".urlencode($ligne->cat_url)."\">[".$ligne->cat_title."]</a></span>";
			$content .= "		<br />Tag(s): ";
			$content .= 		f_lestags($PARAM_domaine.$PARAM_racine.$PARAM_script.'/tag/',$ligne->post_meta);
			$content .= "	</div>";
			$content .= "	<div>";
			$content .= 		$ligne->post_excerpt_xhtml;
			$content .= "		<p class=\"read-it\">";
			$content .= "			<a title=\"Lire la suite ".$ligne->post_title."\" href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">Lire la suite</a>";
			$content .= "		</p>";
			$content .= "	</div>";
			$content .= "</div>";
			$pourh4	.= "<div class=\"col_3\">";
			$pourh4	.= "<h4 class=\"post-title\"><a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a></h4>";
			$pourh4	.= $ligne->post_excerpt_xhtml;;
			$pourh4	.= "</div>";
		} else {
			$content .= "<div class=\"description\">";
			$content .= "	<h1 class=\"post-title\">";
			$content .= "		<a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .= "	<div class=\"post-tags\">";
			$content .= "		Le ".date('d/m/Y',strtotime($ligne->post_creadt))." dans ";
			$content .= "		<span class=\"categ\"><a href=\"".$PARAM_domaine.$PARAM_racine.$PARAM_script."/category/".urlencode($ligne->cat_url)."\">[".$ligne->cat_title."]</a></span>";
			$content .= "		<br />Tag(s): ";
			$content .= 		f_lestags($PARAM_domaine.$PARAM_racine.$PARAM_script.'/tag/',$ligne->post_meta);
			$content .= "	</div>";
			$content .=  	$ligne->post_content_xhtml;
			$content .= "</div>";
		}
	}
	// Et je remet aussi la selection de page en bas
	$content .= $afflienpage;
	$resultats->closeCursor(); // on ferme le curseur des résultats
}

if ($url[0] == "post"){
	$query = "SELECT * FROM ".$PARAM_prefixBDD."post,".$PARAM_prefixBDD."category WHERE 
					".$PARAM_prefixBDD."post.blog_id = 'default'
					AND ".$PARAM_prefixBDD."post.cat_id = ".$PARAM_prefixBDD."category.cat_id
					AND post_url='".$url[1]."'";
	if (DEBUG) echo "<br />".$query."<br />"; 
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ);
	while( $ligne = $resultats->fetch() ){
		$PARAM_title = $ligne->post_title;
		$PARAM_description = substr(strip_tags($ligne->post_content_xhtml),0,150);
		if ($msgerreur != ""){
			$content .= $msgerreur;
		} else {
			$content .= $PARAM_pubpost;
		}
		$content .= "<article lang=\"fr-FR\">";
		$content .= "	<h1 class=\"post-title\">";
		$content .= 		$ligne->post_title;
		$content .= "	</h1>";
		$content .= "	<div class=\"post-tags\">";
		$content .= "		Le ".date('d/m/Y',strtotime($ligne->post_creadt))." dans ";
		$content .= "		<span class=\"categ\"><a href=\"".$PARAM_domaine.$PARAM_racine.$PARAM_script."/category/".urlencode($ligne->cat_url)."\">[".$ligne->cat_title."]</a></span>";
		$content .= "		<br />Tag(s): ";
		$content .= 		f_lestags($PARAM_domaine.$PARAM_racine.$PARAM_script.'/tag/',$ligne->post_meta);
		$content .= "	</div>";
		$content .= "	<div class=\"description yoxview\">";
		$content .= 		$ligne->post_content_xhtml;
		$content .= "	</div>";
		$content .= "</article>";
		$content .= $PARAM_pubpost;
		$lepostid = $ligne->post_id;
        //$content .= "<hr />";
	}
	// On recup les commentaires aussi
	$query = "SELECT * FROM ".$PARAM_prefixBDD."post,".$PARAM_prefixBDD."comment 
					WHERE 
						".$PARAM_prefixBDD."post.post_id=".$PARAM_prefixBDD."comment.post_id 
						AND post_url='$url[1]' 
						AND comment_status = 1 
						ORDER BY comment_dt ASC";
	if (DEBUG) echo "<br />".$query."<br />";
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ);
	$i = 0;
	$comment .=  "<h2>Commentaire(s) :</h2>";
	$comment .=  '	<div id="comment">';
	while( $ligne = $resultats->fetch() ){
		$i++;
		$comment .=  "<div id=\"c".$ligne->comment_id."\">";
		$comment .=  	$i.". Le ".$ligne->comment_dt." par ".$ligne->comment_author."<br />";
		$comment .=  	$ligne->comment_content;
		$comment .=  "</div>";
        $comment .=  "<hr />";
	}
	$resultats->closeCursor(); // on ferme le curseur des résultats
	
	$comment .= '
	    <form action="'.$PARAM_domaine.$PARAM_racine.$PARAM_script.'/post/'.$url[1].'#pr" method="post" id="comment-form">
			<h2>Ajouter un commentaire</h2>
			<div>
                <label>
                        <span>Nom ou pseudo&nbsp;:</span>
                        <input name="c_name" placeholder="Entrez un nom ou un pseudo" type="text" tabindex="1" required autofocus value="'.$_POST['c_name'].'"/>
                </label>
			</div>
			<div>
                <label>
                        <span>Adresse email&nbsp;:</span>
                        <input name="c_mail" placeholder="Entrez une adresse email" type="email" tabindex="2" required autofocus value="'.$_POST['c_mail'].'"/>
                </label>
			</div>
			<div>
                <label>
                        <span>Votre site&nbsp;:</span>
                        <input name="c_site" placeholder="Entrez l\'url de votre site" type="url" tabindex="3" value="'.$_POST['c_site'].'"/>
                </label>
			</div>
			<div>
                <label>
                        <span>Commentaire&nbsp;:</span>
                        <textarea name="c_content" placeholder="Saisissez votre commentaire" type="textarea" tabindex="4" required />'.$_POST['c_content'].'</textarea>
                </label>
			</div>
			<p class="form-help">Le code HTML est affiché comme du texte et les adresses web sont automatiquement transformées.<br />
			Le champ ci-dessous est pour confirmer que vous êtes humain</p>
			<div>
                <label>
                        <span>Première lettre de l\'alphabet:</span>
                        <input name="c_ctrl" placeholder="Saisissez la première lettre de l\'alphabet" type="text" tabindex="5" required value="'.$_POST['c_ctrl'].'"/>
                </label>
			</div>
			<div>
                <label>
                        <input name="bval" placeholder="Valider votre saisie" type="submit" value="Valider" tabindex="6" />
                </label>
			</div>
			<input type="hidden" name="postid" value="'.$lepostid.'" />
		</form>';
	$comment .=  "	</div>";
}


/* Traitement des pages tag */
if ($url[0] == "tag"){
	// On ecrase $PARAM_title pour avoir un titre de page unique
	$quelpage = "";
	if ($url[2] > 1){
			$quelpage = " (page ".$url[2].")";
	} 
	$PARAM_title = $url[1]." : ".$PARAM_title.$quelpage;
	// Idem pour la description
	$PARAM_description = "L'ensemble des billets en rapport avec : ".$url[1].$quelpage;
	// Trouver le nombre de billet
	$query = "SELECT ".$PARAM_prefixBDD."meta.post_id FROM ".$PARAM_prefixBDD."meta, ".$PARAM_prefixBDD."post 
					WHERE 
					".$PARAM_prefixBDD."meta.meta_id = '".$url[1]."'
					AND ".$PARAM_prefixBDD."meta.post_id = ".$PARAM_prefixBDD."post.post_id
					AND post_status=1
					AND blog_id = 'default'
					AND post_password IS NULL
					AND post_type = 'post'
					ORDER BY post_dt DESC;";
	if (DEBUG) echo "<br />".$query."<br />"; 
	$resultats=$connexion->prepare($query);
	$resultats->execute();
	$countbillet = $resultats->rowCount();
	if (DEBUG) echo "countbillet=".$countbillet;
	// Si il y a plus de 10 resultats il faut une gestion page
	if ($countbillet > 10){
		// Comme je vais m'en servir deux fois je ne refais pas deux fois ->getLinks()
		$afflienpage = getPaginationTag($url[2], $countbillet, 10, 1,$PARAM_domaine.$PARAM_racine, $PARAM_script."/tag/".$url[1]."/page/","tag",$url[1]);
		$content = $afflienpage;
	}
	
	$query = "SELECT * FROM ".$PARAM_prefixBDD."meta, ".$PARAM_prefixBDD."post, ".$PARAM_prefixBDD."category 
					WHERE 
					".$PARAM_prefixBDD."meta.meta_id = '".$url[1]."'
					AND ".$PARAM_prefixBDD."meta.post_id = ".$PARAM_prefixBDD."post.post_id
					AND ".$PARAM_prefixBDD."post.cat_id =  ".$PARAM_prefixBDD."category.cat_id
					AND post_status=1
					AND ".$PARAM_prefixBDD."post.blog_id = 'default'
					AND post_password IS NULL
					AND post_type = 'post'
					ORDER BY post_dt DESC LIMIT ".(($url[2]-1)*10).",10;";
	if (DEBUG) echo "<br />".$query."<br />";
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ);
	$pub = 0;
	while( $ligne = $resultats->fetch() ){
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
			$content .= "		<a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .= "	<div class=\"post-tags\">";
			$content .= "		Le ".date('d/m/Y',strtotime($ligne->post_creadt))." dans ";
			$content .= "		<span class=\"categ\"><a href=\"".$PARAM_domaine.$PARAM_racine.$PARAM_script."/category/".urlencode($ligne->cat_url)."\">[".$ligne->cat_title."]</a></span>";
			$content .= "		<br />Tag(s): ";
			$content .= 		f_lestags($PARAM_domaine.$PARAM_racine.$PARAM_script.'/tag/',$ligne->post_meta);
			$content .= "	</div>";
			$content .= "	<div>";
			$content .= 		$ligne->post_excerpt_xhtml;
			$content .= "		<p class=\"read-it\">";
			$content .= "			<a title=\"Lire la suite ".$ligne->post_title."\" href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">Lire la suite</a>";
			$content .= "		</p>";
			$content .= "	</div>";
			$content .= "</div>";
			$pourh4	.= "<div class=\"col_3\">";
			$pourh4	.= "<h4 class=\"post-title\"><a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a></h4>";
			$pourh4	.= $ligne->post_excerpt_xhtml;;
			$pourh4	.= "</div>";
		} else {
			$content .= "<div class=\"description\">";
			$content .= "	<h1 class=\"post-title\">";
			$content .= "		<a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .= "	<div class=\"post-tags\">";
			$content .= "		Le ".date('d/m/Y',strtotime($ligne->post_creadt))." dans ";
			$content .= "		<span class=\"categ\"><a href=\"".$PARAM_domaine.$PARAM_racine.$PARAM_script."/category/".urlencode($ligne->cat_url)."\">[".$ligne->cat_title."]</a></span>";
			$content .= "		<br />Tag(s): ";
			$content .= 		f_lestags($PARAM_domaine.$PARAM_racine.$PARAM_script.'/tag/',$ligne->post_meta);
			$content .= "	</div>";
			$content .=  	$ligne->post_content_xhtml;
			$content .= "</div>";
		}
	}
	// Et je remet aussi la selection de page en bas
	$content .= $afflienpage;
	$resultats->closeCursor(); // on ferme le curseur des résultats
}

/* Traitement des pages category */
if ($url[0] == "category"){
	// J'interdit l'indexation ayant peu de catégorie, des pages identiques se retrouvent
	// possible entre les pages du blog et les page de categorie.
	$noindex = "<meta name=\"robots\" content=\"noindex\" />";

	// On ecrase $PARAM_title pour avoir un titre de page unique
	$quelpage = "";
	if ($url[2] > 1){
			$quelpage = " (page ".$url[2].")";
	} 
	$PARAM_title = $url[1]." : ".$PARAM_title.$quelpage;
	// Idem pour la description
	$PARAM_description = "L'ensemble des billets placés dans la catégorie : ".$url[1].$quelpage;
	// Trouver le nombre de billet
	$query = "SELECT ".$PARAM_prefixBDD."category.cat_id FROM ".$PARAM_prefixBDD."category, ".$PARAM_prefixBDD."post 
					WHERE 
					".$PARAM_prefixBDD."category.cat_url = '".$url[1]."'
					AND ".$PARAM_prefixBDD."category.cat_id = ".$PARAM_prefixBDD."post.cat_id
					AND post_status=1
					AND ".$PARAM_prefixBDD."post.blog_id = 'default'
					AND post_password IS NULL
					AND post_type = 'post'
					ORDER BY post_dt DESC;";
	if (DEBUG) echo "<br />".$query."<br />"; 
	$resultats=$connexion->prepare($query);
	$resultats->execute();
	$countbillet = $resultats->rowCount();
	if (DEBUG) echo "countbillet=".$countbillet;
	// Si il y a plus de 10 resultats il faut une gestion page
	if ($countbillet > 10){
		// Comme je vais m'en servir deux fois je ne refais pas deux fois ->getLinks()
		$afflienpage = getPaginationTag($url[2], $countbillet, 10, 1,$PARAM_domaine.$PARAM_racine, $PARAM_script."/category/".$url[1]."/page/","category",$url[1]);
		$content = $afflienpage;
	}
	
	$query = "SELECT * FROM ".$PARAM_prefixBDD."category, ".$PARAM_prefixBDD."post 
					WHERE 
					".$PARAM_prefixBDD."category.cat_url = '".urldecode($url[1])."'
					AND ".$PARAM_prefixBDD."category.cat_id = ".$PARAM_prefixBDD."post.cat_id
					AND post_status=1
					AND ".$PARAM_prefixBDD."post.blog_id = 'default'
					AND post_password IS NULL
					AND post_type = 'post'
					ORDER BY post_dt DESC LIMIT ".(($url[2]-1)*10).",10;";
	if (DEBUG) echo "<br />".$query."<br />";
	$resultats=$connexion->query($query);
	$resultats->setFetchMode(PDO::FETCH_OBJ);
	$pub = 0;
	while( $ligne = $resultats->fetch() ){
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
			$content .= "		<a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .= "	<div class=\"post-tags\">";
			$content .= "		Le ".date('d/m/Y',strtotime($ligne->post_creadt))." dans ";
			$content .= "		<span class=\"categ\"><a href=\"".$PARAM_domaine.$PARAM_racine.$PARAM_script."/category/".urlencode($ligne->cat_url)."\">[".$ligne->cat_title."]</a></span>";
			$content .= "		<br />Tag(s): ";
			$content .= 		f_lestags($PARAM_domaine.$PARAM_racine.$PARAM_script.'/tag/',$ligne->post_meta);
			$content .= "	</div>";
			$content .= "	<div>";
			$content .= 		$ligne->post_excerpt_xhtml;
			$content .= "		<p class=\"read-it\">";
			$content .= "			<a title=\"Lire la suite ".$ligne->post_title."\" href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">Lire la suite</a>";
			$content .= "		</p>";
			$content .= "	</div>";
			$content .= "</div>";
			$pourh4	.= "<div class=\"col_3\">";
			$pourh4	.= "<h4 class=\"post-title\"><a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a></h4>";
			$pourh4	.= $ligne->post_excerpt_xhtml;;
			$pourh4	.= "</div>";
		} else {
			$content .= "<div class=\"description\">";
			$content .= "	<h1 class=\"post-title\">";
			$content .= "		<a href=\"".$PARAM_racine.$PARAM_script."/post/".$ligne->post_url."\">".$ligne->post_title."</a>";
			$content .= "	</h1>";
			$content .= "	<div class=\"post-tags\">";
			$content .= "		Le ".date('d/m/Y',strtotime($ligne->post_creadt))." dans ";
			$content .= "		<span class=\"categ\"><a href=\"".$PARAM_domaine.$PARAM_racine.$PARAM_script."/category/".urlencode($ligne->cat_url)."\">[".$ligne->cat_title."]</a></span>";
			$content .= "		<br />Tag(s): ";
			$content .= 		f_lestags($PARAM_domaine.$PARAM_racine.$PARAM_script.'/tag/',$ligne->post_meta);
			$content .= "	</div>";
			$content .=  	$ligne->post_content_xhtml;
			$content .= "</div>";
		}
	}
	// Et je remet aussi la selection de page en bas
	$content .= $afflienpage;
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
	<style type="text/css">
	<?php 
	// Pour gagner des points de vitesse on met le css dans le fichier principal
	include("css/layout_1140.css");
	?>
	html {
		font-family: Ubuntu, Verdana, Tahoma, sans-serif;
		font-weight: 300;
		background-color: #fff;
		color: blue;
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
		
        .examples .row p {
			background: #fff;
            padding:4px 0;
            /* text-align:center; */
        }
        .latest-articles .title {
			color: #333;
			font-size: 15px;
			font-family: 'Open Sans';
			font-weight: 800;
			display: block;
			margin-bottom: 0.6em;
			transition: color 0.3s ease-out 0s;
			
		}
		
		
	.post-excerpt p {
		margin-left: 120px;
	}
	.post-excerpt img {
		height: 100px;
		width: 100px;
		float: left;
		margin-bottom : 1em;
	}
	.description {
		margin: 0px;
		padding: 0px;
		border-top : 1px solid #E8E8E8;
		min-height: 150px;
		background: #fff;
		clear: left;
		margin-bottom: 2em;
	}
	
	ul { list-style: none; }
	ul li { list-style: none; }
	.description ul li {
		margin: 0px;
		margin-left: 1em;
		padding: 0px 0px 0px 11px;
		background: url('<?php echo $PARAM_domaine.$PARAM_racine; ?>img/lili.gif') no-repeat scroll 0px 8px transparent;
	}
	
	.sidebar, #comment, .post-tags {
		font-family: Verdana,Arial,Geneva,Helvetica,sans-serif;
		font-size: 0.8em;
		color: #000099;
	}
	.sidebar a, .post-tags a {
		color: #000099;
		text-decoration: none;
	}
	.sidebar a:visited, .post-tags a:visited {
		color: gray;
	}
	.sidebar a:hover , .post-tags a:hover{
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
	.post-tags {
		margin-top : 1em;
		margin-bottom : 1em;
	}
	#comment {
		font-size: 0.9em;
		color: #000;
	}
	
	
	.read-it a {
		text-decoration: none;
	}
	.read-it a {
		background: none repeat scroll 0% 0% #09F;
		float: left;
		font-family: Georgia,serif;
		font-size: 12px;
		font-style: italic;
		font-weight: bold;
		height: 20px;
		line-height: 20px;
		text-align: center;
		width: 100px;
		z-index: 1200;
		margin-left: 20px;
		color: #FFF;
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
	
	.video-container {
		position: relative;
		padding-bottom: 56.25%;
		padding-top: 30px; height: 0; overflow: hidden;
	}

	.video-container iframe, .video-container object, .video-container embed {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
	}
	
	.valtxt {
		font-weight: 700;
		background-color: #FFFF00;
	}
	
	
	/* Pour le formulaire responsive */
	#comment-form input[type="text"],
	#comment-form input[type="email"],
	#comment-form input[type="tel"],
	#comment-form input[type="url"],
	#comment-form textarea {
		width:95%;
		box-shadow:inset 0 1px 2px #DDD, 0 1px 0 #FFF;
		-webkit-box-shadow:inset 0 1px 2px #DDD, 0 1px 0 #FFF;
		-moz-box-shadow:inset 0 1px 2px #DDD, 0 1px 0 #FFF;
		border:1px solid #CCC;
		background:#FFF;
		margin:0 0 5px;
		padding:10px;
		border-radius:5px;
	}

	#comment-form button[type="submit"] {
		cursor:pointer;
		width:95%;
		border:none;
		background:#991D57;
		background-image:linear-gradient(bottom, #8C1C50 0%, #991D57 52%);
		background-image:-moz-linear-gradient(bottom, #8C1C50 0%, #991D57 52%);
		background-image:-webkit-linear-gradient(bottom, #8C1C50 0%, #991D57 52%);
		color:#FFF;
		margin:0 0 5px;
		padding:10px;
		border-radius:5px;
	}

	::-webkit-input-placeholder {
		color:#888;
	}
	:-moz-placeholder {
		color:#888;
	}
	::-moz-placeholder {
		color:#888;
	}
	:-ms-input-placeholder {
		color:#888;
	}


    </style>	
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title><?php echo $PARAM_title; ?></title>
    <meta name="description" content="<?php echo $PARAM_description; ?>" />
    <meta name="geo.position" content="<?php echo $PARAM_geo_position; ?>" />
	<meta name="geo.placename" content="<?php echo $PARAM_geo_placename; ?>" />
	<meta name="geo.region" content="<?php echo $PARAM_geo_region; ?>FR-fr" />
    <?php echo $noindex; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
    <div class="container12 examples">
        <div class="row">
            <div id="top" class="column12 examples">
                <h1><a href="<?php echo $PARAM_urltot;?>"><?php echo $PARAM_domtitle;?></a></h1>
            </div>
        </div>
        
        <div class="row">
            <div class="column8 examples">
				<?php echo $content; ?>
				<br />
				<?php echo $comment; ?>
            </div>
            <div class="column4">
				<?php
				echo $PARAM_sidebar1;
				echo $last_comment;
				echo $PARAM_sidebar2; 
				?>
			</div>
        </div>
        <footer>
		<div class="row">
            <div class="column12">
                <p>
					<?php 
					$timeend=microtime(true);
					$time=$timeend-$timestart;
					echo "<br />Tps execution : " . $time . " secondes";
					echo $PARAM_stat; 
					?>
				</p>
            </div>
        </div>
        </footer>
    </div>
</body>
</html>
