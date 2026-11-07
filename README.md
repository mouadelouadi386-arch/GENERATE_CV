# Générateur de CV

Application web de création et de consultation de CV, développée en PHP natif, MySQL et JavaScript. Elle propose six modèles et permet d'exporter des CV en PDF.

## Prérequis

- PHP 8 ou plus avec PDO MySQL
- MySQL ou MariaDB
- Apache (par exemple avec XAMPP)
- Composer (facultatif : aucune dépendance Composer n'est actuellement requise)

## Installation locale avec XAMPP

1. Placez le projet dans `C:\xampp\htdocs\cvfix` (ou dans un autre sous-dossier de `htdocs`).
2. Démarrez Apache et MySQL depuis le panneau de contrôle XAMPP.
3. Créez la base de données en important `database/schema.sql` dans phpMyAdmin. Le script crée la base `cv_generator` et ses tables.
4. Copiez `.env.example` vers `.env`, puis ajustez les paramètres de connexion MySQL dans `.env`.
5. Ouvrez `http://localhost/cvfix/` dans votre navigateur. Si le dossier du projet porte un autre nom, remplacez `cvfix` dans l'adresse.

L'application écrit les photos et les PDF générés dans `public/uploads/`. Ce dossier est ignoré par Git, car il peut contenir des données personnelles. Il sera créé au besoin par l'application ; si votre configuration ne permet pas sa création, créez-le et donnez au processus Apache le droit d'y écrire.

## Structure

- `index.php` : point d'entrée de l'application.
- `backend/config/` : configuration de la base de données.
- `backend/controllers/` : contrôleurs de l'application.
- `backend/models/` : accès aux données.
- `backend/views/` : vues HTML.
- `backend/helpers/` : utilitaires, dont le rendu PDF.
- `database/schema.sql` : schéma et données initiales des modèles.
- `public/assets/`, `public/css/`, `public/js/`, `public/style/` et `public/templates/` : ressources et modèles visuels.
- `public/uploads/` : fichiers générés à l'exécution, non versionnés.

## Git et données sensibles

Ne versionnez jamais `.env`, les photos des utilisateurs ou les PDF générés. Le fichier `.env.example` ne contient que des valeurs d'exemple ; copiez-le en `.env` pour votre configuration locale. Avant le premier push, vérifiez les fichiers sélectionnés avec `git status` et n'ajoutez pas de fichiers ignorés avec `git add -f`.
