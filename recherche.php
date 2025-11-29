<?php
    function afficherListes($elementsVoulus,$elementsNonVoulus,$elementsInconnus){
        if(!empty($elementsVoulus)){
            ?><div>Liste des aliments souhaités :&nbsp;
            <?php
            foreach($elementsVoulus as $ingredient){
                if(end($elementsVoulus) == $ingredient){
                    echo $ingredient.'</div>';
                }
                else{
                    echo $ingredient.', ';
                }
            }
        }
        if(!empty($elementsNonVoulus)){
            ?><div>Liste des aliments non souhaités :&nbsp;      
            <?php
            foreach($elementsNonVoulus as $ingredient){
                if(end($elementsNonVoulus) == $ingredient){
                    echo $ingredient.'</div>';
                }
                else{
                    echo $ingredient.', ';
                }
            }
        }
        if(!empty($elementsInconnus)){
            ?><div>Éléments non reconnus dans la requête :&nbsp;
            <?php
            foreach($elementsInconnus as $ingredient){
                if(end($elementsInconnus) == $ingredient){
                    echo $ingredient.'</div>';
                }
                else{
                    echo $ingredient.', ';
                }
            }
        }
    }

    function trouverToutesLesCategories($categorie, $hierarchie) {
        $toutes = [$categorie];
        if (isset($hierarchie[$categorie]['sous-categorie'])) {
            foreach ($hierarchie[$categorie]['sous-categorie'] as $sousCat) {
                $toutes = array_merge($toutes, trouverToutesLesCategories($sousCat, $hierarchie));
            }
        }
        return $toutes;
    }

    function afficherCocktails($elementsVoulus,$elementsNonVoulus){
        global $Recettes;
        global $Hierarchie;
        global $texteRecherche;
        $elementsVoulusAvecSousCategorie = [];
        $elementsNonVoulusAvecSousCategorie = [];
        foreach($elementsVoulus as $elementVoulu){
            $elementsVoulusAvecSousCategorie= array_merge($elementsVoulusAvecSousCategorie,trouverToutesLesCategories($elementVoulu,$Hierarchie));
        }
        $elementsVoulusAvecSousCategorie = array_unique($elementsVoulusAvecSousCategorie);
        
        foreach($elementsNonVoulus as $elementNonVoulu){
            $elementsNonVoulusAvecSousCategorie = array_merge($elementsNonVoulusAvecSousCategorie,trouverToutesLesCategories($elementNonVoulu,$Hierarchie));
        }

        $elementsNonVoulusAvecSousCategorie = array_unique($elementsNonVoulusAvecSousCategorie);
        $cocktailsTrouves = [];
        foreach ($Recettes as $id => $recette) { 
            if (array_intersect($recette['index'], $elementsVoulusAvecSousCategorie) && !array_intersect($recette['index'],$elementsNonVoulusAvecSousCategorie)){
                $recette['id'] = $id;
                $recette['indiceSatisfaction'] = CalculerIndiceSatisfaction($elementsVoulusAvecSousCategorie,$recette);
                $cocktailsTrouves[] = $recette;
            }
        }

        if (count($cocktailsTrouves) > 0) {
            $indices = [];
            foreach($cocktailsTrouves as $key => $cocktail){
                $indices[$key] = $cocktail['indiceSatisfaction'];
            }
            arsort($indices);
            
            $cocktailsTries = [];
            foreach($indices as $key => $valeur){
                $cocktailsTries[] = $cocktailsTrouves[$key];
            }
            $cocktailsTrouves = $cocktailsTries;
            foreach ($cocktailsTrouves as $cocktail) {
                
                if(in_array($cocktail['id'], $_SESSION["user"]["recettesFavoris"])){
                    $imageCoeur = "Photos/Coeur_plein.png";
                } else {
                    $imageCoeur = "Photos/Coeur_vide.png";
                }

                $id_html = 'recette-' . $cocktail['id'];

                echo '<div class="carte-cocktail" id="' . $id_html . '">';
                    echo '<div class="carte-header">';
                        echo '<a href="index.php?page=recette&id=' . $cocktail['id'] . '" class="zone-cliquable">';
                            echo '<div class="carte-titre">' . $cocktail['titre'] . '</div>';
                        echo '</a>';
                        echo '<a href="index.php?page=recherche&texteRecherche='.$texteRecherche.'&est_favori=' . $cocktail['id'] . '">';
                            echo '<img src="'.$imageCoeur.'" class="image-coeur"/>';
                        echo '</a>';
                    echo '</div>';

                    echo '<a href="index.php?page=recette&id=' . $cocktail['id'] . '" class="zone-cliquable">';
                        $nomImage = str_replace(" ","_",$cocktail['titre']) . '.jpg';
                        if(!file_exists("Photos/".$nomImage)) $nomImage = "default.jpg";
                        echo '<img src="Photos/'.$nomImage.'" class="image-cocktail">';
                        
                        echo '<ul class="liste-ingredients">';
                        foreach($cocktail['index'] as $ing){
                            echo '<li>' . htmlspecialchars($ing) . '</li>';
                        }
                        echo '</ul>';
                        echo '<div class="satisfaction">Satisfaction : '.round($cocktail['indiceSatisfaction'],2).'%.</div>';
                    echo '</a>';
                echo '</div>';
            }
        } else {
            echo '<p>Aucun cocktail trouvé.</p>';
        }
    }

    function estUnIngredientValide($ingredient){
        global $Hierarchie;
        $tousLesIngredient = array_unique(trouverToutesLesCategories("Aliment", $Hierarchie));
        foreach($tousLesIngredient as $ingredientValide){
            if($ingredient == $ingredientValide){
                return true;
            }
        }
        return false;
    }

    function remplirTableauxIngredients(&$elementsVoulus,&$elementsNonVoulus,&$elementsInconnus,$texteRecherche){
        preg_match_all('/("[^"]+"|\S+)/', $texteRecherche, $matches);
        $termes = $matches[0];

        $termes_sans_guillemets = array_map(function($termes) {
            return trim($termes, '"');
        }, $termes);

        foreach ($termes_sans_guillemets as $terme) {
            if (strpos($terme, '-') === 0) {
                $elementNonVoulu = substr($terme,1);
                if(estUnIngredientValide($elementNonVoulu)){
                    $elementsNonVoulus[] = $elementNonVoulu;
                }
                else{
                    $elementsInconnus[] = $elementNonVoulu ;
                }
            } else {
                if (strpos($terme, '+') === 0) {
                    $elementVoulu = substr($terme,1);
                    if(estUnIngredientValide($elementVoulu)){
                        $elementsVoulus[] = $elementVoulu;
                    }
                    else{
                        $elementsInconnus[] = $elementVoulu ;
                    }
                } else {
                    if(estUnIngredientValide($terme)){
                        $elementsVoulus[] = $terme;
                    }
                    else{
                        $elementsInconnus[] = $terme;
                    }
                }
            }
        }
    }

    function CalculerIndiceSatisfaction($elementsVoulus,$cocktail){
        $indiceSatisfaction = 0 ;
        foreach($elementsVoulus as $elementVoulu){
            foreach($cocktail['index'] as $ingredient){
                if($ingredient == $elementVoulu){
                    $indiceSatisfaction++;
                }
            }
        }
        $indiceSatisfaction /= count($elementsVoulus);
        return $indiceSatisfaction * 100;
    }

?>
<main>
    <?php
        if(isset($_GET['texteRecherche'])){
            $texteRecherche = $_GET['texteRecherche'];
        }
        else{
            $texteRecherche ="";
        }

        if(substr_count($texteRecherche,'"') % 2 == 1){
            echo '<div>Problème de syntaxe dans votre requête : nombre impair de double-quotes</div>';
        }
        else{
            $elementsNonVoulus = [];
            $elementsVoulus = [];
            $elementsInconnus = [];
            remplirTableauxIngredients($elementsVoulus,$elementsNonVoulus,$elementsInconnus,$texteRecherche);
            if(empty($elementsNonVoulus) && empty($elementsVoulus)){
                echo '<div>Problème dans votre requête : recherche impossible</div>';
            }   
            else{
                echo '<div>';
                    afficherListes($elementsVoulus,$elementsNonVoulus,$elementsInconnus);
                echo '</div>';
                echo '<div class="affichage-cocktails">';
                    afficherCocktails($elementsVoulus,$elementsNonVoulus);
                echo '</div>';
            }
        }
    ?>
</main>