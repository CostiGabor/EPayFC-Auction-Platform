# Projet PRWB 2526 - Groupe a03 - EPayFC

## Installation sous MacOS ou Linux

En développement, donnez les permissions d'écriture aux dossiers uploads et database :

* `sudo chmod -R 777 uploads`
* `sudo chmod -R 777 database`

## Notes de version itération 1

### Liste des utilisateurs et mots de passes

* admin@epfc.eu , password "Password1,", admin
* boverhaegen@epfc.eu, password "Password1,", utilisateur
* mamichel@epfc.eu, password "Password1,", utilisateur
* quhouben@epfc.eu, password "Password1,", utilisateur
* xapigeolet@epfc.eu, password "Password1,", utilisateur
  

  # 🛒 EPayFC - Plateforme de Vente et d'Enchères en Ligne

EPayFC est une application web full-stack développée en PHP, MySQL et JavaScript. Inspirée de plateformes comme eBay, elle permet aux utilisateurs de créer, gérer et enchérir sur des annonces. 

Ce projet met en œuvre une architecture **MVC stricte**, le design pattern **POST-Request-Get (PRG)**, et intègre des services AJAX tout en garantissant une **dégradation gracieuse** (fonctionnement complet même si JavaScript est désactivé côté client).

## 🛠️ Stack Technique

* **Backend :** PHP 8.2 (Architecture MVC MVC sur mesure, PDO).
* **Base de données :** MySQL (Requêtes préparées, intégrité référentielle, clés étrangères).
* **Frontend :** HTML5, CSS3 (Bootstrap 5.3), JavaScript (jQuery, AJAX, jQuery UI pour le Drag & Drop).
* **Traitement d'images :** Librairie PHP GD (redimensionnement, compression, génération de vignettes).

## ✨ Fonctionnalités Principales

### 1. Gestion des Ventes et Enchères
* **Double système de vente :** Support des enchères pures, de l'achat immédiat, ou d'un modèle hybride combinant les deux.
* **Moteur d'enchères sécurisé :** Validation stricte des montants (l'offre doit dépasser l'offre actuelle ou le prix de départ), gestion des dates de clôture et attribution automatique au meilleur enchérisseur.
* **Historique :** Suivi complet des offres d'achat, des ventes réalisées et des objets remportés.

### 2. Expérience Utilisateur (UI/UX) & Dynamisme (AJAX/jQuery)
* **Recherche Fulltext & Filtrage :** Recherche dynamique par mot-clé et catégories. L'état de la recherche est propagé dans toute la navigation de manière "stateless" (encodage Base64URL dans les paramètres GET), sans utiliser de variables de session.
* **Gestion des images par Drag & Drop :** Upload d'images multiples, redimensionnement automatique côté serveur (max 1080px et vignettes 360px), et réorganisation de l'ordre d'affichage via glisser-déposer (jQuery UI).
* **Validations en temps réel :** Contrôles synchrones et asynchrones (ex: vérification de la disponibilité de l'email/pseudo) sans rechargement de page.

### 3. Architecture et Sécurité
* **Graceful Degradation :** L'intégralité du site (y compris les formulaires complexes, la gestion d'images et les catégories) est fonctionnelle même lorsque le JavaScript est désactivé dans le navigateur.
* **Sécurité des données :** Hachage des mots de passe, requêtes SQL préparées contre les injections, et nettoyage systématique des inputs (XSS).
* **Temps Simulé (Time Mocking) :** Implémentation d'une API de temps interne (indépendante de l'horloge système) permettant d'avancer le temps de l'application pour tester la clôture des enchères dans le futur, sans corrompre la base de données.

---

## 🚀 Guide d'Installation (Environnement XAMPP)

Suivez ces instructions pour déployer le projet localement :

1. **Prérequis :** Disposer de [XAMPP](https://www.apachefriends.org/) (avec PHP 8.2 minimum) et lancer les modules **Apache** et **MySQL**.
2. **Cloner le projet :**
   Placez le dossier du projet dans le répertoire racine de votre serveur web :
   `C:\xampp\htdocs\prwb_2526_xyy` (remplacez le nom du dossier par celui du projet).
3. **Base de données :**
   * Ouvrez phpMyAdmin (`http://localhost/phpmyadmin`).
   * Importez le schéma de la base de données situé dans le dossier `database/` (fichiers `.sql` fournis).
4. **Configuration :**
   Vérifiez que les identifiants de base de données (root/sans mot de passe par défaut sur XAMPP) sont corrects dans les fichiers `dev.ini` et `prod.ini`.
5. **Lancer l'application :**
   Accédez à l'URL suivante dans votre navigateur : 
   `http://localhost/prwb_2526_xyy/`

---
*Projet réalisé dans le cadre de la dernière année de Bachelier en Informatique - Développement d'applications (EPFC, Bruxelles).*