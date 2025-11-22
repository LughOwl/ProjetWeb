<?php 
    session_start();
    include 'Donnees.inc.php'; // On charge les données une fois pour toute la page

    // Initialisation
    if(!isset($_SESSION["user"]["recettesFavoris"])){
        $_SESSION["user"]["recettesFavoris"] = [];
    }

    // Récupération de la catégorie courante (par défaut 'Aliment')
    $categorieActuelle = isset($_GET['categorie']) ? $_GET['categorie'] : 'Aliment';

    // --- TRAITEMENT DU CLIC SUR UN CŒUR ---
    if (isset($_GET['toggle_favori'])) {
        $idRecette = $_GET['toggle_favori'];
        
        // Ajout ou Suppression
        if (in_array($idRecette, $_SESSION["user"]["recettesFavoris"])) {
            $key = array_search($idRecette, $_SESSION["user"]["recettesFavoris"]);
            unset($_SESSION["user"]["recettesFavoris"][$key]);
            $_SESSION["user"]["recettesFavoris"] = array_values($_SESSION["user"]["recettesFavoris"]); // Réindexation
        } else {
            $_SESSION["user"]["recettesFavoris"][] = $idRecette;
        }

        // Redirection pour nettoyer l'URL mais EN GARDANT la catégorie actuelle
        // Sinon on revient à l'accueil à chaque clic
        header("Location: nav.php?categorie=" . urlencode($categorieActuelle)); 
        exit();
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cocktails</title>
    <style>
        /* Un peu de style pour remplacer ton JS */
        a { text-decoration: none; color: black; }
        a:hover { text-decoration: underline; }
        .layout { display: flex; gap: 10px; }
        .sidebar { border: 1px solid black; padding: 10px; width: 200px; }
        .content { border: 1px solid black; padding: 10px; width: 500px; }
        .cocktail-card { border: 1px solid #ccc; border-radius: 5px; margin: 10px 0; padding: 15px; }
    </style>
</head>
<body>
    <nav>
        <a href="nav.php"><button type="button">Navigation</button></a>
        <button type="button">Recette <img src="Photos/Coeur_plein.png" width="15px"/></button>
        Recherche: 
        <form style="display: inline;" action="" method="POST">
            <input type="search" />
            <button type="submit"><img src="Photos/Loupe.png" width="15px"/></button>
        </form>
        <button>Zone de connexion</button>
    </nav>

    <div class="layout">
        <aside class="sidebar">
            <?php include 'Hierarchie.php'; ?>
        </aside>
    
        <main class="content">
            <?php include 'Cocktails.php'; ?>
        </main>
    </div>
    
</body>
</html>