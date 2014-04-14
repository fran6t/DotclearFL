<?php

/* Retourne un tableau a deux dim la premiere
 * si nous sommes à l'accueil
 * 		$urlcompl[0] = "index";
 * 		$urlcompl[1] = "";
 * si  rien trouvé
 * 		$urlcompl[0] = "404";
 * 		$urlcompl[1] = "";
 * si c'est une page
 * 		$urlcompl[0] = "page";
 * 		$urlcompl[1] = "";
 * si c'est un billet
 * 		$urlcompl[0] = "post";
 * 		$urlcompl[1] = "le reste de l'url";
 * */

function feclateURL($monurl,$PARAM_racine){
	$urlcompl[0] = "404";
	$urlcompl[1] = "";
	// On retire l'eventuel racine du site
	$monurl = str_replace($PARAM_racine,"",$monurl);
	if ($monurl == ""){
		$urlcompl[0] = "index";
		$urlcompl[1] = 1;
		return $urlcompl;
	}
	//echo "monurl=".$monurl."<br />";
	$urllocale = explode("/",$monurl); 
	//echo "urllocale[0]=".$urllocale[0];
	// le premier element doit etre index.php
	if ($urllocale[0] != "index.php"){
		$urlcompl[0] = "404";
		$urlcompl[1] = "";
		return $urlcompl;
	}
	if ($urllocale[1] == "feed"){
		$urlcompl[0] = "feed";
		$urlcompl[1] = "feed/rss2";
		return $urlcompl;
	}
	if ($urllocale[1] == "sitemap.xml"){
		$urlcompl[0] = "sitemap";
		$urlcompl[1] = "sitemap.xml";
		return $urlcompl;
	}
	if ($urllocale[1] == "post"){
		if ($urllocale[2] == ""){
			$urlcompl[0] = "404";
			$urlcompl[1] = "";
			return $urlcompl;
		} else {
			// Nous sommes en presence d'une url qui peut posseder elle meme des / il faut donc reconstituer
			$urltmp = "";
			// Il faut pas le premier /
			$sep = "";
			for ($i = 2; $i < count($urllocale); $i++) {
				$urltmp .= $sep.$urllocale[$i];
				$sep = "/"; 
			}
			$urlcompl[0] = "post";
			$urlcompl[1] = $urltmp;
			return $urlcompl;
		}
	}
	if ($urllocale[1] == "page"){
		// ce cas ne dois pas se presenter
		if ($urllocale[2] == ""){
			$urlcompl[0] = "page";
			$urlcompl[1] = 1;
			return $urlcompl;
		} else {
			$urlcompl[0] = "page";
			$urlcompl[1] = $urllocale[2];
			return $urlcompl;
		}
	}
	if ($urllocale[1] == "pages"){
		if ($urllocale[2] == ""){
			$urlcompl[0] = "404";
			$urlcompl[1] = "";
			return $urlcompl;
		} else {
			// Nous sommes en presence d'une url qui peut posseder elle meme des / il faut donc reconstituer
			$urltmp = "";
			// Il faut pas le premier /
			$sep = "";
			for ($i = 2; $i < count($urllocale); $i++) {
				$urltmp .= $sep.$urllocale[$i];
				$sep = "/"; 
			}
			$urlcompl[0] = "pages";
			$urlcompl[1] = $urltmp;
			return $urlcompl;
		}
	}
	if ($urllocale[1] == "tag"){
		if ($urllocale[2] == ""){
			$urlcompl[0] = "404";
			$urlcompl[1] = "";
			return $urlcompl;
		} else {
			// une url tag peut avoir deux formes soit
			// tag/letag ou tag/letag/page/X
			// Il faut donc isoler letag et si besoin le n° de page
			$urlcompl[0] = "tag";
			$urlcompl[1] = urldecode($urllocale[2]);
			if ($urllocale[3] == "page"){
				$urlcompl[2] = $urllocale[4];  
			} else {
				$urlcompl[2] = 1;
			}
			return $urlcompl;
		}
	}
	if ($urllocale[1] == "category"){
		if ($urllocale[2] == ""){
			$urlcompl[0] = "404";
			$urlcompl[1] = "";
			return $urlcompl;
		} else {
			$urlcompl[0] = "category";
			$urlcompl[1] = urldecode($urllocale[2]);
			if ($urllocale[3] == "page"){
				$urlcompl[2] = $urllocale[4];  
			} else {
				$urlcompl[2] = 1;
			}
			return $urlcompl;
		}
	}
	return $urlcompl;  
}

