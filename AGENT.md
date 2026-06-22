# AGENT.md — Contexte projet Symfony

## Langue et style

Toutes les réponses, explications, commentaires de revue et propositions doivent être rédigés en français, sauf demande explicite contraire.

Le projet doit rester simple, maintenable et réalisable rapidement. Ne pas proposer de refonte massive ou d’architecture trop complexe sans raison forte.

Quand du code existant est modifié :
- ne pas renommer les variables, propriétés, méthodes, classes ou fichiers sans demande explicite ;
- ne pas supprimer les commentaires existants ;
- expliquer précisément les changements effectués ;
- signaler les incertitudes au lieu d’affirmer sans vérifier.

## Contexte général du projet

Le projet est une application web Symfony + Twig appelée provisoirement **générateur d’excuses**.

L’idée est de permettre à des utilisateurs de créer, générer, commenter, noter et faire valider des excuses selon différents contextes.

Exemples de cas d’usage :
- générer une excuse pour un retard ;
- créer une excuse pour une absence ;
- proposer une excuse pour un devoir non rendu ;
- reformuler une excuse dans un ton professionnel ;
- soumettre une excuse à validation ;
- commenter ou noter une excuse ;
- attribuer des badges aux utilisateurs.

Le ton du projet peut être un peu décalé, humoristique et absurde, mais le code doit rester propre, professionnel et conforme aux attentes d’un projet Symfony.

## Contraintes pédagogiques importantes

Le projet doit respecter les contraintes suivantes :

- Symfony 8.1 avec Twig.
- Application web professionnelle, robuste et sécurisée.
- Authentification avec le Security Component Symfony.
- Minimum 3 rôles utilisateurs.
- Minimum 10 entités Doctrine.
- Héritage Doctrine obligatoire.
- Minimum 2 relations ManyToMany.
- Minimum 8 relations OneToMany / ManyToOne.
- Voter personnalisé obligatoire.
- API JSON avec Serializer Symfony.
- Envoi d’e-mails avec Mailer / Notifier.
- Consommation d’une API externe avec HttpClient.
- Interface d’administration sécurisée.
- Formulaires dynamiques avec Form Events.
- Repositories avec QueryBuilder pour filtres avancés.
- Minimum 10 pages Twig distinctes.
- Au moins 1 test unitaire et 1 test fonctionnel.
- Pipeline CI avec lint Symfony, PHPStan et tests.
- Déploiement en ligne.

## Rôles prévus

Les rôles de base sont :

```txt
ROLE_USER
ROLE_VALIDATOR
ROLE_ADMIN
```

### ROLE_USER

Utilisateur classique.

Il peut :
- créer des excuses ;
- modifier ses propres excuses selon certaines conditions ;
- commenter des excuses ;
- consulter ses notifications ;
- recevoir des badges.

### ROLE_VALIDATOR

Validateur.

Il peut :
- consulter les excuses en attente ;
- accepter une excuse ;
- refuser une excuse ;
- demander des modifications ;
- ajouter un commentaire de validation.

### ROLE_ADMIN

Administrateur.

Il peut :
- gérer les utilisateurs ;
- gérer les excuses ;
- gérer les catégories ;
- gérer les contextes ;
- gérer les tons ;
- gérer les tags ;
- gérer les badges ;
- accéder à l’interface d’administration.

## Entités prévues

Le modèle de données initial contient les entités suivantes :

```txt
User
Excuse
ClassicExcuse
EmergencyExcuse
ProfessionalExcuse
ExcuseCategory
ExcuseContext
ExcuseTone
ExcuseComment
ExcuseRating
ExcuseValidation
Tag
Badge
Notification
```

Cela donne 14 entités PHP, avec un héritage Doctrine porté par `Excuse`.

## Héritage Doctrine

L’héritage prévu est basé sur l’entité `Excuse`.

```txt
Excuse
├── ClassicExcuse
├── EmergencyExcuse
└── ProfessionalExcuse
```

### Excuse

