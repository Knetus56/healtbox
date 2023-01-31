Description :
===

Ce plugin Jeedom permet de controler les VMC RENSON HEALTBOX 3


Prérequis :
===
Pour pouvoir récupérer les différentes valeurs de votre VMC, il vous faut obtenir une clé.
Pour cela envoyer un mail à service@renson.been en spécifiant bien le numéro de série de l'appareil.
Celui-ci se trouve dans l'application dédié à la vmc.


Configuration :
===
> Après téléchargement du plugin, il faut l’activer.

Rentrer l'IP de votre VMC dans Paramètres spécifiques


Commandes :
===

la commande BOOST permet d'activer le boost dans une piece selon un temps désiré

- 'nom de la piece':boostOFF  =  laisser vide
> Après téléchargement du plugin, il faut l’activer.
- 'nom de la piece':boostON  = '{"enable": true, "level": 200, "timeout": 900}' ou : 
   - enable : laisser a true
   - level : niveau de ventilation voulue.
   - timeout : temps voulu du boost en seconde.