// Utilisé par getPaginationString pour eviter le duplicate content car /index.php/page/1 ne doit 
// pas exister puisqu'il s'agit de la page d'accueil du blog.

function antiduplicate($targetpage,$pagestring,$counter){
	if ($counter == 1){
		return "<a href=\"" . $targetpage . "\">$counter</a>";
	} else {
		return "<a href=\"" . $targetpage . $pagestring . $counter . "\">$counter</a>";
	}
}

//function to return the pagination string
function getPaginationString($page = 1, $totalitems, $limit = 15, $adjacents = 1, $targetpage = "/", $pagestring = "?page=")
{		
	//defaults
	$nbreaffiche = 5;
	if(!$adjacents) $adjacents = 1;
	if(!$limit) $limit = 15;
	if(!$page) $page = 1;
	if(!$targetpage) $targetpage = "/";
	
	//other vars
	$prev = $page - 1;									//previous page is page - 1
	$next = $page + 1;									//next page is page + 1
	$lastpage = ceil($totalitems / $limit);				//lastpage is = total items / items per page, rounded up.
	$lpm1 = $lastpage - 1;								//last page minus 1
	
	/* 
		Now we apply our rules and draw the pagination object. 
		We're actually saving the code to a variable in case we want to draw it more than once.
	*/
	$pagination = "";
	if($lastpage > 1)
	{	
		$pagination .= "<div class=\"pagination\"";
		if($margin || $padding)
		{
			$pagination .= " style=\"";
			if($margin)
				$pagination .= "margin: $margin;";
			if($padding)
				$pagination .= "padding: $padding;";
			$pagination .= "\"";
		}
		$pagination .= ">";

		//previous button
		if ($page > 1) 
			if ($prev == 1){
				$pagination .= "<a href=\"$targetpage\">««</a>";
			} else {
				$pagination .= "<a href=\"$targetpage$pagestring$prev\">««</a>";
			}
		else
			$pagination .= "<span class=\"disabled\">««</span>";	
		
		//pages	
		if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
		{	
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page)
					$pagination .= "<span class=\"current\">$counter</span>";
				else
					$pagination .= antiduplicate($targetpage,$pagestring,$counter);					
			}
		}
		elseif($lastpage >= 7 + ($adjacents * 2))	//enough pages to hide some
		{
			//close to beginning; only hide later pages
			if($page < 1 + ($adjacents * 3))		
			{
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
				{
					if ($counter == $page)
						$pagination .= "<span class=\"current\">$counter</span>";
					else
						$pagination .= antiduplicate($targetpage,$pagestring,$counter);				
				}
				$pagination .= "<span class=\"elipses\">...</span>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . $lpm1 . "\">$lpm1</a>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . $lastpage . "\">$lastpage</a>";		
			}
			//in middle; hide some front and some back
			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
			{
				$pagination .= "<a href=\"" . $targetpage . $pagestring . "1\">1</a>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . "2\">2</a>";
				$pagination .= "<span class=\"elipses\">...</span>";
				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
				{
					if ($counter == $page)
						$pagination .= "<span class=\"current\">$counter</span>";
					else
						$pagination .= antiduplicate($targetpage,$pagestring,$counter);				
				}
				$pagination .= "...";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . $lpm1 . "\">$lpm1</a>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . $lastpage . "\">$lastpage</a>";		
			}
			//close to end; only hide early pages
			else
			{
				$pagination .= "<a href=\"" . $targetpage . $pagestring . "1\">1</a>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . "2\">2</a>";
				$pagination .= "<span class=\"elipses\">...</span>";
				for ($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination .= "<span class=\"current\">$counter</span>";
					else
						$pagination .= antiduplicate($targetpage,$pagestring,$counter);				
				}
			}
		}
		
		//next button
		if ($page < $counter - 1) 
			$pagination .= "<a href=\"" . $targetpage . $pagestring . $next . "\">»»</a>";
		else
			$pagination .= "<span class=\"disabled\">»»</span>";
		$pagination .= "</div>\n";
	}
	
	return $pagination;

}


function antiduplicatetag($targetpage,$pagestring,$counter,$queltype,$queltag){
	if ($counter == 1){
		return "<a href=\"" . $targetpage . "index.php/".$queltype."/".$queltag . "\">$counter</a>";
	} else {
		return "<a href=\"" . $targetpage . $pagestring . $counter . "\">$counter</a>";
	}
}