Entité parente représentant une excuse générique.

Champs prévus :
- `id`
- `author`
- `category`
- `context`
- `tone`
- `title`
- `content`
- `status`
- `urgencyLevel`
- `credibilityScore`
- `type`
- `createdAt`
- `updatedAt`

Statuts possibles :
```txt
pending
validated
rejected
```

### ClassicExcuse

Sous-type d’excuse pour les excuses classiques du quotidien.

Exemples :
- retard léger ;
- oubli ;
- absence courte ;
- petit imprévu.

Champs spécifiques :
- `estimatedDelay`
- `isReusable`

### EmergencyExcuse

Sous-type d’excuse pour les excuses urgentes ou dramatiques.

Exemples :
- panne grave ;
- problème familial ;
- incident de transport ;
- urgence de dernière minute.

Champs spécifiques :
- `emergencyLevel`
- `requiresProof`

### ProfessionalExcuse

Sous-type d’excuse adaptée au monde professionnel.

Exemples :
- réunion manquée ;
- mail oublié ;
- deadline ratée ;
- retard client.

Champs spécifiques :
- `targetRecipient`
- `professionalTone`

## Description des tables

### user

Représente les utilisateurs de l’application.

Champs principaux :
- `id`
- `email`
- `roles`
- `password`
- `pseudo`
- `isVerified`
- `createdAt`

Relations :
- un utilisateur peut créer plusieurs excuses ;
- un utilisateur peut écrire plusieurs commentaires ;
- un utilisateur peut donner plusieurs notes ;
- un utilisateur peut recevoir plusieurs notifications ;
- un utilisateur peut obtenir plusieurs badges.

### excuse

Table centrale du projet.

Elle contient les excuses créées ou générées par les utilisateurs.

Relations :
- une excuse appartient à un auteur ;
- une excuse appartient à une catégorie ;
- une excuse appartient à un contexte ;
- une excuse possède un ton ;
- une excuse peut recevoir plusieurs commentaires ;
- une excuse peut recevoir plusieurs notes ;
- une excuse peut recevoir plusieurs validations ;
- une excuse peut avoir plusieurs tags.

### classic_excuse

Sous-table liée à `excuse`.

Contient les données spécifiques aux excuses classiques.

### emergency_excuse

Sous-table liée à `excuse`.

Contient les données spécifiques aux excuses urgentes.

### professional_excuse

Sous-table liée à `excuse`.

Contient les données spécifiques aux excuses professionnelles.

### excuse_category

Catégorie générale de l’excuse.

Exemples :
- Retard
- Absence
- Devoir non rendu
- Réunion manquée
- Deadline ratée
- Oubli de mail

### excuse_context

Contexte dans lequel l’excuse est utilisée.

Exemples :
- École
- Travail
- Famille
- Amis
- Administration
- Transport

### excuse_tone

Ton de l’excuse.

Exemples :
- Sérieux
- Crédible
- Dramatique
- Absurde
- Professionnel
- Passif-agressif

Le champ `riskLevel` permet d’indiquer si le ton est risqué ou non.

### excuse_comment

Commentaire laissé sur une excuse.

Chaque commentaire appartient :
- à une excuse ;
- à un utilisateur.

### excuse_rating

Note donnée à une excuse.

Chaque note appartient :
- à une excuse ;
- à un utilisateur.

Le score peut représenter la qualité globale, la crédibilité ou le côté drôle.

### excuse_validation

Validation d’une excuse par un validateur.

Chaque validation appartient :
- à une excuse ;
- à un utilisateur validateur.

Statuts possibles :
```txt
accepted
rejected
```

### tag

Mot-clé associé aux excuses.

Exemples :
- crédible
- risqué
- absurde
- scolaire
- professionnel
- urgent
- légendaire

### excuse_tag

Table de liaison ManyToMany entre `excuse` et `tag`.

### badge

Récompense obtenue par les utilisateurs.

Exemples :
- Mytho débutant
- Retardataire professionnel
- Maître de l’improvisation
- Excuse légendaire
- Validateur impitoyable

