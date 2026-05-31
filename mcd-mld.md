1. Modèle Conceptuel de Données (MCD)
L'MCD kaywade7 chno homa l'entités (les objets dyal le système) w l'3ala9at li binathom bla mandkhlo f les détails techniques dyal la base de données.

A. Les Entités (Les carrés) :

UTILISATEUR

id_utilisateur (Identifiant)

nom_complet

email

telephone

mot_de_passe

role (Admin, Client, Livreur)

latitude (Optionnel)

longitude (Optionnel)

COLIS

id_colis (Identifiant)

code_suivi

nom_destinataire

tel_destinataire

adresse_destinataire

ville_destinataire

prix_colis

frais_livraison

statut_actuel

date_creation

HISTORIQUE_STATUT

id_historique (Identifiant)

statut

localisation_gps

date_action

AVIS

id_avis (Identifiant)

note

commentaire

date_avis

B. Les Associations et Cardinalités (Les ovales et les lignes) :

ENREGISTRER : Bin UTILISATEUR (Client) w COLIS

Un Utilisateur (Client) peut enregistrer de 0 à N Colis. (0,n)

Un Colis est enregistré par 1 et 1 seul Utilisateur (Client). (1,1)

AFFECTER : Bin UTILISATEUR (Livreur) w COLIS

Un Utilisateur (Livreur) peut se voir affecter de 0 à N Colis. (0,n)

Un Colis est affecté à 0 ou 1 Utilisateur (Livreur). (0,1)

POSSEDER_HISTORIQUE : Bin COLIS w HISTORIQUE_STATUT

Un Colis peut posséder de 1 à N Historiques. (1,n)

Un Historique appartient à 1 et 1 seul Colis. (1,1)

EFFECTUER_ACTION : Bin UTILISATEUR w HISTORIQUE_STATUT

Un Utilisateur peut effectuer de 0 à N actions (Historiques). (0,n)

Un Historique est effectué par 0 ou 1 Utilisateur. (0,1) (0 hit momkin system li ydir update)

EVALUER_COLIS : Bin COLIS w AVIS

Un Colis peut recevoir 0 ou 1 Avis. (0,1)

Un Avis concerne 1 et 1 seul Colis. (1,1)

CONCERNER_LIVREUR : Bin UTILISATEUR (Livreur) w AVIS

Un Utilisateur (Livreur) peut être concerné par de 0 à N Avis. (0,n)

Un Avis concerne 1 et 1 seul Utilisateur (Livreur). (1,1)

2. Modèle Logique de Données (MLD)
L'MLD howa la traduction dyal l'MCD l des tables relationnelles. Hada howa li ghadi tkteb f le rapport dyalek b had la nomenclature standard (Les Clés Primaires mster 3lihom, w les Clés Étrangères fihom #):

UTILISATEUR (<u>id_utilisateur</u>, nom_complet, email, telephone, mot_de_passe, role, latitude, longitude)

COLIS (<u>id_colis</u>, code_suivi, nom_destinataire, tel_destinataire, adresse_destinataire, ville_destinataire, prix_colis, frais_livraison, statut_actuel, date_creation, #id_client, #id_livreur)

#id_client est une clé étrangère faisant référence à id_utilisateur (le client).

#id_livreur est une clé étrangère faisant référence à id_utilisateur (le livreur, peut être NULL).

HISTORIQUE_STATUT (<u>id_historique</u>, statut, localisation_gps, date_action, #id_colis, #id_utilisateur)

#id_colis est une clé étrangère faisant référence à id_colis.

#id_utilisateur est une clé étrangère faisant référence à id_utilisateur (celui qui a modifié le statut).

AVIS (<u>id_avis</u>, note, commentaire, date_avis, #id_colis, #id_livreur)

#id_colis est une clé étrangère faisant référence à id_colis (Unique).

#id_livreur est une clé étrangère faisant référence à id_utilisateur (le livreur).