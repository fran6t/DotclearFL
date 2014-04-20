DotclearFL
==========

DotclearFL est une demarche personnelle et se veut un essai d'affichage de blog basé sur le moteur DOTCLEAR 
en se passant dans l'immédiat de fonctionalité majeur tel que le ping, ce qui me pousse à réaliser 
cela, c'est la possibilité en rendant ultra-light de basculer mon ou mes blogs sur 
un auto-hebergement, ce sont aussi les multiples ralentissement au moment de la réalisation de 
ce README chez mon hebergeur sans avoir creuser plus que cela il me semble que c'est du au nouvel environnement
et à la multitude d'include. 

Dans l'immédiat le back-office est toujours réalisé avec DOTCLEAR et je pense que ça le restera d'autant
que l'admin continue a s'étoffer et a être convenable chez mon hebergeur. 

Sauf erreur de ma part cette version est fonctionnelle et reprend les principales url d'un blog Dotclear sauf
les pages archives, tags.

L'affichage est reponsive et obtiens des scores vert chez google page speed tant sur les ordis que les mobiles

L'idée est de se servir de la BDD DOTCLEAR pour :

* Affichage en mode responsive max 1140 pixels 
* Afficher la page d'accueil
* Afficher la page billet avec ses commentaires
* Afficher les pages statiques
* Afficher les pages tag
* Afficher les pages catégories
* Autoriser le postage de commentaires
* Mettre un maximum de chose en cache pour limiter au maximum les acces BDD
* Sitemap
* Flux rss

La liste de TODO est longue :

* compléter la feuille de style pour un affichage correct
* optimiser le code par exemple en fusionnant des functions faisant quasiment la meme chose (tag et catégorie)
* faire le système de cache (reste à eclater en repertoire)
* implémenter la partie javascript pour navigation horizontale (Billet precedent ou suivant)
* ne pas faire d'include c a d fusionner tout dans index.php pour la version finale stable
* s'assurer de la compatibilité avec DC 1.2.6
* prendre en compte certain plugin (lesquels ?)
* améliorer la gestion PDO (quand les requetes remontent rien)
* voir possibilité de faire page archive
* creuser le prefetch voir http://www.eboyer.com/dev/869-prefetching-html5/
* optimiser les requetes (jointure et limiter strictement au champs qui servent à la place de *)
* trouver une popup qui fonctionne très bien sur mobile comme sur l'ordi pour l'instant j'ai 
testé sans succés (fluidbox, swipbox, yoxview, html5lightbox et Responsive-Lightbox)


Le site officiel de DOTCLEAR
===
Dotclear http://http://fr.dotclear.org

Utilisation de la structure responsive de Joshua Gatcke
===
www.99lime.com http://www.99lime.com
