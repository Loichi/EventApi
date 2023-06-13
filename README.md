# EventApi
Une Api de création d'évènement et gestion d'invitation

# Prérequis
PHP (version 8.2.0)<br>
Composer (version 2.5.5)<br>
MySQL<br>

# Installation
Cloner ce dépôt : git clone https://github.com/Loichi/EventApi.git<br>
Accéder au répertoire du projet : cd ton-projet<br>
Installer les dépendances : composer install<br>
Configurer les paramètres de base de données dans le fichier .env<br>
Créer la base de données : php bin/console doctrine:database:create<br>
Exécuter les migrations : php bin/console doctrine:migrations:migrate<br>
Démarrer le serveur de développement : php bin/console server:run<br>

# Documentation
La documentation complète de l'API est disponible à l'adresse suivante :<br>

http://localhost:8000/api/docs<br>

Elle a été générée automatiquement grâce à API Platform. Tu y trouveras des informations détaillées sur les ressources, les opérations disponibles et les schémas de données.<br>
