# SAE Paaxio

## Code Source du Projet SAE

### README — Organisation des Documents sur GitHub

Date de dernière mise à jour : **25 novembre 2025**  
Contact :

- [BOIX Yohan](https://github.com/Vendettass) — `yboix@iutbayonne.univ-pau.fr`
- [BOUSSOU MOUYABI Jarlin](https://github.com/clevaYann) — `jbmouyabi@iutbayonne.univ-pau.fr`
- [CECILIA URRA Christopher](https://github.com/Justasync) — `ccurra@iutbayonne.univ-pau.fr`
- [DIDELOT Tim](https://github.com/xFufly) — `contact@timdidelot.fr`
- [HOARAU Erwan](https://github.com/ErwanH7) — `ehoarau003@iutbayonne.univ-pau.fr`
- [RAMIREZ BATALLA Angel David](https://github.com/batallio) — `contact@angelbatalla.com`
- [TEISSEIRE Raphaël](https://github.com/rTeisseire) — `rteisseir001@iutbayonne.univ-pau.fr`

---

# 🧩 Projet Web — Installation et Configuration

Bienvenue dans **SAEPaaxio**, un projet collaboratif de développement web.  
Ce document explique comment installer et exécuter le projet en local.

---

## 🚀 Prérequis

Avant de commencer, assurez-vous d’avoir installé :

- **PHP (>=8.2)**
- **Composer (>=2.6.0)**
- **Node.js (>=14.18.0)** et **npm (>=9.0.0)**
- **MySQL (>=8.0)**

---

## 📂 Installation du projet

1. **Cloner le dépôt**

   ```bash
   git clone https://github.com/Justasync/SAEPaaxio.git
   cd SAEPaaxio
   ```

2. **Installer les dépendances PHP**

   ```bash
   composer install
   ```

3. **Installer les dépendances Node.js**

   ```bash
   npm install
   ```

4. **Créer le fichier de configuration `config.json`**

   Vous devez modifier le fichier `config.example.json` dans le dossier `/config/`.  
   Vous devez ensuite supprimer le example du nom pour avoir `config.json`.
   Modifier également le contenu en suivant ce qui est marqué dans votre nouveau fichier `config.json`.

5. **Créer et remplir la base de données**

   Vous pouvez exécuter les fichiers `.sql` de création et de population présents dans le dossier `/sql/` pour initialiser la base de données :

   ```bash
   mysql -u root -p paaxio_db < sql/create_database.sql
   mysql -u root -p paaxio_db < sql/populate_database.sql
   ```

   Remplacez `root` et `paaxio_db` par votre nom d’utilisateur et le nom de votre base si besoin.
