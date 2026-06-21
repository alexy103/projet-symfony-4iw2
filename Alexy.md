# alexy.md — Mission d’Alexy sur le projet

## Rôle d’Alexy

Alexy s’occupe principalement de la partie **métier / back-end** du projet Symfony.

Objectif : rendre fonctionnel le cœur de l’application autour des excuses, de leur création, de leur statut et de leur validation par un utilisateur ayant `ROLE_VALIDATOR`.

Ne pas partir sur des fonctionnalités secondaires tant que le flux principal n’est pas stable.

## Contexte du projet

Le projet est une application Symfony 8.1 + Twig de **générateur d’excuses**.

Un utilisateur peut proposer une excuse. Cette excuse peut ensuite être validée, refusée ou renvoyée pour modification par un utilisateur ayant `ROLE_VALIDATOR`.

## Mission principale

Finaliser et sécuriser ce flux métier :

```txt
Un User crée une excuse
→ l’excuse est en brouillon ou en attente
→ un ROLE_VALIDATOR peut l’accepter ou la refuser
→ les droits sont contrôlés proprement
```

## État actuel

Entités principales déjà créées ou en cours :

```txt
User
Excuse
ClassicExcuse
EmergencyExcuse
ProfessionalExcuse
ExcuseCategory
ExcuseContext
ExcuseTone
```

Héritage Doctrine prévu :

```txt
Excuse
├── ClassicExcuse
├── EmergencyExcuse
└── ProfessionalExcuse
```

Important : les entités enfants ne doivent pas redéclarer leur propre `id`. L’`id` vient de l’entité parente `Excuse`.

## À faire par Alexy

### 1. Vérifier les entités existantes

Vérifier que :
- `Excuse` est bien l’entité parente ;
- `ClassicExcuse`, `EmergencyExcuse` et `ProfessionalExcuse` étendent bien `Excuse` ;
- les enfants ne redéclarent pas `id` ;
- les relations vers `User`, `ExcuseCategory`, `ExcuseContext` et `ExcuseTone` fonctionnent ;
- les migrations passent proprement.

### 2. Finaliser les fixtures Hautelook Alice

Les fixtures doivent contenir :
- un admin ;
- un validateur ;
- deux utilisateurs classiques ;
- plusieurs catégories ;
- plusieurs contextes ;
- plusieurs tons ;
- plusieurs excuses de chaque type.

Comptes recommandés :

```txt
admin@excusify.test
validator@excusify.test
alex@excusify.test
sam@excusify.test
```

Mot de passe prévu :

```txt
password
```

Attention : dans la classe `User`, le champ du mot de passe est `passwordHash`, pas `password`.

### 3. Créer le CRUD des excuses

Créer les pages minimales :
- liste des excuses ;
- détail d’une excuse ;
- création d’une excuse ;
- modification d’une excuse ;
- suppression si autorisée ;
- liste de “mes excuses”.

Contrôleur recommandé :

```txt
ExcuseController
```

Routes possibles :

```txt
/excuses
/excuses/new
/excuses/{id}
/excuses/{id}/edit
/excuses/{id}/delete
/my-excuses
```

### 4. Gérer la création selon le type d’excuse

Types d’excuses :

```txt
ClassicExcuse
EmergencyExcuse
ProfessionalExcuse
```

Champs spécifiques :

```txt
ClassicExcuse
- estimatedDelay
- isReusable

EmergencyExcuse
- emergencyLevel
- requiresProof

ProfessionalExcuse
- targetRecipient
- professionalTone
```

Pour aller vite, faire une page de création par type :

```txt
/excuses/new/classic
/excuses/new/emergency
/excuses/new/professional
```

### 5. Définir les statuts d’excuse

Statuts recommandés :

```txt
pending
validated
rejected
```

Signification :
- `pending` : soumis à validation ;
- `validated` : accepté par un validateur ;
- `rejected` : refusé par un validateur.

### 6. Créer le Voter sur Excuse