### user_badge

Table de liaison ManyToMany entre `user` et `badge`.

### notification

Notification envoyée à un utilisateur.

Exemples :
- excuse soumise à validation ;
- excuse validée ;
- excuse refusée ;
- nouveau commentaire ;
- nouveau badge obtenu.

## Relations principales

### Relations ManyToOne / OneToMany

```txt
Excuse ManyToOne User
Excuse ManyToOne ExcuseCategory
Excuse ManyToOne ExcuseContext
Excuse ManyToOne ExcuseTone

ExcuseComment ManyToOne Excuse
ExcuseComment ManyToOne User

ExcuseRating ManyToOne Excuse
ExcuseRating ManyToOne User

ExcuseValidation ManyToOne Excuse
ExcuseValidation ManyToOne User

Notification ManyToOne User
```

Ces relations permettent de dépasser les 8 relations OneToMany / ManyToOne demandées.

### Relations ManyToMany

```txt
Excuse ManyToMany Tag
User ManyToMany Badge
```

Ces deux relations remplissent l’exigence de 2 relations ManyToMany.

## DBML de référence

```dbml
Table user {
  id integer [primary key]
  email varchar(180) [unique, not null]
  roles json [not null]
  password varchar(255) [not null]
  pseudo varchar(100) [not null]
  is_verified boolean [default: false]
  created_at datetime [not null]
}

Table excuse {
  id integer [primary key]
  author_id integer [not null]
  category_id integer [not null]
  context_id integer [not null]
  tone_id integer [not null]

  title varchar(255) [not null]
  content text [not null]
  status varchar(50) [not null]
  urgency_level integer [not null]
  credibility_score integer
  type varchar(50) [not null]

  created_at datetime [not null]
  updated_at datetime
}

Table classic_excuse {
  id integer [primary key]
  excuse_id integer [unique, not null]

  estimated_delay integer
  is_reusable boolean [default: true]
}

Table emergency_excuse {
  id integer [primary key]
  excuse_id integer [unique, not null]

  emergency_level integer [not null]
  requires_proof boolean [default: false]
}

Table professional_excuse {
  id integer [primary key]
  excuse_id integer [unique, not null]

  target_recipient varchar(100)
  professional_tone varchar(100)
}

Table excuse_category {
  id integer [primary key]
  name varchar(100) [not null]
  description text
  is_active boolean [default: true]
}

Table excuse_context {
  id integer [primary key]
  name varchar(100) [not null]
  description text
}

Table excuse_tone {
  id integer [primary key]
  name varchar(100) [not null]
  description text
  risk_level integer [not null]
}

Table excuse_comment {
  id integer [primary key]
  excuse_id integer [not null]
  author_id integer [not null]

  content text [not null]
  created_at datetime [not null]
}

Table excuse_rating {
  id integer [primary key]
  excuse_id integer [not null]
  author_id integer [not null]

  score integer [not null]
  created_at datetime [not null]
}

Table excuse_validation {
  id integer [primary key]
  excuse_id integer [not null]
  validator_id integer [not null]

  status varchar(50) [not null]
  comment text
  validated_at datetime [not null]
}

Table tag {
  id integer [primary key]
  name varchar(100) [not null]
  color varchar(30)
}

Table excuse_tag {
  excuse_id integer [not null]
  tag_id integer [not null]

  indexes {
    (excuse_id, tag_id) [primary key]
  }
}

Table badge {
  id integer [primary key]
  name varchar(100) [not null]
  description text
  icon varchar(255)
}

Table user_badge {
  user_id integer [not null]
  badge_id integer [not null]

  indexes {
    (user_id, badge_id) [primary key]
  }
}

Table notification {
  id integer [primary key]
  user_id integer [not null]

  title varchar(255) [not null]
  message text [not null]
  is_read boolean [default: false]
  created_at datetime [not null]
}

Ref: excuse.author_id > user.id

Ref: excuse.category_id > excuse_category.id
Ref: excuse.context_id > excuse_context.id
Ref: excuse.tone_id > excuse_tone.id

Ref: classic_excuse.excuse_id > excuse.id
Ref: emergency_excuse.excuse_id > excuse.id
Ref: professional_excuse.excuse_id > excuse.id

Ref: excuse_comment.excuse_id > excuse.id
Ref: excuse_comment.author_id > user.id

Ref: excuse_rating.excuse_id > excuse.id
Ref: excuse_rating.author_id > user.id

Ref: excuse_validation.excuse_id > excuse.id
Ref: excuse_validation.validator_id > user.id

Ref: excuse_tag.excuse_id > excuse.id
Ref: excuse_tag.tag_id > tag.id

Ref: user_badge.user_id > user.id
Ref: user_badge.badge_id > badge.id

Ref: notification.user_id > user.id
```

