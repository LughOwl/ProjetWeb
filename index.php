<?php
    session_start();
    include 'Donnees.inc.php';

    function chargerUtilisateurs() {
        if (!file_exists("users.json")) {
            file_put_contents("users.json", "[]");
        }
        $json = file_get_contents("users.json");
        return json_decode($json, true);
    }

    function sauvegarderUtilisateurs($users) {
        file_put_contents("users.json", json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    function supprimerUtilisateur($login) {
        $users = chargerUtilisateurs();
        foreach ($users as $key => $user) {
            if ($user['login'] === $login) {
                unset($users[$key]);
                break;
            }
        }
        sauvegarderUtilisateurs(array_values($users));
    }
    

    $listePages = ['navigation', 'favoris', 'inscription', 'profil', 'recherche', 'recette'];
    
    // --- GESTION DE LA DÉCONNEXION ---
    if (isset($_POST['deconnexion'])) {
        session_destroy();   // détruit la session
        header("Location: index.php");
        exit();
    }

   // --- GESTION DE LA CONNEXION ---
    if (isset($_POST['login-nav']) && isset($_POST['motDePasse-nav'])) {
        $login = trim($_POST['login-nav']);
        $password_soumis = $_POST['motDePasse-nav'];

        $utilisateurs = chargerUtilisateurs();
        $utilisateur_trouve = false;

        foreach ($utilisateurs as $user) {
            // 1. On trouve l'utilisateur par le login
            if ($user['login'] === $login) { 
                // 2. On vérifie si le mot de passe soumis correspond au HACHAGE stocké
                //    ATTENTION : $user['password'] DOIT contenir le hachage (ex: $2y$10$...)
                if (password_verify($password_soumis, $user['password'])) { 
                    // Connexion réussie
                    $_SESSION["user"]["login"] = $user['login'];
                    if (isset($user['recettesFavoris'])) {
                        $_SESSION["user"]["recettesFavoris"] = $user['recettesFavoris'];
                    } else {
                        $_SESSION["user"]["recettesFavoris"] = [];
                    }
                    header("Location: index.php");
                    exit();
                }
            }
        }
        
        // Si la boucle se termine sans connexion réussie
        $erreur_connexion = "Identifiants incorrects.";
    }

    if (isset($_GET['page']) && in_array($_GET['page'], $listePages)) {
        $pageCourante = $_GET['page'];
    } else {
        $pageCourante = 'navigation';
    }

    if(!isset($_SESSION["user"]["recettesFavoris"])){
        $_SESSION["user"]["recettesFavoris"] = [];
    }

    if(!isset($_GET['categorie'])){
       $categorieActuelle = 'Aliment';
    } else {
        $categorieActuelle = $_GET['categorie'];
    }

    if (isset($_GET['est_favori'])) {
        $idRecette = $_GET['est_favori'];
        
        if (in_array($idRecette, $_SESSION["user"]["recettesFavoris"])) {
            $indice = array_search($idRecette, $_SESSION["user"]["recettesFavoris"]);
            unset($_SESSION["user"]["recettesFavoris"][$indice]);
            $_SESSION["user"]["recettesFavoris"] = array_values($_SESSION["user"]["recettesFavoris"]);
        } else {
            $_SESSION["user"]["recettesFavoris"][] = $idRecette;
        }
        
        // Mise à jour de la liste de favoris dans le fichier users.json si l'utilisateur est connecté
        if (isset($_SESSION["user"]["login"])) {
            $utilisateurs = chargerUtilisateurs();
            $login_actuel = $_SESSION["user"]["login"];
            
            // Recherche de l'utilisateur dans le tableau des utilisateurs
            foreach ($utilisateurs as $cle => $user) {
                if ($user['login'] === $login_actuel) {
                    $utilisateurs[$cle]['recettesFavoris'] = $_SESSION["user"]["recettesFavoris"];
                    sauvegarderUtilisateurs($utilisateurs);
                    break;
                }
            }
        }

        $urlRedirection = "index.php?page=" . $pageCourante;
        if ($pageCourante === 'navigation') {
            $ancre ="#recette-" . $idRecette;
            $urlRedirection .= "&categorie=" . urlencode($categorieActuelle). $ancre;
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
            <button type="button" class="nav-bouton">
                Navigation
            </button>
        </a>
        <a href="index.php?page=favoris">
            <button type="button" class="nav-bouton">
                Recettes 
                <img src="Photos/Coeur_plein.png" width="15px"/>
            </button>
        </a>

        <div class="zone-recherche">
            <form action="" method="POST">
                <label class="recherche-label" for="recherche-input">
                    Recherche :
                </label>
                <input type="recherche" id="recherche-input" class="recherche-input"/>
                <button type="submit" class="recherche-bouton">
                    <img src="Photos/Loupe.png"/>
                </button>
            </form>
        </div>
        
        
        <div class="zone-connexion">
            <?php  
            if (isset($_SESSION["user"]["login"])) {
                echo htmlspecialchars($_SESSION["user"]["login"]);
                ?>
                <a href="index.php?page=profil">
                    <button class="connexion-bouton">
                        Profil
                    </button>
                </a>
                <form method="post">
                    <button type="submit" name="deconnexion" class="connexion-bouton">
                        Se déconnecter
                    </button>
                </form>
            <?php
            } else {
                ?> 
                <form method="post" action="index.php" style = "display: inline;"> 
                    <label for="login-nav" class="connexion-label">
                        Login
                    </label>
                    <input type="text" id="login-nav" name="login-nav" class="connexion-input" required />
                    <label for="motDePasse-nav" class="connexion-label">
                        Mot de passe
                    </label>
                    <input type="password" id="motDePasse-nav" name="motDePasse-nav" class="connexion-input" required />
                    <!-- input ?? -->
                    <button type="submit" class="connexion-bouton">
                        Connexion
                    </button>
                </form>
                <a href ="index.php?page=inscription">
                    <button class="connexion-bouton">
                        S'inscrire
                    </button>
                </a>
            <?php
            } 
            ?>
        </div>
    </nav>
    
    <?php
    // Affichage de l'erreur de connexion si elle existe
    if (isset($erreur_connexion)) {
        echo '<div class="erreur">' . $erreur_connexion . '</div>';
    }
    ?>
    <div class="structure">
        <?php
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