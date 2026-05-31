CAHIER DES CHARGES : APPLICATION WEB DE GESTION ET DE SUIVI DES LIVRAISONS
Réalisé par : Simohamed
Cadre du projet : Projet de Fin d'Études (PFE)
Zone cible / Contexte : Agence logistique intermédiaire (Région d'Errachidia / National)

1. Contexte Général du Projet
Avec la croissance rapide du commerce électronique, la gestion logistique du dernier kilomètre est devenue un défi majeur. Les entreprises et les e-commerçants ont besoin de partenaires de livraison fiables pour acheminer leurs colis vers les clients finaux. Actuellement, beaucoup d'agences de livraison locales gèrent leurs opérations de manière manuelle ou utilisent des outils dispersés, ce qui entraîne des retards, des pertes d'informations et une mauvaise expérience pour le client final.

C'est dans ce contexte que s'inscrit notre Projet de Fin d'Études, visant à digitaliser et automatiser le processus d'intermédiation logistique.

2. Problématique
Les problèmes majeurs identifiés dans le processus de livraison actuel sont :

Manque de visibilité : Les e-commerçants n'ont pas de suivi en temps réel de leurs expéditions.

Gestion manuelle : L'affectation des colis aux livreurs se fait souvent par téléphone ou sur papier.

Insatisfaction client : Le destinataire final ne sait pas quand son colis arrivera et n'a pas de canal pour évaluer le service.

Suivi financier complexe : La gestion du "Cash on Delivery" (Paiement à la livraison) entre le livreur, l'agence et l'e-commerçant est source d'erreurs.

3. Objectifs du Projet
L'objectif principal de ce projet est de concevoir et de développer une Application Web centralisée permettant de lier trois acteurs principaux (l'agence, l'e-commerçant et le livreur) tout en offrant une interface de suivi transparente pour le client final.

Les objectifs spécifiques :

Fournir un tableau de bord aux e-commerçants pour enregistrer et suivre leurs colis.

Offrir à l'administrateur (l'agence) un centre de contrôle pour l'affectation et le suivi en temps réel.

Doter les livreurs d'une interface mobile-friendly pour mettre à jour les statuts.

Mettre en place un système de tracking et de feedback par lien unique pour le destinataire, sans obligation de création de compte.

4. Spécifications Fonctionnelles
L'application est divisée en plusieurs modules en fonction des droits d'accès (Rôles) :

4.1. Espace Administrateur (L'Agence de Livraison)
Authentification et gestion des comptes : Créer, modifier et supprimer les comptes des clients (e-commerçants) et des livreurs.

Gestion des colis : Valider les colis enregistrés, organiser le ramassage et affecter les colis aux livreurs disponibles.

Tracking en temps réel : Visualiser la position des livreurs sur une carte interactive (Google Maps/Leaflet) lors de leurs tournées.

Gestion financière : Suivre les encaissements des livreurs et les versements dus aux clients.

Tableau de bord : Consulter les statistiques globales (taux de livraison, avis clients, etc.).

4.2. Espace Client (L'E-commerçant / Expéditeur)
Authentification : Accès sécurisé à l'espace client.

Enregistrement de colis : Formulaire pour ajouter un nouveau colis (Informations du destinataire, prix du colis, description).

Suivi de l'état : Tableau récapitulatif montrant l'évolution du statut des colis (Enregistré, Ramassé, En cours, Livré, Retourné).

Bilan financier : Visualisation du total des fonds récupérés par l'agence sur les livraisons réussies.

4.3. Espace Livreur
Liste des missions : Accès direct aux colis qui lui sont affectés.

Mise à jour du statut : Possibilité de modifier l'état du colis (ex: de "En attente" à "En cours de livraison" puis à "Livré").

Géolocalisation : Autoriser l'application à transmettre sa position GPS actuelle au serveur lorsqu'une tournée est en cours.

4.4. Espace Destinataire (Client Final)
Page de Tracking publique : Accès via un lien sécurisé et unique généré par le système (ex: tracking/CODE_UNIQUE).

Historique : Visualisation de la timeline du colis depuis le ramassage jusqu'à la réception.

Système d'évaluation : Dès que le colis est marqué comme "Livré", un formulaire apparaît permettant au destinataire de noter la qualité de la livraison (1 à 5 étoiles) et de laisser un commentaire.

5. Spécifications Non-Fonctionnelles
Ergonomie et UX/UI : L'interface doit être intuitive, moderne et responsive. L'interface du livreur doit être particulièrement optimisée pour une utilisation sur smartphone (Mobile-first).

Sécurité : Les mots de passe doivent être hachés. L'accès aux routes doit être strictement protégé par des Middlewares selon les rôles.

Performances : Le changement de statut et le système de tracking doivent s'afficher rapidement, idéalement avec des requêtes asynchrones pour éviter les rechargements de page inutiles.

Technologies prévues :

Backend : Framework Laravel (PHP).

Frontend : Blade, Tailwind CSS, et JavaScript (pour la géolocalisation et l'interactivité).

Base de données : MySQL.

Temps réel : HTML5 Geolocation API (pour la capture des coordonnées).