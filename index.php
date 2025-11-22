<?php 
    session_start();
    include 'Donnees.inc.php';

    $listePages = ['navigation', 'favoris'];

    if (isset($_GET['page'])) {
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
    </style>
</head>
<body>
    <?php
        
    ?>
    <nav>
        <a href="index.php?page=navigation&categorie=Aliment"><button type="button">Navigation</button></a>
        <a href="index.php?page=favoris"><button type="button">Recette <img src="Photos/Coeur_plein.png" width="15px"/></button></a>
        Recherche: 
        <form style="display: inline;" action="" method="POST">
            <input type="search" />
            <button type="submit"><img src="Photos/Loupe.png" width="15px"/></button>
        </form>
        <button>Zone de connexion</button>
    </nav>

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
                //Ajout de nouvelles pages ici si besoin
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
                //Ajout de nouvelles pages ici si besoin
                default:
                    echo 'Page non trouvée.';
                    break;
            }
            ?>
        </main>
    </div>
    
</body>
</html>