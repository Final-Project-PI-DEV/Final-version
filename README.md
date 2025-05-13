# Plateforme de Services Touristiques

## Description du Projet

Cette plateforme est une application web complète développée avec Symfony 6.4 qui offre divers services touristiques en Tunisie. Elle permet aux utilisateurs de découvrir, réserver et évaluer des restaurants, des activités de loisirs, des événements, et des services de transport. La plateforme intègre également un système de gestion des réclamations et un forum communautaire.

## Fonctionnalités Principales

### Gestion des Utilisateurs
Inscription et authentification sécurisée
Profils utilisateurs personnalisables
Système de réinitialisation de mot de passe
Rôles et permissions différenciés


### Services Touristiques
- *Restaurants*: Consultation, réservation et évaluation de restaurants
- *Loisirs*: Découverte et réservation d'activités de loisirs
- *Événements*: Calendrier d'événements avec filtrage par date et lieu
- *Transports*: Réservation de services de transport

### Système de Publication
Forum communautaire avec publications et commentaires
Système de likes et d'interactions
Partage de photos et d'expériences
Filtrage des publications par lieu et date


### Gestion des Réclamations
Système complet de soumission et suivi des réclamations
Catégorisation des réclamations:

  - Produit défectueux (Produit de mauvaise qualité, Produit ne fonctionne pas correctement, Accessoires ou pièces manquants, Autre)
  - Service non conforme (Mauvaise gestion des réservations, Sécurité et nuisances sur place, Accueil et service client médiocre, Autre)
  - Problème technique (Bug sur le site web, Problème de connexion à mon compte, Erreur de paiement, Message d'erreur lors d'une action, Autre)
  - Autre
Système de réponse et de suivi des réclamations
Évaluation de la satisfaction client


### Paiement en Ligne
Intégration avec Stripe pour les paiements sécurisés
Gestion de panier d'achat
Historique des transactions


### Fonctionnalités Supplémentaires
Carte interactive des services et attractions
Système de messagerie interne
Génération de QR codes
Chatbot d'assistance


## Technologies Utilisées

### Backend
- *Symfony 6.4*: Framework PHP robuste et moderne
- *Doctrine ORM*: Gestion de la base de données et des entités
- *Symfony Security*: Système d'authentification et d'autorisation
- *Symfony Mailer*: Envoi d'emails avec intégration Google

### Frontend
- *Twig*: Moteur de templates pour le rendu des vues
- *Stimulus*: Framework JavaScript léger
- *Turbo*: Navigation fluide sans rechargement complet
- *Asset Mapper*: Gestion des assets frontend

### Autres
- *Stripe*: Traitement des paiements
- *TCPDF*: Génération de documents PDF
- *QR Code*: Génération de codes QR
- *reCAPTCHA v3*: Protection contre les robots

## Installation

### Prérequis
PHP 8.1 ou supérieur
Composer
Symfony CLI
Node.js et npm
Base de données MySQL ou PostgreSQL


### Étapes d'installation

Cloner le dépôt

git clone [URL_DU_DEPOT]
cd [NOM_DU_PROJET]

Installer les dépendances PHP

composer install

Installer les dépendances JavaScript

npm install

Configurer les variables d'environnement

# Copier le fichier .env en .env.local et configurer les paramètres
cp .env .env.local
# Éditer .env.local avec les informations de connexion à la base de données et autres configurations

Créer la base de données et exécuter les migrations

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

Démarrer le serveur de développement

symfony server:start

## Structure du Projet

- *src/Controller/*: Contrôleurs de l'application
- *src/Entity/*: Entités Doctrine (modèles de données)
- *src/Repository/*: Repositories pour l'accès aux données
- *src/Form/*: Formulaires de l'application
- *src/Service/*: Services métier
- *templates/*: Templates Twig pour le rendu des vues
- *public/*: Fichiers accessibles publiquement
- *assets/*: Fichiers source pour le frontend (CSS, JS)
- *config/*: Fichiers de configuration
- *migrations/*: Migrations de base de données

## Contribution

Fork le projet

2. Créer une branche pour votre fonctionnalité (git checkout -b feature/amazing-feature)
3. Commit vos changements (git commit -m 'Add some amazing feature')
4. Push vers la branche (git push origin feature/amazing-feature)
Ouvrir une Pull Request


## Licence

Ce projet est sous licence propriétaire. Tous droits réservés.
