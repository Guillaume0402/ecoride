# Délivrabilité e-mail (SendGrid) — SPF/DKIM

Objectif: réduire les spams et améliorer la réputation en authentifiant votre domaine d’envoi pour les e-mails d’EcoRide.

## 1) Choisir l’expéditeur et le domaine

-   Utilisez un domaine que vous contrôlez, ex: `ecoride.example`.
-   Adresse d’envoi recommandée: `no-reply@ecoride.example` (à définir en `MAIL_FROM`).
-   En environnement PaaS (Heroku), privilégiez un relais SMTP (SendGrid).

## 2) Configurer SendGrid

1. Créez un compte SendGrid et ajoutez l’add-on sur Heroku si ce n’est pas déjà fait.
2. Dans SendGrid:
    - Settings → Sender Authentication → Domain Authentication → Get Started
    - Sélectionnez votre DNS provider, puis votre domaine (ex: `ecoride.example`).
    - Laissez l’option “Use automated security” (CNAME) activée si possible.
    - SendGrid fournit 3 à 5 enregistrements CNAME à créer dans votre DNS (DKIM/Return-Path).
3. Créez les enregistrements dans votre DNS (chez votre registrar/ hébergeur).
4. Revenez dans SendGrid et cliquez sur “Verify”. Attendez la propagation DNS (quelques minutes à 24h).

Note: Domain Authentication configure DKIM et un sous-domaine Return-Path (CNAME). SPF est couvert implicitement via ce Return-Path (alignment avec DMARC possible). Vous pouvez également publier un SPF explicite incluant SendGrid.

## 3) SPF (optionnel, recommandé)

Publiez/complétez l’enregistrement TXT SPF de votre domaine racine (ou sous-domaine d’envoi):

```
v=spf1 include:sendgrid.net ~all
```

-   Si vous avez déjà un SPF, ne créez pas un second enregistrement TXT SPF. Fusionnez plutôt:
    -   Exemple: `v=spf1 include:spf.protection.outlook.com include:sendgrid.net ~all`
-   Utilisez `~all` (softfail) ou `-all` (fail strict) selon votre politique.

## 4) DKIM (via SendGrid)

La Domain Authentication génère des CNAME qui pointent vers les clés DKIM hébergées chez SendGrid. Une fois vérifiés, DKIM signera vos e-mails.

-   Vérifiez la présence de l’en-tête `DKIM-Signature` dans un e-mail livré.
-   Les providers (Gmail, Outlook) valorisent fortement DKIM.

## 5) DMARC (bonus)

Pour contrôler la politique globale d’authentification (SPF/DKIM) et recevoir des rapports:

Exemple minimal en TXT sur `_dmarc.ecoride.example`:

```
v=DMARC1; p=none; rua=mailto:dmarc-reports@ecoride.example; sp=none; adkim=s; aspf=s
```

-   `p=none` pour observer sans impact; passez à `quarantine` puis `reject` quand tout est stable.
-   `adkim=s` et `aspf=s` imposent un alignement strict (sous-domaines non autorisés par défaut).

## 6) Variables d’environnement côté app

Dans Heroku ou votre env prod, définissez:

-   `MAIL_FROM=no-reply@ecoride.example`
-   `MAIL_FROM_NAME=EcoRide`
-   `SMTP_HOST=smtp.sendgrid.net`
-   `SMTP_PORT=587`
-   `SMTP_USER=apikey`
-   `SMTP_PASS=<votre_clef_API_SendGrid>`
-   `SMTP_SECURE=tls`
-   Optionnels: `MAIL_REPLY_TO`, `LIST_UNSUBSCRIBE_URL`, `LIST_UNSUBSCRIBE_MAILTO`, `LIST_UNSUBSCRIBE_POST=1`

Assurez-vous que `SITE_URL` pointe sur votre domaine prod avec slash final (ex: `https://app.ecoride.example/`).

## 7) Vérifier la délivrabilité

-   Envoyez un e-mail test depuis l’app:
    -   `php scripts/send_test_email.php destinataire@example.com`
-   Inspectez les en-têtes reçus:
    -   `Authentication-Results` (SPF, DKIM, DMARC)
    -   `DKIM-Signature`
    -   `Return-Path`
-   Sur SendGrid: Activity feed (livraisons/bounces/spam reports).

## 8) Dépannage courant

-   CNAME non propagés: attendez; vérifiez via `dig`/`nslookup` que les CNAME pointent bien vers SendGrid.
-   SPF multiples: ne gardez qu’un seul enregistrement SPF, fusionnez les `include:`.
-   From non aligné: utilisez la même racine de domaine entre `MAIL_FROM` et la Domain Authentication SendGrid.
-   Spam initial: marquez « Non-spam », authentifiez SPF/DKIM, et envoyez quelques e-mails sains.

## 9) Check-list

-   [ ] Domain Authentication validée dans SendGrid (DKIM OK)
-   [ ] SPF publié et non dupliqué (un seul TXT SPF)
-   [ ] DMARC en mode `p=none` (puis renforcer progressivement)
-   [ ] Variables d’env mises à jour (`MAIL_FROM` sur domaine authentifié)
-   [ ] Test réel reçu en boîte de réception (pas spam)
