# Shipily 📦 - Système de Gestion & de Suivi de Livraisons

Shipily est une application web centralisée de gestion et de suivi en temps réel des livraisons, spécialement conçue pour une agence logistique intermédiaire (Projet de Fin d'Études). Elle permet de connecter efficacement les agences, les e-commerçants (expéditeurs), les livreurs et les destinataires finaux.

L'interface utilisateur a été conçue selon des normes de design modernes, premium et minimalistes (inspirées de l'esthétique épurée de *Cal.com* / *Cal AI*), avec une approche entièrement **responsive / Mobile-first** pour les livreurs sur le terrain.

---

## 🚀 Fonctionnalités Clés par Acteur

### 👤 1. Espace Administrateur (L'Agence)
- **Gestion des Utilisateurs :** Création, modification et suppression des clients (e-commerçants) et des livreurs.
- **Centre de Logistique :** Validation des colis enregistrés, passage au statut "Ramassé", et affectation intelligente aux livreurs disponibles.
- **Suivi GPS en Temps Réel :** Visualisation cartographique de la position actuelle des livreurs durant leurs tournées de livraison.
- **Historique Logistique :** Suivi automatisé des étapes de livraison et génération d'entrées historiques à chaque changement de statut.

### 💼 2. Espace Client (L'E-commerçant / Expéditeur)
- **Tableau de Bord :** Statistiques globales (colis livrés, en cours, fonds totaux en attente de récupération).
- **Création de Colis :** Enregistrement rapide des nouveaux colis (informations destinataire, prix du colis) générant un **code de suivi unique**.
- **Suivi en Direct :** Vue globale et filtrable sous forme de tableau interactif pour suivre l'état de chaque expédition.

### 🚴 3. Espace Livreur (Mobile-First)
- **Feuille de Route Mobile :** Liste simplifiée et optimisée pour smartphone affichant les colis affectés.
- **Gestion du Cycle de Livraison :** Mise à jour rapide des statuts des colis (`En cours`, `Livré`, `Retourné`).
- **Suivi GPS Actif :** Option pour démarrer la tournée qui transmet automatiquement la position géographique du livreur en arrière-plan à l'agence (via l'API Geolocation HTML5).

### 🔍 4. Espace Destinataire (Client Final - Public)
- **Page de Tracking Publique :** Accessibilité directe via `/track/{code_suivi}` sans authentification requise.
- **Timeline de Livraison :** Visualisation de toutes les étapes clés du colis (de l'enregistrement à la livraison).
- **Système de Feedback :** Une fois le colis marqué comme livré, le destinataire peut laisser une évaluation (1 à 5 étoiles) ainsi qu'un commentaire sur la prestation du livreur.

---

## 🛠️ Stack Technique

- **Framework Backend :** Laravel v12 (PHP 8.2+)
- **Architecture Frontend :** Livewire v4 & Livewire Volt v1 (Composants Single-File réactifs)
- **Composants UI :** Flux UI (Premium Reactivity)
- **Stylisation :** Tailwind CSS v4 (Design minimaliste et moderne)
- **Base de Données :** MySQL
- **APIs :** HTML5 Geolocation API pour le tracking de position

---

## ⚙️ Installation & Configuration

### Prérequis
- PHP >= 8.2
- Composer
- Node.js & NPM
- Un serveur de base de données (MySQL)

### Étapes d'installation

1. **Cloner le projet**
   ```bash
   git clone <repository-url>
   cd shipily
   ```

2. **Installer les dépendances PHP & JS**
   ```bash
   composer install
   npm install
   ```

3. **Configurer l'environnement**
   Copiez le fichier d'exemple et configurez vos accès à la base de données :
   ```bash
   cp .env.example .env
   ```
   Générez la clé d'application :
   ```bash
   php artisan key:generate
   ```

4. **Migrations et Données de Test (Seeding)**
   Exécutez les migrations de base de données tout en injectant les données de démonstration :
   ```bash
   php artisan migrate --seed
   ```

5. **Lancer le serveur de développement**
   Démarrez le serveur Laravel :
   ```bash
   php artisan serve
   ```
   Démarrez le serveur Vite pour les assets et les styles :
   ```bash
   npm run dev
   ```

---

## 🔑 Comptes de Démonstration (Seeded)

Lors du seeding de la base de données, trois comptes types sont automatiquement créés avec le mot de passe par défaut : `password`

| Rôle | Email | Mot de passe |
| :--- | :--- | :--- |
| **Administrateur** | `admin@shipily.test` | `password` |
| **Client (E-commerçant)** | `client@shipily.test` | `password` |
| **Livreur** | `livreur@shipily.test` | `password` |

---

## 🧪 Tests

Le projet utilise **Pest PHP** pour ses tests unitaires et fonctionnels.

Pour lancer la suite de tests :
```bash
php artisan test
```