## Voter personnalisé prévu

Un Voter doit gérer les permissions fines sur les excuses.

Règles envisagées :

Un utilisateur peut modifier une excuse uniquement si :
- il est connecté ;
- il est l’auteur de l’excuse ;
- l’excuse est en `rejected`.

Un validateur peut valider une excuse uniquement si :
- il possède `ROLE_VALIDATOR` ;
- l’excuse est en `pending`.

Un admin peut tout gérer.

## API JSON prévue

Endpoints possibles :

```txt
GET /api/v1/excuses/random
GET /api/v1/excuses/{id}
```

L’API doit utiliser le Serializer Symfony avec des groupes de normalisation.

Exemples de groupes :
```txt
excuse:read
excuse:write
user:read
tag:read
```

## API externe prévue

L’application doit consommer une API externe avec HttpClient.

Idées possibles :
- API d’IA pour générer une excuse ;
- API d’IA pour reformuler une excuse ;
- API pour calculer un score de crédibilité ;
- API externe fictive ou simple si le temps est limité.

Prévoir un service dédié, par exemple :

```txt
ExcuseGeneratorService
```

Ce service peut recevoir :
- une catégorie ;
- un contexte ;
- un ton ;
- un niveau d’urgence.

Et retourner :
- un texte généré ;
- un score de crédibilité ;
- des tags proposés.

## Mailer / Notifier

Utiliser le composant Mailer ou Notifier pour envoyer des messages.

Événements possibles :
- excuse soumise à validation ;
- excuse validée ;
- excuse refusée ;
- nouveau commentaire ;
- nouveau badge obtenu.

Prévoir un service, par exemple :

```txt
ExcuseNotificationService
```

## Formulaires dynamiques

Le formulaire de création d’excuse doit changer selon les choix utilisateur.

Exemples :
- si la catégorie est `Retard`, afficher `estimatedDelay` ;
- si la catégorie est `Absence`, afficher une date de début et une date de fin si ces champs sont ajoutés ;
- si le type est `ProfessionalExcuse`, afficher `targetRecipient` et `professionalTone` ;
- si le type est `EmergencyExcuse`, afficher `emergencyLevel` et `requiresProof`.

Utiliser les Form Events Symfony :
- `PRE_SET_DATA`
- `PRE_SUBMIT`

## Interface d’administration

L’administration peut être faite avec EasyAdminBundle ou en Twig sur mesure.

Le plus rapide est EasyAdminBundle.

Entités à administrer :
- User
- Excuse
- ExcuseCategory
- ExcuseContext
- ExcuseTone
- Tag
- Badge
- Notification

## Pages Twig prévues

Prévoir au moins 10 pages distinctes.

Exemples :
1. Accueil
2. Inscription
3. Connexion
4. Liste des excuses
5. Détail d’une excuse
6. Création d’une excuse
7. Modification d’une excuse
8. Mes excuses
9. Excuses en attente de validation
10. Détail validation d’une excuse
11. Profil utilisateur
12. Notifications
13. Classement des meilleures excuses
14. Administration

## Repositories / QueryBuilder

