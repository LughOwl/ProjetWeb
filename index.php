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
    

    $listePages = ['navigation', 'favoris', 'inscription']; // Ajout de 'inscription'
    
    // --- GESTION DE LA DÉCONNEXION ---
    if (isset($_POST['deconnexion'])) {
        session_destroy();   // détruit la session
        header("Location: index.php");
        exit();
    }

   // --- GESTION DE LA CONNEXION ---
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = trim($_POST['login']);
        $password_soumis = $_POST['password']; // Mot de passe entré par l'utilisateur

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
                    $_SESSION["user"]["recettesFavoris"] = isset($user['recettesFavoris']) ? $user['recettesFavoris'] : [];
                    
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
    <style>
        a {color: blue; }
        .layout { display: flex; gap: 10px; }
        .sidebar { border: 1px solid black; padding: 10px; width: 200px; }
        .content { border: 1px solid black; padding: 10px; width: 500px; }
        .cocktail-card { border: 1px solid #ccc; border-radius: 5px; margin: 10px 0; padding: 15px; }
        /* Style pour le message d'erreur */
        .erreur { color: red; font-weight: bold; margin-top: 10px; } 
    </style>
</head>
<body>
    <nav>
        <a href="index.php?page=navigation&categorie=Aliment"><button type="button">Navigation</button></a>
        <a href="index.php?page=favoris"><button type="button">Recette <img src="Photos/Coeur_plein.png" width="15px"/></button></a>
        Recherche: 
        <form style="display: inline;" action="" method="POST">
            <input type="search" />
            <button type="submit"><img src="Photos/Loupe.png" width="15px"/></button>
        
        </form>
        
        <div style="display: inline; float: right; ">
            <?php  
            if (isset($_SESSION["user"]["login"])) {
                echo "Bienvenue, " . htmlspecialchars($_SESSION["user"]["login"]);
                ?><button>Profil</button>
                <form method="post" style="display: inline;">
                    <button type="submit" name="deconnexion">Se déconnecter</button>
                </form>
            <?php
            } else {
                ?> 
                <form method="post" action="index.php" style = "display: inline;"> 
                    <label for="login">Login</label>
                    <input type="text" id="login" name="login" required />
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required />
                    <button type="submit">Connexion</button>
                </form>
                <a href ="index.php?page=inscription"><button>S'inscrire</button></a>
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

    <div class="layout">
        <aside class="sidebar">
            <?php
            switch ($pageCourante) {
                case 'navigation':
                    include 'Hierarchie.php';
                    break;
                case 'favoris':
                    echo '<strong>Recettes favorites</strong><br>';
                    break;
                case 'inscription':
                    echo '<strong>Inscription</strong><br>';
                    break;
                default:
                    echo 'Page non trouvée.';
                    break;
            } 
            ?>
        </aside>
    
        <main class="content">
            <?php
            switch ($pageCourante) {
                case 'navigation':
                    include 'cocktails.php';
                    break;
                case 'favoris':
                    include 'favoris.php';
                    break;
                case 'inscription':
                    include 'Inscription.php';
                    break;
                default:
                    echo 'Page non trouvée.';
                    break;
            }
            ?>
        </main>
    </div>
    
</body>
</html>