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

Renseigner l'IP de votre VMC dans Paramètres spécifiques.
Renseigner le CRON pour indiquer la fréquence d'actualisation voulue.

Commandes :
===


Commandes INFO:
---

- 'nom de la piece':temperature : retourne la température de l'air au niveau du capteur de la pièce.
- 'nom de la piece':humidity : retourne l'humidité de l'air au niveau du capteur de la pièce.
- 'nom de la piece':debit : retourne le débit d'air du capteur de la pièce en %.
- 'nom de la piece':COV : retourne le taux de COV pour les capteurs équipés.
- 'nom de la piece':CO2 : retourne le taux de CO2 pour les capteurs équipés.
- 'nom de la piece':boost-timeout : retourne le temps restant du BOOST.
- 'nom de la piece':boost-enable : retourne l'état du BOOST :
   - 0 : Inactif
   - 1 : Actif
- 'nom de la piece':profil : retourne l'état du Profil :
   - 0 : Eco
   - 1 : Health
   - 2 : Intense


Commandes ACTION:
---

la commande BOOST permet d'activer le boost dans une piece selon un temps désiré


- 'nom de la piece':boostOFF  =  laisser vide
- 'nom de la piece':boostON  = {"enable": true, "level": 200, "timeout": 900}; ou : 
   - enable : laisser a true
   - level : niveau de ventilation voulue.
   - timeout : temps voulu du boost en seconde.

- 'nom de la piece':changeProfil  =  0,1 ou 2 
   - 0 : Eco
   - 1 : Health
   - 2 : Intense
