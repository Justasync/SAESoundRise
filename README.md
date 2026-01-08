# SAE Paaxio

## Code Source du Projet SAE

### README — Organisation des Documents sur GitHub

Date de dernière mise à jour : **7 janvier 2026**  
Contact :

- [BOIX Yohan](https://github.com/Vendettass) — `yboix@iutbayonne.univ-pau.fr`
- [BOUSSOU MOUYABI Jarlin](https://github.com/clevaYann) — `jbmouyabi@iutbayonne.univ-pau.fr`
- [CECILIA URRA Christopher](https://github.com/Justasync) — `ccurra@iutbayonne.univ-pau.fr`
- [DIDELOT Tim](https://github.com/xFufly) — `contact@timdidelot.fr`
- [HOARAU Erwan](https://github.com/ErwanH7) — `ehoarau003@iutbayonne.univ-pau.fr`
- [RAMIREZ BATALLA Angel David](https://github.com/batallio) — `contact@angelbatalla.com`
- [TEISSEIRE Raphaël](https://github.com/rTeisseire) — `rteisseir001@iutbayonne.univ-pau.fr`

---

# Projet Web — Installation et Configuration

Bienvenue dans **SAEPaaxio**, un projet collaboratif de développement web.  
Ce document explique comment installer et exécuter le projet en local.

---

## Documentation

La documentation technique détaillée, générée automatiquement avec **Doxygen**, est disponible en ligne à l'adresse suivante : **[docs.paaxio.com](https://docs.paaxio.com){:target="\_blank"}**

La génération de cette documentation se fait à chaque mise à jour du code sur la branche `main`.  
Le système utilise un workflow GitHub Actions automatisé (voir `.github/workflows/docs.yml`) qui :

- Installe **Doxygen** et **Graphviz** sur un environnement Ubuntu
- Génère la documentation à partir des fichiers sources du dépôt
- Publie automatiquement le résultat dans le dossier `docs/html` sur GitHub Pages, rendant la documentation accessible publiquement.

---

## Prérequis

Avant de commencer, assurez-vous d’avoir installé :

- **PHP (>=8.2)**
- **Composer (>=2.6.0)**
- **Node.js (>=14.18.0)** et **npm (>=9.0.0)**
- **MySQL (>=8.0)**

---

## Installation du projet

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

6. **Télécharger les fichiers multimédias** _(Optionnel)_

   > ⚠️ **Cette étape est optionnelle.** Ces fichiers sont uniquement des données de démonstration/test et ne sont pas nécessaires pour le fonctionnement du projet.

   Les fichiers multimédias (images et audio) ne sont pas inclus dans le dépôt Git. Si vous souhaitez utiliser les données de test, vous pouvez les télécharger depuis les liens suivants :

   - **Photos de profil des utilisateurs** :  
     📥 [https://paaxio.com/downloads/profile_pictures.zip](https://paaxio.com/downloads/profile_pictures.zip)  
     → À extraire dans `/assets/images/profile_pictures/`

   - **Images des albums** :  
     📥 [https://paaxio.com/downloads/albums.zip](https://paaxio.com/downloads/albums.zip)  
     → À extraire dans `/assets/images/albums/`

   - **Fichiers audio (musiques)** :  
     📥 [https://paaxio.com/downloads/audio.zip](https://paaxio.com/downloads/audio.zip)  
     → À extraire dans `/assets/audio/`

   **Sources des fichiers de démonstration :**

   - Photos de profil : générées par [This Person Does Not Exist](https://thispersondoesnotexist.com)
   - Images des albums : générées par [Lorem Picsum](https://picsum.photos/500)
   - Musiques : provenant de [Pixabay Music](https://pixabay.com/music/) (libres de droits)

   **Exemple de commandes pour télécharger et extraire les fichiers :**

   ```bash
   # Photos de profil
   wget https://paaxio.com/downloads/profile_pictures.zip
   unzip profile_pictures.zip -d assets/images/profile_pictures/

   # Images des albums
   wget https://paaxio.com/downloads/albums.zip
   unzip albums.zip -d assets/images/albums/

   # Fichiers audio
   wget https://paaxio.com/downloads/audio.zip
   unzip audio.zip -d assets/audio/
   ```
