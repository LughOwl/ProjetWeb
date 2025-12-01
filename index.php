<?php
session_start();
include 'Donnees.inc.php';

// Pour charger les utilisateurs lors de la connexion
function chargerUtilisateurs() {
    if (!file_exists("users.json")) {
        file_put_contents("users.json", "[]");
    }
    $json = file_get_contents("users.json");
    return json_decode($json, true);
}

// Pour trouver un utilisateur avec son login et la liste des utilisateurs
function recupererIdUtilisateur($login, $users) {
    foreach ($users as $index => $user) {
        if ($user['login'] === $login) {
            return $index;
        }
    }
    return -1;
}

// Pour sauvegarder un utilisateur dans le fichier json
function sauvegarderUtilisateurs($users) {
    file_put_contents("users.json", json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Pour modifier un utilisateur contenu dans le fichier json avec son login et la liste des utilisateurs qui vont être modifés donc par référence &
function modifierUtilisateur($login, &$users) {
    foreach ($users as &$user) {
        if ($user['login'] === $login) {
            if(isset($_SESSION["user"]["nom"])) 
                $user['nom'] = $_SESSION["user"]["nom"]; 
            else 
                $user['nom'] = "";

            if(isset($_SESSION["user"]["prenom"])) 
                $user['prenom'] = $_SESSION["user"]["prenom"]; 
            else 
                $user['prenom'] = "";

            if(isset($_SESSION["user"]["dateNaissance"])) 
                $user['dateNaissance'] = $_SESSION["user"]["dateNaissance"]; 
            else 
                $user['dateNaissance'] = "";

            if(isset($_SESSION["user"]["sexe"])) 
                $user['sexe'] = $_SESSION["user"]["sexe"]; 
            else 
                $user['sexe'] = "";
            break;
        }
    }
    sauvegarderUtilisateurs($users);
}

// Si on clique sur le bouton déconnexion, on détruit la session
if (isset($_POST['deconnexion'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Si on clique sur le bouton recherche, on fait une recherche avec le trxte contenu dans l'input texteRecherche
if (isset($_GET['submit-recherche'])) {
    header("Location: index.php?page=recherche&texteRecherche=" . $_GET['texteRecherche']);
    exit();
}

// Lorsqu'on clique sur Connexion, on vérifie d'abord que les deux input ne sont pas vide 
// pour ensuite faire les vérification pour tenter de connecter l'utilisateur
if (!empty($_POST['login-nav']) && !empty($_POST['motDePasse-nav'])) {
    $login = trim($_POST['login-nav']);
    $password_soumis = $_POST['motDePasse-nav'];

    $utilisateurs = chargerUtilisateurs();
    $utilisateur_trouve = false;

    if (!empty($utilisateurs)) {
        foreach ($utilisateurs as $user) {
            if ($user['login'] === $login) {
                if (password_verify($password_soumis, $user['password'])) {
                    $idUtilisateur = recupererIdUtilisateur($login, $utilisateurs);
                    $_SESSION["user"] = $utilisateurs[$idUtilisateur];
                    header("Location: index.php");
                    exit();
                }
            }
        }
    }
    $erreur_connexion = "Identifiants incorrects.";
}

// On initialise une liste de page pour ensuite mettre à jour la variable 
// $pageCourante en récupérant "page=..." dans l'url,
// si "page" est mal initialisé, on met la page courante à 'navigation'
$listePages = ['navigation', 'favoris', 'inscription', 'profil', 'recherche', 'recette'];

if (isset($_GET['page']) && in_array($_GET['page'], $listePages)) {
    $pageCourante = $_GET['page'];
} else {
    $pageCourante = 'navigation';
}

// Si le tableau de recettes favorites n'est pas initialisé, on le fait
if (!isset($_SESSION["user"]["recettesFavoris"])) {
    $_SESSION["user"]["recettesFavoris"] = [];
}

// Met à jour le chemin et la variable $categorieActuelle pour naviguer dans la page Navigation
if (isset($_GET['chemin'])) {
    $cheminCourant = $_GET['chemin'];
    $hierarchieChemin = explode('_', $cheminCourant);
    $categorieActuelle = end($hierarchieChemin);
} else {
    $cheminCourant = 'Aliment';
    $categorieActuelle = 'Aliment';
}

// Pour gérer le clic du bouton coeur dans différentes conditions 
// avec "est_favoris=..." dans l'url
if (isset($_GET['est_favori'])) {
    $idRecette = $_GET['est_favori'];
    
    // Si la recette est dans le tableau des favoris on l'enlève, sinon on l'ajoute
    if (in_array($idRecette, $_SESSION["user"]["recettesFavoris"])) {
        $indice = array_search($idRecette, $_SESSION["user"]["recettesFavoris"]);
        unset($_SESSION["user"]["recettesFavoris"][$indice]);
        $_SESSION["user"]["recettesFavoris"] = array_values($_SESSION["user"]["recettesFavoris"]);
    } else {
        $_SESSION["user"]["recettesFavoris"][] = $idRecette;
    }
    
    // Si on est connecté, on met à jour les favoris et on sauveafrde dans le json
    if (isset($_SESSION["user"]["login"])) {
        $utilisateurs = chargerUtilisateurs();
        $login_actuel = $_SESSION["user"]["login"];
        
        foreach ($utilisateurs as $cle => $user) {
            if ($user['login'] === $login_actuel) {
                $utilisateurs[$cle]['recettesFavoris'] = $_SESSION["user"]["recettesFavoris"];
                sauvegarderUtilisateurs($utilisateurs);
                break;
            }
        }
    }
    
    // Permet de gérer le clic du bouton coeur si on est soit dans navigation, soit dans recherche, soit dans recette
    $urlRedirection = "index.php?page=" . $pageCourante;
    if ($pageCourante === 'navigation') {
        $ancre = "#recette-" . $idRecette;
        $urlRedirection .= "&chemin=" . urlencode($cheminCourant) . $ancre;
    }
    if ($pageCourante === 'recherche') {
        $ancre = "#recette-" . $idRecette;
        $urlRedirection .= "&texteRecherche=" . urlencode($_GET['texteRecherche']) . $ancre;
    }
    if ($pageCourante === 'recette') {
        $urlRedirection .= "&id=" . urlencode($idRecette);
    }

    header("Location: " . $urlRedirection);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cocktails</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php?page=navigation&categorie=Aliment">
            <div class="nav-bouton">
                Navigation
            </div>
        </a>
        <a href="index.php?page=favoris">
            <div class="nav-bouton">
                Recettes 
                <img src="Photos/Coeur_plein.png" width="15" alt="image coeur">
            </div>
        </a>
        <div class="zone-recherche">
            <form action="index.php?page=recherche" method="GET">
                <label class="recherche-label" for="recherche-input">
                    Recherche :
                </label>
                <input type="search" id="recherche-input" name="texteRecherche" class="recherche-input" value="<?php
                        if (isset($_GET["texteRecherche"])) {
                            echo htmlspecialchars($_GET["texteRecherche"]);
                        } else {
                            echo '';
                        }
                    ?>">
                <button type="submit" name="submit-recherche" class="recherche-bouton">
                    <img src="Photos/Loupe.png" alt="image loupe">
                </button>
            </form>
        </div>
        <div class="zone-connexion">
            <?php if (isset($_SESSION["user"]["login"])){ ?><div class="texte-login"><?php echo htmlspecialchars($_SESSION["user"]["login"]); ?></div>
            <a href="index.php?page=profil">
                <div class="connexion-bouton">
                    Profil
            </div>
            </a>
            <form method="post">
                <button type="submit" name="deconnexion" class="connexion-bouton">
                    Se déconnecter
                </button>
            </form>
        <?php } else { ?><form method="post" action="index.php" style="display: inline;"> 
                <label for="login-nav" class="connexion-label">
                    Login
                </label>
                <input type="text" id="login-nav" name="login-nav" class="connexion-input" required >
                <label for="motDePasse-nav" class="connexion-label">
                    Mot de passe
                </label>
                <input type="password" id="motDePasse-nav" name="motDePasse-nav" class="connexion-input" required >
                <button type="submit" class="connexion-bouton">
                    Connexion
                </button>
            </form>
            <a href="index.php?page=inscription">
                <div class="connexion-bouton">
                    S'inscrire
                </div>
            </a>
        <?php } ?></div>
    </nav>
    <?php 
    if (isset($erreur_connexion)){ 
    ?><div class="erreur"><?php echo $erreur_connexion; ?></div>
    <?php } 
    ?><div class="structure">
        <?php
        // affiche la page correspondant à la variable $pageCourante 
        // mis à jour avec des "page=..." dans l'url
        switch ($pageCourante) {
            case 'navigation':
                include 'navigation.php';
                break;
            case 'favoris':
                include 'favoris.php';
                break;
            case 'inscription':
                include 'inscription.php';
                break;
            case 'profil':
                include 'profil.php';
                break;
            case 'recherche':
                include 'recherche.php';
                break;
            case 'recette':
                include 'recette.php';
                break;
            default:
                echo 'Page non trouvée.';
                break;
        }
        ?>
    
    </div>
</body>
</html>