Prévoir des méthodes personnalisées dans les repositories.

Exemples :
- trouver les excuses les mieux notées ;
- trouver les excuses en attente de validation ;
- filtrer par catégorie, contexte, ton et score ;
- rechercher dans le titre et le contenu ;
- éviter les problèmes N+1 avec des jointures.

Exemples de méthodes :
```txt
findBestRatedExcuses()
findPendingExcusesWithAuthor()
findByFilters()
searchByKeyword()
```

## Tests prévus

### Test unitaire

Tester un service, par exemple :
- `ExcuseGeneratorService`
- `CredibilityScoreService`

Exemple :
- vérifier qu’un score de crédibilité est entre 0 et 100 ;
- vérifier qu’une excuse générée n’est pas vide.

### Test fonctionnel

Tester un scénario web, par exemple :
- connexion utilisateur ;
- création d’une excuse ;
- soumission à validation ;
- accès refusé à une excuse qui n’appartient pas à l’utilisateur.

## CI/CD

Prévoir un pipeline GitHub Actions ou GitLab CI.

Étapes minimales :
- installation des dépendances ;
- lint Symfony ;
- lint Twig ;
- lint YAML ;
- PHPStan niveau 5 minimum recommandé ;
- PHPUnit.

Commandes typiques :
```bash
composer install
php bin/console lint:container
php bin/console lint:twig templates
php bin/console lint:yaml config
vendor/bin/phpstan analyse
php bin/phpunit
```

## Docker / serveur web

Si le projet utilise Nginx, il n’y a pas besoin de `.htaccess`.

Nginx doit pointer vers :

```txt
/var/www/html/app/public
```

La configuration Nginx doit rediriger les routes Symfony vers `index.php`.

Exemple important :

```nginx
location / {
    try_files $uri /index.php$is_args$args;
}
```

Le service PHP doit être accessible via PHP-FPM, souvent :

```nginx
fastcgi_pass php:9000;
```

Le nom `php` doit correspondre au nom du service dans `docker-compose.yml`.

## Ordre de développement conseillé

Ne pas tout développer d’un coup.

Ordre recommandé :

```txt
1. User + authentification
2. ExcuseCategory
3. ExcuseContext
4. ExcuseTone
5. Excuse
6. Héritage Excuse
7. Création / édition / liste / détail des excuses
8. Voter sur Excuse
9. Commentaires
10. Notes
11. Validations
12. Tags
13. Badges
14. Notifications
15. API JSON
16. Mailer
17. API externe
18. Admin
19. Tests
20. CI/CD
```

## Règles de contribution pour les IA

Lorsqu’une IA intervient sur ce projet, elle doit :

- conserver la structure Symfony standard ;
- éviter les architectures trop lourdes ;
- ne pas renommer sans demander ;
- ne pas supprimer les commentaires existants ;
- expliquer toute modification de code ;
- garder le projet compatible avec Symfony 8.1 ;
- privilégier Twig pour l’interface ;
- respecter les contraintes pédagogiques ;
- ne pas inventer de fonctionnalité hors périmètre sans l’indiquer ;
- préférer les solutions simples, testables et livrables rapidement.

## Priorité actuelle

La priorité actuelle est de construire une première base fonctionnelle :

```txt
User
ExcuseCategory
ExcuseContext
ExcuseTone
Excuse
```

Les autres entités doivent être ajoutées progressivement, une fois que la création et l’affichage d’excuses fonctionnent correctement.

## Suivi de mise en oeuvre (partage equipe)

Cette section est maintenue au fil des echanges pour aligner tous les collaborateurs.

### Decisions validees

- Le projet doit coller au maximum au sujet de cours pour viser une bonne note.
- Le Voter personnalise est obligatoire et prioritaire dans le flux metier.
- L'API du projet est implementee avec API Platform.
- Les permissions cibles de `ExcuseVoter` sont :
  - `EXCUSE_VIEW`
  - `EXCUSE_EDIT`
  - `EXCUSE_DELETE`
  - `EXCUSE_VALIDATE`
