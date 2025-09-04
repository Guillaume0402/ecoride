
# `git-stratégie.md` – Stratégie Git pour EcoRide

## Objectif :
Assurer un développement clair, organisé et conforme aux **bonnes pratiques demandées pour l'ECF**.

---

## Structure des branches

| Nom de branche       | Description                                           |
|----------------------|-------------------------------------------------------|
| `main`               | Version **stable** prête à être livrée en production |
| `develop`            | Intégration de toutes les fonctionnalités validées   |
| `feat/xxx`           | Développement d'une **nouvelle fonctionnalité**      |
| `fix/xxx`            | **Correction de bug** non critique                   |
| `hotfix/xxx`         | **Correction urgente** directement en production     |
| `refactor/xxx`       | **Refactorisation** de code sans ajout de fonction   |
| `style/xxx`          | Modifications purement **visuelles / CSS**           |
| `doc/xxx`            | **Documentation**, README, changelog, etc.          |

---

## Workflow recommandé

### 1. Se baser sur `develop` à jour :
```bash
git checkout develop
git pull origin develop
```

### 2. Créer une branche dédiée et nommée proprement :
```bash
git checkout -b feat/nom-clair-fonctionnalité
```

*Exemple : `feat/signup-form`, `refactor/routing-system`*

---

### 3. Travailler avec des commits réguliers :
```bash
git add .
git commit -m "feat: création du formulaire d'inscription"
```

>  Utilise des messages explicites :  
> - `feat:` pour ajout  
> - `fix:` pour correction  
> - `refactor:`, `style:`, `doc:`, etc.

---

### 4. Pousser la branche si nécessaire :
```bash
git push origin feat/nom-clair
```

---

### 5. Fusionner dans `develop` après validation :
```bash
git checkout develop
git merge feat/nom-clair
```

---

### 6. Fusionner dans `main` quand la version est **testée et stable** :
```bash
git checkout main
git pull origin main
git merge develop
git push origin main
```

---

##  Règles de bonne conduite

- Ne jamais coder directement sur `main` ou `develop`
- 1 branche = 1 tâche (même petite)
- Rester cohérent avec les préfixes (`feat/`, `fix/`, etc.)
- Supprimer les branches locales une fois mergées

```bash
git branch -d feat/nom-clair
```

---

## Exemple complet

```bash
# Nouvelle fonctionnalité
git checkout develop
git pull origin develop
git checkout -b feat/modale-auth
# Code...
git add .
git commit -m "feat: modale de connexion et d'inscription"
git push origin feat/modale-auth

# Testé → merge dans develop
git checkout develop
git merge feat/modale-auth

# Tout validé → merge dans main
git checkout main
git merge develop
git push origin main
```

---

##  Suivi des branches Git

| Branche                     | Type       | Statut         | Description courte                               | Merge vers     |
|----------------------------|------------|----------------|---------------------------------------------------|----------------|
| main                       | stable     |  à conserver  | Version finale stable (prod)                       | -              |
| develop                    | intégration|  à conserver  | Version de développement principale                | main           |
| feat/creation-profil       | feature    |  à conserver  | Formulaire de création de profil                   | develop        |
| feat/creation-covoiturage  | feature    |  à conserver  | Formulaire de création de covoiturage              | develop        |
| feat/nav-modale-auth       | feature    |  à conserver  | Navigation + modale connexion/inscription          | develop        |
| feat/home-page             | feature    |  renommée     | Page d’accueil avec présentation et images         | develop        |
| feat/search-covoiturages   | feature    |  renommée     | Formulaire et affichage de recherche               | develop        |
| refactor/routing           | refactor   |  renommée     | Refonte du système de routage                      | develop        |
| feat/pages-header-update   | feature    |  en cours     | Nouvelles pages vierges + refonte du header        | develop        |

>  Branches supprimées : `sauvegarde-apres-stash`, `feature/router-refactor` (doublon inutile)

---

##  Convention de message de commit

### Format recommandé :
```
<type>: <courte description à l’infinitif>

<ligne vide>

[facultatif] Détail, explication, référence ticket ou lien Notion.
```

### Types acceptés :

| Type        | Description                                      |
|-------------|--------------------------------------------------|
| `feat`      |  Nouvelle fonctionnalité                         |
| `fix`       |  Correction de bug                               |
| `refactor`  |  Refactorisation sans changement fonctionnel     |
| `style`     |  Modifs visuelles uniquement (CSS, HTML…)        |
| `doc`       |  Modifications de docs ou README                 |
| `test`      |  Ajout ou modif de tests                         |
| `chore`     |  Maintenance ou tâches annexes (npm, config…)    |
| `hotfix`    |  Correction urgente en prod                      |

---

##  Activer un template automatique

1. Créer un fichier `.gitmessage.txt` :

```text
<type>: <courte description à l’infinitif>

# Ligne vide obligatoire

# Détaille ici ce que tu as fait, pourquoi.
# Laisse vide si pas nécessaire.

# Types valides :
# feat, fix, refactor, style, doc, test, chore, hotfix
```

2. Exécuter :

```bash
git config --global commit.template .gitmessage.txt
```

Dès lors, `git commit` ouvrira ce modèle automatiquement.
