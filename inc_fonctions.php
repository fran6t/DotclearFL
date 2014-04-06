<?php

/* retourne index si nous sommes à l'accueil
 * retourne 404 si rien trouvé
 * retourne URL du billet si post
 * */
function feclateURL($monurl,$PARAM_racine){
	// On retire l'eventuel racine du site
	$monurl = str_replace($PARAM_racine,"",$monurl);
	if ($monurl == ""){
		return "index";
	}
	//echo "monurl=".$monurl."<br />";
	$urllocale = explode("/",$monurl); 
	//echo "urllocale[0]=".$urllocale[0];
	// le premier element doit etre index.php
	if ($urllocale[0] != "index.php"){
		return "404";
	}
	if ($urllocale[1] == "post"){
		if ($urllocale[2] == ""){
			return "404";
		} else {
			return $urllocale[2];
		}
	}
	return "index";  
}

/* *
 * Ci-dessous les fonctions ou class pour extraire une chaine de caractere en tenant 
 * compte des balises html
 * http://www.pjgalbraith.com/2011/11/truncating-text-html-with-php/
 * 
 * Exemple d'utilisation
 * $html = '<p>This is <strong>test</strong> html text.</p>';
 * $output = TruncateHTML::truncateChars($html, '11', '...');
 * echo $output;
 * $output = TruncateHTML::truncateWords($html, '3', '...');
 * echo $output;
* */


class TruncateHTML {
    
    public static function truncateChars($html, $limit, $ellipsis = '...') {
        
        if($limit <= 0 || $limit >= strlen(strip_tags($html)))
            return $html;
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        
        $body = $dom->getElementsByTagName("body")->item(0);
        
        $it = new DOMLettersIterator($body);
        
        foreach($it as $letter) {
            if($it->key() >= $limit) {
                $currentText = $it->currentTextPosition();
                $currentText[0]->nodeValue = substr($currentText[0]->nodeValue, 0, $currentText[1] + 1);
                self::removeProceedingNodes($currentText[0], $body);
                self::insertEllipsis($currentText[0], $ellipsis);
                break;
            }
        }
        return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $dom->saveHTML());
    }
    
    public static function truncateWords($html, $limit, $ellipsis = '...') {
        
        if($limit <= 0 || $limit >= self::countWords(strip_tags($html)))
            return $html;
        
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        
        $body = $dom->getElementsByTagName("body")->item(0);
        
        $it = new DOMWordsIterator($body);
        
        foreach($it as $word) {            
            if($it->key() >= $limit) {
                $currentWordPosition = $it->currentWordPosition();
                $curNode = $currentWordPosition[0];
                $offset = $currentWordPosition[1];
                $words = $currentWordPosition[2];
                
                $curNode->nodeValue = substr($curNode->nodeValue, 0, $words[$offset][1] + strlen($words[$offset][0]));
                
                self::removeProceedingNodes($curNode, $body);
                self::insertEllipsis($curNode, $ellipsis);
                break;
            }
        }
        
        return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $dom->saveHTML());
    }
    
    private static function removeProceedingNodes(DOMNode $domNode, DOMNode $topNode) {        
        $nextNode = $domNode->nextSibling;
        
        if($nextNode !== NULL) {
            self::removeProceedingNodes($nextNode, $topNode);
            $domNode->parentNode->removeChild($nextNode);
        } else {
            //scan upwards till we find a sibling
            $curNode = $domNode->parentNode;
            while($curNode !== $topNode) {
                if($curNode->nextSibling !== NULL) {
                    $curNode = $curNode->nextSibling;
                    self::removeProceedingNodes($curNode, $topNode);
                    $curNode->parentNode->removeChild($curNode);
                    break;
                }
                $curNode = $curNode->parentNode;
            }
        }
    }
    
    private static function insertEllipsis(DOMNode $domNode, $ellipsis) {    
        $avoid = array('a', 'strong', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'a', 'img', 'iframe'); //html tags to avoid appending the ellipsis to
        
        if( in_array($domNode->parentNode->nodeName, $avoid) && $domNode->parentNode->parentNode !== NULL) {
            // Append as text node to parent instead
            $textNode = new DOMText($ellipsis);
            
            if($domNode->parentNode->parentNode->nextSibling)
                $domNode->parentNode->parentNode->insertBefore($textNode, $domNode->parentNode->parentNode->nextSibling);
            else
                $domNode->parentNode->parentNode->appendChild($textNode);
        } else {
            // Append to current node
            $domNode->nodeValue = rtrim($domNode->nodeValue).$ellipsis;
        }
    }
    
    private static function countWords($text) {
        $words = preg_split("/[\n\r\t ]+/", $text, -1, PREG_SPLIT_NO_EMPTY);
        return count($words);
    }
    
}
/**
 * Iterates individual characters (Unicode codepoints) of DOM text and CDATA nodes
 * while keeping track of their position in the document.
 *
 * Example:
 *
 *  $doc = new DOMDocument();
 *  $doc->load('example.xml');
 *  foreach(new DOMLettersIterator($doc) as $letter) echo $letter;
 *
 * NB: If you only need characters without their position
 *     in the document, use DOMNode->textContent instead.
 *
 * @author porneL http://pornel.net
 * @license Public Domain
 *
 */