Créer :

```txt
ExcuseVoter
```

Permissions possibles :

```txt
EXCUSE_VIEW
EXCUSE_EDIT
EXCUSE_DELETE
EXCUSE_VALIDATE
```

Règles :
- l’auteur peut modifier son excuse si elle est en `rejected` ;
- l’auteur peut supprimer son excuse si elle n’est pas `validated` ;
- un `ROLE_VALIDATOR` peut valider/refuser une excuse en `pending` ;
- un `ROLE_ADMIN` peut tout faire.

### 7. Créer le système de validation

Créer l’entité :

```txt
ExcuseValidation
```

Champs recommandés :
- `status`
- `comment`
- `validatedAt`

Relations :
- `ExcuseValidation ManyToOne Excuse`
- `ExcuseValidation ManyToOne User`

Ici, le `User` est le validateur.

Statuts possibles :

```txt
accepted
rejected
needs_changes
```

Routes possibles :

```txt
/validator/excuses
/validator/excuses/{id}/accept
/validator/excuses/{id}/reject
```

### 8. Ajouter des méthodes Repository utiles

Dans `ExcuseRepository`, prévoir :

```txt
findPendingExcuses()
findUserExcuses(User $user)
findValidatedExcuses()
findByFilters(...)
```

Objectifs :
- éviter le N+1 ;
- préparer les filtres ;
- faciliter les pages Twig.

### 9. Préparer le calcul de crédibilité

Le champ `credibilityScore` ne doit pas être rempli par l’utilisateur.

Il doit être calculé automatiquement par l’application.

Service possible :

```txt
CredibilityScoreService
```

Règle simple :
- score de base : 70 ;
- ton absurde : -30 ;
- ton dramatique : -10 ;
- contexte cohérent avec catégorie : +10 ;
- contenu trop court : -15 ;
- professional excuse : +10 ;
- emergency avec preuve requise : -10.

Le score doit rester entre 0 et 100.

### 10. Préparer les tests de base

Tests recommandés :
- un utilisateur connecté peut créer une excuse ;
- un utilisateur ne peut pas modifier l’excuse d’un autre ;
- un validateur peut accéder aux excuses en attente ;
- un utilisateur sans `ROLE_VALIDATOR` ne peut pas valider une excuse.

## Ce qu’Alexy ne fait pas en priorité

Ne pas prioriser pour l’instant :
- badges ;
- notifications ;
- commentaires ;
- notes ;
- tags ;
- interface admin complète ;
- API OpenAI ;
- Mailer ;
- design avancé.

Ces parties pourront être faites par l’autre personne ou plus tard.

## Livrable attendu

À la fin de cette partie, on doit pouvoir :

```txt
1. Charger les fixtures
2. Se connecter avec un utilisateur
3. Créer une excuse
4. Voir ses excuses
5. Modifier une excuse autorisée
6. Se connecter comme validateur
7. Voir les excuses en attente
8. Accepter ou refuser une excuse
9. Vérifier que les droits sont protégés par le Voter
```

## Commandes utiles

Charger les fixtures :

```bash
php bin/console hautelook:fixtures:load
```

Créer une migration :

```bash
php bin/console make:migration
```

Appliquer les migrations :

```bash
php bin/console doctrine:migrations:migrate
```

Repartir de zéro si nécessaire :

```bash
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console hautelook:fixtures:load
```

## Règles importantes

Ne pas renommer les propriétés existantes sans nécessité.

Ne pas modifier ces noms sans décision commune :

```txt
Excuse
ClassicExcuse
EmergencyExcuse
ProfessionalExcuse
ExcuseCategory
ExcuseContext
ExcuseTone
ExcuseValidation
User
```

Ne pas générer des migrations en parallèle avec l’autre personne sans coordination.

Ne pas ajouter trop de fonctionnalités tant que le flux de création et validation d’excuse n’est pas terminé.

## Journal de travail (Alexy + copilote)

