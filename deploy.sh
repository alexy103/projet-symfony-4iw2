#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

ENV_FILE=.env.prod.local
COMPOSE="docker compose --env-file $ENV_FILE -f compose.prod.yaml"

if [ ! -f "$ENV_FILE" ]; then
    echo "Erreur : $ENV_FILE introuvable. Copie .env.prod.local.example et remplis-le."
    exit 1
fi

echo ">> Récupération du code"
git pull --ff-only

echo ">> Build de l'image de production"
$COMPOSE build

echo ">> Démarrage des conteneurs"
$COMPOSE up -d

echo ">> Attente de la base de données"
until $COMPOSE exec -T db pg_isready -U app -d app >/dev/null 2>&1; do
    sleep 1
done

echo ">> Nettoyage du cache"
$COMPOSE exec -T php php bin/console cache:clear --no-interaction

echo ">> Migrations de base de données"
$COMPOSE exec -T php php bin/console doctrine:migrations:migrate --no-interaction

echo ">> Chargement des fixtures (purge puis recharge la base)"
$COMPOSE exec -T php php bin/console hautelook:fixtures:load --no-interaction

echo ">> Correction des droits (var/ + uploads)"
$COMPOSE exec -T php chown -R www-data:www-data var public/uploads

echo ">> Déploiement terminé."
