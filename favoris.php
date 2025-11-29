<main>
            <div class="titre-page">Liste des cocktails favoris</div>
            <div class="affichage-cocktails">
<?php
                    $indicesCocktailsFavoris = $_SESSION["user"]["recettesFavoris"];
                    $nbCocktailsFavoris = count($_SESSION["user"]["recettesFavoris"]);

                    if ($nbCocktailsFavoris > 0) {
                        $cocktailsFavoris = [];
                        foreach ($indicesCocktailsFavoris as $indice) { 
                            $cocktailsFavoris[] = $Recettes[$indice];
                        }
                        foreach ($cocktailsFavoris as $cocktailFavoris) {
                            $id = array_search($cocktailFavoris, $Recettes);
?>
                <div class="carte-cocktail">
                    <div class="carte-header">
                        <a href="index.php?page=recette&id=<?php echo $id; ?>" class="zone-cliquable">
                            <div class="carte-titre"><?php echo $cocktailFavoris['titre']; ?></div>
                        </a>
                        <a href="index.php?page=favoris&est_favori=<?php echo $id; ?>">
                            <img src="Photos/Coeur_plein.png" class="image-coeur" alt="image coeur">
                        </a>
                    </div>
                    <a href="index.php?page=recette&id=<?php echo $id; ?>" class="zone-cliquable">
<?php
                            $nomImage = str_replace(" ","_",$cocktailFavoris['titre']) . '.jpg';
                            if(!file_exists("Photos/".$nomImage)) $nomImage = "default.jpg";
?>
                        <img src="Photos/<?php echo $nomImage; ?>" class="image-cocktail" alt="image cocktail">
                        <ul class="liste-ingredients">
<?php
                            foreach($cocktailFavoris['index'] as $ing){
                                echo '                            <li>' . htmlspecialchars($ing) . '</li>
';
                            }
?>
                        </ul>
                    </a>
                </div>
<?php
                        }
                    } else {
                        echo '<p>Aucun cocktail trouvé.</p>';
                    }
?>
            </div>
        </main>