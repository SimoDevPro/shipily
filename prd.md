Product Requirements Document (PRD): Système de Gestion de Livraison (PFE)
1. Project Overview & Business Logic
Modèle Métier: Agence de Livraison (Intermédiaire). Les clients (E-commerçants) enregistrent des colis, l'agence gère la logistique, les livreurs livrent, et les destinataires (sans compte) reçoivent le colis et donnent leur avis.

Stack Technique: Laravel (PHP 8.x), Livewire ou Inertia.js (pour la réactivité), Tailwind CSS, MySQL.

Design Language: Interface premium, propre et minimaliste (Aesthétique similaire à Cal AI).

Acteurs: * Admin (Agence - Gestion totale).

Client (E-commerce - Enregistre les colis).

Livreur (Effectue les livraisons).

Destinataire (Utilisateur final - Accès via lien de tracking uniquement).

Phase 1: Database Architecture & Authentication
Objectif: Préparer le terrain avec les tables et les rôles.

Tasks for Cursor/Claude:
Auth Setup: Installer Laravel Breeze.

Migrations - Table users: Ajouter role (enum: admin, client, livreur), phone, et champs GPS (current_lat, current_lng nullable pour les livreurs).

Migrations - Table colis: * Clés étrangères: client_id, livreur_id (nullable).

Champs de base: code_suivi (Unique), nom_destinataire, telephone_destinataire, adresse_destinataire, ville_destinataire (ex: Errachidia, Aoufous).

Finances: prix_colis, frais_livraison.

statut (enum: enregistre, ramasse, en_cours, livre, retourne).

Migrations - Table colis_histories: colis_id, user_id (nullable), statut, localisation.

Migrations - Table avis: colis_id (unique), livreur_id, note (integer 1-5), commentaire.

Middlewares: Créer IsAdmin, IsClient, IsLivreur.

Phase 2: E-commerce Client Portal (Expéditeur)
Objectif: Permettre aux e-commerçants d'enregistrer leurs colis.

Tasks for Cursor/Claude:
Client Dashboard: Afficher les statistiques de base (Colis livrés, En cours, Montant total à récupérer).

Enregistrement de Colis: Formulaire pour créer un colis (Infos du destinataire + Prix du colis). Action: Sauvegarde le colis avec le statut enregistre et génère un code_suivi unique.

Liste des Colis: Tableau filtrable affichant l'état en temps réel de leurs expéditions.

Phase 3: Admin Control Center
Objectif: Le cœur logistique pour l'agence.

Tasks for Cursor/Claude:
Vue Globale: Dashboard avec KPIs (Nouveaux colis, Colis en cours, Performances des livreurs).

Gestion et Affectation: Tableau des colis. L'Admin peut sélectionner un colis enregistre, le marquer comme ramasse, et l'affecter à un livreur_id.

Tracking Admin: Voir la position du livreur sur une carte (si les coordonnées GPS du livreur sont à jour).

Action Historique: Chaque changement de statut déclenché par l'Admin crée une ligne dans colis_histories.

Phase 4: Interface Livreur & GPS Tracking
Objectif: Web App mobile-friendly pour les livreurs sur le terrain.

Tasks for Cursor/Claude:
Liste des Tâches: Afficher uniquement les colis où livreur_id correspond à l'utilisateur connecté.

Actions de Livraison: Boutons pour changer le statut (En cours, Livré, Retourné).

Live Location (HTML5 Geolocation API): Quand le livreur clique sur "Démarrer la tournée", un script JS envoie ses coordonnées (Lat/Lng) à Laravel via une API Update Route toutes les minutes. Mettre à jour current_lat et current_lng dans la table users.

Phase 5: Tracking Destinataire & Système d'Avis (Feedback)
Objectif: L'interface publique pour le client final, sans création de compte.

Tasks for Cursor/Claude:
Page de Suivi Publique: Créer une route /track/{code_suivi}. Afficher la "Timeline" du colis en lisant la table colis_histories.

Carte Live (Optionnel via Websockets): Afficher un marqueur sur la carte en fonction des coordonnées du livreur affecté (Pusher / Laravel Echo).

Système de Feedback: * Si le statut du colis est livre, masquer la timeline et afficher un formulaire d'Avis (1 à 5 étoiles + Commentaire).

Soumission: Sauvegarder dans la table avis (lier colis_id et livreur_id).

Phase 6: Système de Notifications & Final Polish
Objectif: Communication et finition.

Tasks for Cursor/Claude:
Database Notifications: Utiliser le système natif de Laravel pour notifier le Livreur quand un colis lui est affecté, et l'Admin quand un colis est retourné ou reçoit un mauvais avis.

SMS/Email Alert (Mockup): Préparer la logique (Events/Listeners) qui simule l'envoi du lien de tracking au Destinataire dès que le colis passe à en_cours.

UI Refinement: S'assurer que les formulaires, les boutons et les tableaux utilisent un design minimaliste avec un bon usage de l'espace blanc (White space).