Ce journal est mis a jour au fil de l'eau pour suivre exactement ce qui est decide et realise.

### Decision du moment

- Priorite immediate : coller au sujet de cours pour maximiser la note.
- On demarre par l'etape 1 : `ExcuseVoter`.
- Les permissions retenues :
  - `EXCUSE_VIEW`
  - `EXCUSE_EDIT`
  - `EXCUSE_DELETE`
  - `EXCUSE_VALIDATE`
- Sur les routes validator, l'admin doit avoir les memes possibilites que le validator.
- Les tests sont reportes a la fin du flux principal (rappel obligatoire avant cloture).
- L'API sera implementee avec API Platform (choix valide car vu en cours).

### Avancement

- Etape 1 terminee : `app/src/Security/Voter/ExcuseVoter.php` implemente.
- Etape 2 terminee : securisation minimale des routes dans `app/config/packages/security.yaml`.
- Etape 3 terminee : methodes repository ajoutees dans `app/src/Repository/ExcuseRepository.php`.
- Etape 4 terminee : `app/src/Controller/ExcuseController.php` + templates Twig du flux excuses.
- Etape 5 terminee : `app/src/Controller/ValidatorExcuseController.php` + page Twig validator (pending, accept/reject, traçabilite `ExcuseValidation`).
- Etape 6 terminee : API Platform installee + endpoints `GET /api/v1/excuses/{id}` et `GET /api/v1/excuses/random`.
- Etape 7 terminee : `ApiResource` ajoute sur les entites exposees (`Excuse`, `ExcuseCategory`, `ExcuseContext`, `ExcuseTone`, `Tag`) en lecture.
- Etape 8 terminee : suppression complete de Nelmio CORS (`.env`, `bundles.php`, recette Flex, lockfiles) et verification Symfony OK.

### Prochaine etape

- Ajouter des tests fonctionnels de securite :
  - un user non validator ne peut pas valider
  - un validator peut valider uniquement les excuses `pending`
  - controle des acces sur les routes validator.
- Ajouter ensuite le connecteur meteo via HttpClient et brancher une generation d'excuse simple basee meteo.

## Ce qu'il manque encore (checklist finale)

### Bloc prioritaire fin de flux principal

- [ ] Tests fonctionnels de securite (a faire en fin de flux principal, rappel obligatoire).
- [ ] Verification complete du flux : creation excuse -> pending -> validation/rejet -> droits Voter.

### API (strategie retenue)

- [x] Mettre en place API Platform (choix valide car vu en cours).
- [ ] Exposer les endpoints API minimaux du sujet :
  - [x] `GET /api/v1/excuses/random`
  - [x] `GET /api/v1/excuses/{id}`
- [ ] Configurer les groupes Serializer (`excuse:read`, `excuse:write`, `user:read`, `tag:read`).

### API externe meteo (idee retenue)

- [ ] Ajouter un service de consommation API meteo (HttpClient Symfony).
- [ ] Ajouter une route API/metier simple basee meteo (ex: generation d'excuse selon la ville/la meteo).
- [ ] Gerer proprement les erreurs reseau/timeouts et fallback message.

### Notifications et mails

- [ ] Brancher Mailer/Notifier sur les evenements cles (soumission, validation, rejet, badge).

### Qualite et outillage

- [ ] Ajouter PHPUnit (le package n'est pas encore present dans `composer.json`).
- [ ] Creer au moins 1 test unitaire + 1 test fonctionnel conformes au sujet.
- [ ] Ajouter PHPStan (config + niveau cible) et corriger les erreurs critiques.
- [ ] Ajouter pipeline CI (lint container/twig/yaml + phpstan + tests).

### Livrables de cloture

- [ ] Verifier la coherence des fixtures avec le flux final.
- [ ] Verifier les droits ROLE_USER / ROLE_VALIDATOR / ROLE_ADMIN sur toutes les routes critiques.
- [ ] Faire une passe de recette finale avant merge (routes, formulaires, suppression, validation).