//function to return the pagination string
function getPaginationTag($page = 1, $totalitems, $limit = 15, $adjacents = 1, $targetpage = "/", $pagestring = "?page=", $queltype, $queltag)
{		
	//defaults
	if(!$adjacents) $adjacents = 1;
	if(!$limit) $limit = 15;
	if(!$page) $page = 1;
	if(!$targetpage) $targetpage = "/";
	
	//other vars
	$prev = $page - 1;									//previous page is page - 1
	$next = $page + 1;									//next page is page + 1
	$lastpage = ceil($totalitems / $limit);				//lastpage is = total items / items per page, rounded up.
	$lpm1 = $lastpage - 1;								//last page minus 1
	
	/* 
		Now we apply our rules and draw the pagination object. 
		We're actually saving the code to a variable in case we want to draw it more than once.
	*/
	$pagination = "";
	if($lastpage > 1)
	{	
		$pagination .= "<div class=\"pagination\"";
		if($margin || $padding)
		{
			$pagination .= " style=\"";
			if($margin)
				$pagination .= "margin: $margin;";
			if($padding)
				$pagination .= "padding: $padding;";
			$pagination .= "\"";
		}
		$pagination .= ">";

		//previous button
		if ($page > 1) 
			if ($prev == 1){
				$pagination .= "<a href=\"$targetpage". "index.php/".$queltype."/".$queltag ."\">« prev</a>";
			} else {
				$pagination .= "<a href=\"$targetpage$pagestring$prev\">« prev</a>";
			}
		else
			$pagination .= "<span class=\"disabled\">« prev</span>";	
		
		//pages	
		if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
		{	
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page)
					$pagination .= "<span class=\"current\">$counter</span>";
				else
					$pagination .= antiduplicatetag($targetpage,$pagestring,$counter,$queltype,$queltag);					
			}
		}
		elseif($lastpage >= 7 + ($adjacents * 2))	//enough pages to hide some
		{
			//close to beginning; only hide later pages
			if($page < 1 + ($adjacents * 3))		
			{
				for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
				{
					if ($counter == $page)
						$pagination .= "<span class=\"current\">$counter</span>";
					else
						$pagination .= antiduplicatetag($targetpage,$pagestring,$counter,$queltype,$queltag);				
				}
				$pagination .= "<span class=\"elipses\">...</span>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . $lpm1 . "\">$lpm1</a>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . $lastpage . "\">$lastpage</a>";		
			}
			//in middle; hide some front and some back
			elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
			{
				$pagination .= "<a href=\"" . $targetpage . $pagestring . "1\">1</a>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . "2\">2</a>";
				$pagination .= "<span class=\"elipses\">...</span>";
				for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
				{
					if ($counter == $page)
						$pagination .= "<span class=\"current\">$counter</span>";
					else
						$pagination .= antiduplicatetag($targetpage,$pagestring,$counter,$queltype,$queltag);				
				}
				$pagination .= "...";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . $lpm1 . "\">$lpm1</a>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . $lastpage . "\">$lastpage</a>";		
			}
			//close to end; only hide early pages
			else
			{
				$pagination .= "<a href=\"" . $targetpage . $pagestring . "1\">1</a>";
				$pagination .= "<a href=\"" . $targetpage . $pagestring . "2\">2</a>";
				$pagination .= "<span class=\"elipses\">...</span>";
				for ($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
						$pagination .= "<span class=\"current\">$counter</span>";
					else
						$pagination .= antiduplicatetag($targetpage,$pagestring,$counter,$queltype,$queltag);				
				}
			}
		}
		
		//next button
		if ($page < $counter - 1) 
			$pagination .= "<a href=\"" . $targetpage . $pagestring . $next . "\">next »</a>";
		else
			$pagination .= "<span class=\"disabled\">next »</span>";
		$pagination .= "</div>\n";
	}
	
	return $pagination;

}
/* Faire le lien vers les tags avec le champs post_meta qui est un champs serializé
 * 
 * */
function f_lestags($prefixurl,$datapost_meta){
		if (unserialize($datapost_meta)){
			$tabtags = unserialize($datapost_meta);
			$lestags = "";
			foreach($tabtags['tag'] as $element){
				$lestags.=' <a href="'.$prefixurl.urlencode($element).'">['.$element.']</a>';
			}
		}
		return $lestags;
}

?>
