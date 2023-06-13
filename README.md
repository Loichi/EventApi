# EventApi
Une Api de création d'évènement et gestion d'invitation

# Prérequis
PHP (version 8.2.0)
Composer (version 2.5.5)
MySQL

# Installation
Cloner ce dépôt : git clone https://github.com/Loichi/EventApi.git
Accéder au répertoire du projet : cd ton-projet
Installer les dépendances : composer install
Configurer les paramètres de base de données dans le fichier .env
Créer la base de données : php bin/console doctrine:database:create
Exécuter les migrations : php bin/console doctrine:migrations:migrate
Démarrer le serveur de développement : php bin/console server:run

# Documentation
La documentation complète de l'API est disponible à l'adresse suivante :

http://localhost:8000/api/docs

Elle a été générée automatiquement grâce à API Platform. Tu y trouveras des informations détaillées sur les ressources, les opérations disponibles et les schémas de données.
