DotclearFL
==========

DotclearFL est une demarche personnelle et se veut un essai d'affichage de blog basé sur le moteur DOTCLEAR 
en se passant dans l'immédiat de fonctionalité majeur tel que le ping, les TAGS, ce qui me pousse à réaliser 
cela, c'est la possibilité en rendant cela ultra-light de basculer mon ou mes blogs sur 
un auto-hebergement, ce sont aussi les mutilples ralentissement que je subis au moment de la réalisation de 
ce README chez mon hebergeur. 

Dans l'immédiat le back-office est toujours réalisé avec DOTCLEAR et je pense que ça le restera d'autant
que l'admin continue a s'étoffer. 

L'idée est de se servir de la BDD DOTCLEAR pour :

* Affichage en mode responsive max 1140 pixels 
* Afficher la page d'accueil
* Afficher la page billet avec ses commentaires
* Afficher les pages statiques
* Autoriser le postage de commentaires
* Mettre un maximum de chose en cache pour limiter au maximum les acces BDD
* Sitemap
* Flux rss

La liste de TODO est longue :

* compléter la feuille de style pour un affichage correct
* optimiser ou finir le decorticage d'URL (90%)
* faire le système de cache (reste à eclater en repertoir)
* implémenter la partie javascript pour navigation horizontale (Billet precedent ou suivant)
* faire le flux RSS
* ne pas faire d'include c a d fusionner tout dans index.php pour la version finale stable
* s'assurer de la compatibilité avec DC 1.2.6
* prendre en compte certain plugin (lesquels ?)
* améliorer la gestion PDO (quand les requetes remontent rien)


Le site officiel de DOTCLEAR
===
Dotclear http://http://fr.dotclear.org

Utilisation de la structure responsive de Joshua Gatcke
===
www.99lime.com http://www.99lime.com