- Les routes `/validator` restent accessibles a `ROLE_VALIDATOR` et `ROLE_ADMIN`.
- Les endpoints API d'ecriture (`POST/PATCH`) exigent une authentification (`ROLE_USER`).
- L'auteur d'une excuse API est force cote serveur (utilisateur connecte), jamais par payload.
- `credibilityScore` est calcule automatiquement par l'application.
- L'endpoint random API ne renvoie que des excuses `validated`.

### Regles Voter retenues

- `ROLE_ADMIN` peut tout faire.
- `EXCUSE_EDIT` : auteur uniquement, statut `rejected`.
- `EXCUSE_DELETE` : auteur uniquement, si statut different de `validated`.
- `EXCUSE_VALIDATE` : `ROLE_VALIDATOR` uniquement, et excuse en `pending`.
- `EXCUSE_VIEW` : auteur, validateur et admin.
- Les routes `/validator` sont accessibles a `ROLE_VALIDATOR` et `ROLE_ADMIN`.
- Les excuses non validees ne sont pas exposees publiquement dans les vues front/API publiques.

### Etat d'avancement

- Etape 1 terminee : `ExcuseVoter` implemente dans `app/src/Security/Voter/ExcuseVoter.php`.
- Etape 2 terminee : regles `access_control` minimales ajoutees dans `app/config/packages/security.yaml`.
- Etape 3 terminee : methodes metier ajoutees dans `app/src/Repository/ExcuseRepository.php` (`findPendingExcuses`, `findUserExcuses`, `findValidatedExcuses`, `findByFilters`).
- Etape 4 terminee : `ExcuseController` ajoute avec pages Twig minimales (liste, detail, mes excuses, creation, edition, suppression) et integration du Voter.
- Etape 5 terminee : flux validator ajoute (`/validator/excuses`, actions accept/reject) avec creation d'entrees `ExcuseValidation`.
- Etape 6 terminee : API Platform installee et endpoints API v1 excuses disponibles (`/api/v1/excuses/{id}`, `/api/v1/excuses/random`).
- Etape 7 terminee : ressources API ajoutees directement sur les entites exposees (`Excuse`, `ExcuseCategory`, `ExcuseContext`, `ExcuseTone`, `Tag`).
- Etape 8 terminee : suppression de `nelmio/cors-bundle` (config, bundle, recettes Flex et lockfiles) avec verification `lint:container` OK.
- Etape 9 terminee : durcissement API write (`POST/PATCH` authentifies), auteur force via utilisateur connecte, blocage du changement d'auteur par payload.
- Etape 10 terminee : score `credibilityScore` calcule automatiquement via `CredibilityScoreService` et applique au create/update API.
- Etape 11 terminee : endpoint random limite aux excuses `validated`.
- Etape 12 terminee : ajustements front/profil pour distinguer "mes excuses" et liste publique des excuses validees.
- Etape 13 terminee : ajout de l'acces global a `/my-excuses` (onglet "Mes excuses") et affichage du statut de commentaires recus sur les excuses validees de l'auteur.
- Etape 14 terminee : suppression du statut `needs_changes` dans le flux metier et les fixtures (statuts conserves : `pending`, `validated`, `rejected`).
- Etape 15 terminee : ajout des filtres `/excuses` (categorie, contexte, ton) et du tri (recent, oldest, credibilite, titre).
- Etape 16 terminee : notifications ajoutees pour excuse soumise, nouveau commentaire et badge debloque (validation/rejet deja en place).

### Prochaines etapes prioritaires

- Ajouter les tests minimum obligatoires du sujet :
  - 1 test unitaire (`CredibilityScoreService`).
  - 1 test fonctionnel (droits API + flux validator).
- Verifier et completer la couverture de tests de securite metier (Voter, API write, routes validator).
- Mettre en place l'outillage qualite manquant : PHPUnit, PHPStan, pipeline CI (lint + analyse statique + tests).


