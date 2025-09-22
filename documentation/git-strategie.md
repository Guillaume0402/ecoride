
# `git-stratégie.md` – Stratégie Git pour EcoRide

## Objectif :
Assurer un développement clair, organisé et conforme aux **bonnes pratiques demandées pour l'ECF**.

---

## Structure des branches

| Nom de branche       | Description                                           |
|----------------------|-------------------------------------------------------|
| `main`               | Version **stable** prête à être livrée en production |
| `dev`                | Intégration des fonctionnalités validées             |
| `feat/xxx` ou `feature/xxx` | Développement d'une **nouvelle fonctionnalité**      |
| `fix/xxx`            | **Correction de bug** non critique                   |
| `hotfix/xxx`         | **Correction urgente** directement en production     |
| `refactor/xxx`       | **Refactorisation** de code sans ajout de fonction   |
| `style/xxx`          | Modifications purement **visuelles / CSS**           |
| `docs/xxx`           | **Documentation**, README, changelog, etc.           |
| `chore/xxx`          | Tâches techniques/outillage (config, CI, cleanup)    |

---

## Workflow recommandé

### 1. Se baser sur `dev` à jour :
```bash
git checkout dev
git pull origin dev
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

### 5. Fusionner dans `dev` après validation :
```bash
git checkout dev
git merge feat/nom-clair
```

---

### 6. Fusionner dans `main` quand la version est **testée et stable** :
```bash
git checkout main
git pull origin main
git merge dev
git push origin main
```

---

##  Règles de bonne conduite

- Ne jamais coder directement sur `main` ou `dev`
- 1 branche = 1 tâche (même petite)
- Rester cohérent avec les préfixes (`feat/`, `fix/`, etc.)
- Supprimer les branches locales et distantes une fois mergées

```bash
git branch -d feat/nom-clair
git push origin --delete feat/nom-clair
```

---

## Exemple complet

```bash
# Nouvelle fonctionnalité (ex: modale d'authentification)
git checkout dev
git pull origin dev
git checkout -b feature/modal-auth
# Code...
git add .
git commit -m "feat: modale de connexion et d'inscription"
git push origin feature/modal-auth

# Testé → merge dans dev
git checkout dev
git merge feature/modal-auth

# Tout validé → merge dans main (depuis dev)
git checkout main
git merge dev
git push origin main
```

---

##  Suivi des branches Git (échantillon conservé)

| Branche                 | Type     | Statut       | Description courte                                 | Merge vers |
|-------------------------|----------|--------------|-----------------------------------------------------|------------|
| main                    | stable   | à conserver  | Version finale stable (prod)                        | -          |
| dev                     | intégration | à conserver | Branche d’intégration principale                    | main       |
| feature/modal-auth      | feature  | à conserver  | Modale de connexion / inscription                   | dev        |
| feat/csrf-login         | feature  | à conserver  | Protection CSRF lors de la connexion                | dev        |
| fix/login-error-message | fix      | à conserver  | Amélioration du message d’erreur login              | dev        |
| refactor/javascript     | refactor | à conserver  | Refactorisation JS côté front                       | dev        |
| chore/error-handler     | chore    | à conserver  | Gestionnaire d’erreurs (technique)                  | dev        |
| docs/readme-ecf         | docs     | à conserver  | Documentation spécifique ECF                        | dev        |

>  Branches nettoyées : anciennes branches non essentielles (features/fixes/refactors obsolètes) supprimées localement et à distance pour ne garder qu’un échantillon représentatif.

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
| `docs`      |  Modifications de docs ou README                 |
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
# feat, fix, refactor, style, docs, test, chore, hotfix
```

2. Exécuter :

```bash
git config --global commit.template .gitmessage.txt
```

Dès lors, `git commit` ouvrira ce modèle automatiquement.
