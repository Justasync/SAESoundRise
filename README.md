# SAE Paaxio

## Code Source du Projet SAE

### README — Organisation des Documents sur GitHub

Date de dernière mise à jour : **5 novembre 2025**  
Contact :

- [BOIX Yohan](https://github.com/) — `yboix@iutbayonne.univ-pau.fr`
- [BOUSSOU MOUYABI Jarlin](https://github.com/clevaYann) — `jbmouyabi@iutbayonne.univ-pau.fr`
- [CECILIA URRA Christopher](https://github.com/Justasync) — `ccurra@iutbayonne.univ-pau.fr`
- [DIDELOT Tim](https://github.com/xFufly) — `tdidelot@iutbayonne.univ-pau.fr`
- [HOARAU Erwan](https://github.com/ErwanH7) — `ehoarau003@iutbayonne.univ-pau.fr`
- [RAMIREZ BATALLA Angel David](https://github.com/batallio) — `adrbatalla@iutbayonne.univ-pau.fr`
- [TEISSEIRE Raphaël](https://github.com/rTeisseire) — `rteisseir001@iutbayonne.univ-pau.fr`

---

# 🧩 Projet Web — Installation et Configuration

Bienvenue dans **SAEPaaxio**, un projet collaboratif de développement web.  
Ce document explique comment installer et exécuter le projet en local.

---

## 🚀 Prérequis

Avant de commencer, assurez-vous d’avoir installé :

- **PHP**
- **Composer**
- **Node.js** et **npm**
- **MySQL**

---

## 📂 Installation du projet

1. **Cloner le dépôt**

   ```bash
   git clone https://github.com/Justasync/SAESoundRise.git
   cd SAESoundRise
   ```

2. **Installer les dépendances PHP**

   ```bash
   composer install
   ```

3. **Installer les dépendances Node.js**

   ```bash
   npm install
   ```

4. **Créer le fichier de configuration `constantes.php`**

   Vous devez créer un fichier `constantes.php` dans le dossier `/config/`.  
   Exemple de contenu pour `/config/constantes.php` :

   ```php
   <?php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'paaxio_db');
   define('DB_USER', 'root');
   define('DB_PASS', '!Paaxio123@');
   ```

5. **Créer et remplir la base de données**

   Vous pouvez exécuter les fichiers `.sql` de création et de population présents dans le dossier `/sql/` pour initialiser la base de données :

   ```bash
   mysql -u root -p paaxio_db < sql/create_database.sql
   mysql -u root -p paaxio_db < sql/populate_database.sql
   ```

   Remplacez `root` et `paaxio_db` par votre nom d’utilisateur et le nom de votre base si besoin.
