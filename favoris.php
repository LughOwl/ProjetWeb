<main class="affichage-cocktails">
    <strong>Liste des cocktails favoris</strong>
    <?php

        $indicesCocktailsFavoris = $_SESSION["user"]["recettesFavoris"];
        $nbCocktailsFavoris = count($_SESSION["user"]["recettesFavoris"]);

        // Affichage
        if ($nbCocktailsFavoris > 0) {
            $cocktailsFavoris = [];
            foreach ($indicesCocktailsFavoris as $indice) { 
                $cocktailsFavoris[] = $Recettes[$indice];
            }
            foreach ($cocktailsFavoris as $cocktailFavoris) {

                echo '<div class="cocktail-card">';
                echo '<strong>' . $cocktailFavoris['titre'] . '</strong> ';
                
                // LE LIEN MAGIQUE : On recharge la page en gardant la catégorie + action toggle
                $id = array_search($cocktailFavoris, $Recettes);
                echo '<a href="index.php?page=favoris&est_favori=' . $id . '">';
                echo '<img src="Photos/Coeur_plein.png" width="20px" height="20px" style="vertical-align:middle;"/>';
                echo '</a><br><br>';

                // Image du cocktail
                $nomImage = str_replace(" ","_",$cocktailFavoris['titre']) . '.jpg';
                if(!file_exists("Photos/".$nomImage)) $nomImage = "default.jpg";
                echo '<img src="Photos/'.$nomImage.'" width="70" height="100"><br>';
                
                // Ingrédients
                echo '<ul style="font-size:0.8em; padding-left:15px;">';
                foreach($cocktailFavoris['index'] as $ing){
                    echo '<li>' . htmlspecialchars($ing) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        } else {
            echo '<p>Aucun cocktail trouvé.</p>';
        }
    ?>
</main>
