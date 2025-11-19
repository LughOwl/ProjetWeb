<?php
    include 'Donnees.inc.php';
    // Création du tableau pour stocker les sous-catégories
    $sousCategories = [];

    // Récupérer la catégorie actuelle depuis les paramètres GET
    $categorieActuelle = isset($_GET['categorie']) ? htmlspecialchars($_GET['categorie']) : 'Aucune catégorie spécifiée';
    echo $categorieActuelle;

    // Trouver toutes les sous-catégories de la catégorie actuelle
    foreach ($Hierarchie as $cleCategorie => $categorie) {
        if (isset($categorie['sous-categorie']) && in_array($categorieActuelle, $categorie['sous-categorie'])) {
            $sousCategories = $categorie['sous-categorie'];
            break;
        }
    }

    // Fonction pour afficher un cocktail
    function afficherCocktail($recette) {
        echo '<div style="border: 1px solid black; margin: 5px; padding: 5px;">';
        echo '<strong>' . htmlspecialchars($recette['titre']) . '</strong>';
        // bouton coeur
        // image cocktail
        // ingrédients
        echo '</div>';
    }

    // Afficher les sous-catégories
    foreach ($Recettes as $recette) {
        $cocktail = array_search($categorieActuelle, $recette['index']);
        foreach ($sousCategories as $sousCategorie) {
            afficherCocktail($recette);
        }
        }
        foreach ($sousCategories as $sousCategorie) {
            echo '<div>';
            echo $recette['titre'];
            echo '</div>';
        }
    }
?>

<strong>Liste des cocktails</strong>