final class DOMLettersIterator implements Iterator
{
    private $start, $current;
    private $offset, $key, $letters;

    /**
     * expects DOMElement or DOMDocument (see DOMDocument::load and DOMDocument::loadHTML)
     */
    function __construct(DOMNode $el)
    {
        if ($el instanceof DOMDocument) $this->start = $el->documentElement;
        else if ($el instanceof DOMElement) $this->start = $el;
        else throw new InvalidArgumentException("Invalid arguments, expected DOMElement or DOMDocument");
    }

    /**
     * Returns position in text as DOMText node and character offset.
     * (it's NOT a byte offset, you must use mb_substr() or similar to use this offset properly).
     * node may be NULL if iterator has finished.
     *
     * @return array
     */
    function currentTextPosition()
    {
        return array($this->current, $this->offset);
    }

    /**
     * Returns DOMElement that is currently being iterated or NULL if iterator has finished.
     *
     * @return DOMElement
     */
    function currentElement()
    {
        return $this->current ? $this->current->parentNode : NULL;
    }

    // Implementation of Iterator interface
    function key()
    {
        return $this->key;
    }

    function next()
    {
        if (!$this->current) return;

        if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE)
        {
            if ($this->offset == -1)
            {
                // fastest way to get individual Unicode chars and does not require mb_* functions
                preg_match_all('/./us',$this->current->textContent,$m); $this->letters = $m[0];
            }
            $this->offset++; $this->key++;
            if ($this->offset < count($this->letters)) return;
            $this->offset = -1;
        }

        while($this->current->nodeType == XML_ELEMENT_NODE && $this->current->firstChild)
        {
            $this->current = $this->current->firstChild;
            if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE) return $this->next();
        }

        while(!$this->current->nextSibling && $this->current->parentNode)
        {
            $this->current = $this->current->parentNode;
            if ($this->current === $this->start) {$this->current = NULL; return;}
        }

        $this->current = $this->current->nextSibling;

        return $this->next();
    }

    function current()
    {
        if ($this->current) return $this->letters[$this->offset];
        return NULL;
    }

    function valid()
    {
        return !!$this->current;
    }

    function rewind()
    {
        $this->offset = -1; $this->letters = array();
        $this->current = $this->start;
        $this->next();
    }
}


/* 
 * On enleve les tags qui peuvent ête génant dans pour l'affichage dans les bloc paire et impaire
 * 
 * */
	 
function stripTAG($text){
	//$text = truncateHtml($text, $length = 200, $ending = '...', $exact = false, $considerHtml = true);
	$doc = new DOMDocument();
	@$doc->loadHTML($text);
		
	// ce qui suit fonctionne pas pour retirer tous les liens
	// surement un probleme d'index dans l'itérations
	/*
	$nodes=$doc->getElementsByTagName('img');
	foreach($nodes as $node){
		// suprimer le noeud (formule tordu, mais dom)
		$node->parentNode->removeChild($node);
	}
	*/
	
	// Voici ce qui fonctionne
	// On commence par les img
	$domNodeList = $doc->getElementsByTagname('img');
	$domElemsToRemove = array();
	foreach ( $domNodeList as $domElement ) {
		// ...do stuff with $domElement...
		$domElemsToRemove[] = $domElement;
	}
	
	// les url
	$domNodeList = $doc->getElementsByTagname('a');
	foreach ( $domNodeList as $domElement ) {
		// ...do stuff with $domElement...
		$domElemsToRemove[] = $domElement;
	}
	
	// les iframes (cas ou un billet contient uniquement une video youtube par exemple
	$domNodeList = $doc->getElementsByTagname('iframe');
	foreach ( $domNodeList as $domElement ) {
		// ...do stuff with $domElement...
		$domElemsToRemove[] = $domElement;
	}
	
	// les br
	$domNodeList = $doc->getElementsByTagname('br');
	foreach ( $domNodeList as $domElement ) {
		// ...do stuff with $domElement...
		$domElemsToRemove[] = $domElement;
	}
	
	// Suprimer les paragraphes vides
	$domNodeList = $doc->getElementsByTagname('p');
	foreach ( $domNodeList as $domElement ) {
		// si y a rien entre le paragraphe on vire
		if (strlen(trim($domElement->nodeValue)) == 0){
			$domElemsToRemove[] = $domElement;
		}
	}
	
	// Maintenant on fait réellment le job
	foreach( $domElemsToRemove as $domElement ){
		$domElement->parentNode->removeChild($domElement);
	} 
	$body = $doc->getElementsByTagName('body')->item(0);
	// On retourne que ce qu'il y a entre body car sinon c'est structure avec la descri du dom
	return innerHTML($body);
	//return htmlspecialchars(innerHTML($body));
	//return $doc->saveHTML();
	
	
}





/**
 * Permet à l'issue de l'utilisation de dom de retourner la chaine de caractere sinon 
 * c'est une vraie structure complète qui est retournée
 * 
 * */
function innerHTML($el) {
	$doc = new DOMDocument();
	$doc->appendChild($doc->importNode($el, TRUE));
	$html = trim($doc->saveHTML());
	$tag = $el->nodeName;
	return preg_replace('@^<' . $tag . '[^>]*>|</' . $tag . '>$@', '', $html);
}


?>
