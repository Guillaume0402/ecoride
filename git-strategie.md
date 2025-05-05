# 📁 `git-stratégie.md` – Stratégie Git pour EcoRide

## 🧠 Objectif :
Assurer un développement clair, organisé et conforme aux **bonnes pratiques demandées pour l'ECF**.

---

## 🚀 Structure des branches

- `main` → version **stable** (production/démo)
- `develop` → version **en cours de validation/test**
- `feat/xxx` → développement d'une **nouvelle fonctionnalité**
- `fix/xxx` → correction de **bug**
- `hotfix/xxx` → correction urgente en prod
- `doc/xxx` → documentation ou README

---

## 📌 À FAIRE pour chaque fonctionnalité

1. **Toujours se baser sur `develop` à jour :**
   ```bash
   git checkout develop
   git pull origin develop
   ```

2. **Créer une nouvelle branche claire :**
   ```bash
   git checkout -b feat/nom-fonctionnalité
   ```

3. **Coder et commit régulièrement :**
   ```bash
   git add .
   git commit -m "feat: description claire"
   ```

4. **Pousser la branche (si remote) :**
   ```bash
   git push origin feat/nom-fonctionnalité
   ```

5. **Une fois validée, merger dans `develop` :**
   ```bash
   git checkout develop
   git merge feat/nom-fonctionnalité
   ```

6. **Et quand tout est testé : merger dans `main`**

---

## 🧼 Bonus : pour ne pas oublier

- ❌ Ne code jamais directement sur `main` ou `develop`
- ✅ Crée une branche pour **chaque tâche** (même petite !)
- ✍️ Utilise des messages de commit **clairs et explicites**
