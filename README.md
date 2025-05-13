# Rahalla - Plateforme de Gestion Touristique

## Description du Projet

Rahalla est une application de gestion touristique complète développée en JavaFX. Elle offre une plateforme intégrée pour la gestion des voyages, événements, restaurants, matériels, réclamations clients et blogs. Cette application permet aux utilisateurs de réserver des voyages, participer à des événements, consulter des restaurants, louer du matériel, soumettre des réclamations et partager leurs expériences via le blog.

## Modules Principaux

### 1. Gestion des Utilisateurs
Inscription et authentification
Gestion des profils utilisateurs
Tableau de bord administrateur
Contrôle des accès et permissions


### 2. Gestion des Événements
Création et publication d'événements
Réservation de places
Calendrier des événements
Statistiques de participation


### 3. Gestion des Restaurants
Catalogue des restaurants partenaires
Réservations de tables
Avis et notations
Menus et spécialités


### 4. Gestion du Matériel
Inventaire du matériel disponible
Système de location
Suivi des retours
Maintenance et disponibilité


### 5. Système de Réclamations
Soumission de réclamations catégorisées
Suivi du statut des réclamations
Système de réponse aux réclamations
Statistiques et analyses des avis clients
Chat intégré pour assistance


### 6. Blog et Partage d'Expériences
Publication d'articles
Commentaires et interactions
Partage de photos et expériences
Recommandations de voyages


## Technologies Utilisées

### Backend
Java 17
JavaFX 21.0.2 (Interface utilisateur)
MySQL (Base de données)
Jakarta Mail (Gestion des emails)


### Frontend
FXML (Structure des interfaces)
CSS (Stylisation)
ControlsFX (Composants UI avancés)
Ikonli (Icônes et éléments graphiques)
BootstrapFX (Styles modernes)
RichTextFX (Édition de texte enrichi)


### Outils et Bibliothèques
Maven (Gestion des dépendances)
PDFBox (Génération de documents PDF)
ZXing (Génération de codes QR)
JSON (Traitement des données)
OkHttp (Requêtes HTTP)
JUnit (Tests unitaires)


## Architecture du Projet

Le projet suit une architecture MVC (Modèle-Vue-Contrôleur) :
- *Modèle* : Package Entites contenant les classes de données
- *Vue* : Fichiers FXML dans le répertoire resources
- *Contrôleur* : Package Controller gérant la logique métier
- *Services* : Package Services pour l'accès aux données et opérations
- *Utilitaires* : Package Utils pour les fonctions communes

## Installation et Déploiement

### Prérequis
JDK 17 ou supérieur
Maven 3.8 ou supérieur
MySQL 8.0 ou supérieur


### Installation
1. Cloner le dépôt : git clone https://github.com/aziztemimii/javaPI.git
2. Naviguer vers le répertoire du projet : cd javaPI
3. Installer les dépendances : mvn install
Configurer la base de données dans le fichier de configuration

5. Lancer l'application : mvn javafx:run


## Équipe de Développement

Aziz Temimi
Issam Saffi
Azza Turki
Wadhah Hmissi
yassine rebhi
Nour mahmoud


## Licence

© 2025 Rahalla. Tous droits réservés.
