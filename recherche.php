<?php
    // Code HTML et PHP indenté étrangement pour respecter l'indentaion lorsqu'on fait clic droit puis "page source"
    
    function afficherListes($elementsVoulus, $elementsNonVoulus, $elementsInconnus) {
        if (!empty($elementsVoulus)) {
            echo "                <div>Liste des aliments souhaités :&nbsp;";
            $last = end($elementsVoulus);
            foreach ($elementsVoulus as $ingredient) {
                echo htmlspecialchars($ingredient);
                if ($ingredient !== $last) {
                    echo ', ';
                }
            }
            echo "</div>\n";
        }
        if (!empty($elementsNonVoulus)) {
            echo "                <div>Liste des aliments non souhaités :&nbsp;";
            $last = end($elementsNonVoulus);
            foreach ($elementsNonVoulus as $ingredient) {
                echo htmlspecialchars($ingredient);
                if ($ingredient !== $last) {
                    echo ', ';
                }
            }
            echo "</div>\n";
        }
        if (!empty($elementsInconnus)) {
            echo "                <div>Éléments non reconnus dans la requête :&nbsp;";
            $last = end($elementsInconnus);
            foreach ($elementsInconnus as $ingredient) {
                echo htmlspecialchars($ingredient);
                if ($ingredient !== $last) {
                    echo ', ';
                }
            }
            echo "</div>\n";
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

    function CalculerIndiceSatisfaction($elementsVoulus, $cocktail) {
        $indiceSatisfaction = 0 ;
        foreach($elementsVoulus as $elementVoulu){
            foreach($cocktail['index'] as $ingredient){
                if($ingredient == $elementVoulu){
                    $indiceSatisfaction++;
                }
            }
        }
        $countVoulus = count($elementsVoulus);
        if ($countVoulus > 0) {
            $indiceSatisfaction /= $countVoulus;
        } else {
            return 0;
        }
        return $indiceSatisfaction * 100;
    }

    $tousLesIngredients = '';
    if(empty($tousLesIngredients)) $tousLesIngredients = array_unique(trouverToutesLesCategories("Aliment", $Hierarchie));
    
    function estUnIngredientValide($ingredient) {
        global $tousLesIngredients;
        return in_array($ingredient, $tousLesIngredients);
    }

    function remplirTableauxIngredients(&$elementsVoulus, &$elementsNonVoulus, &$elementsInconnus, $texteRecherche) {
        preg_match_all('/("[^"]+"|\S+)/', $texteRecherche, $matches);
        $termes = $matches[0];

        $termes_sans_guillemets = array_map(function($termes) {
            return trim($termes, '"');
        }, $termes);

        foreach ($termes_sans_guillemets as $terme) {
            if (strpos($terme, '-') === 0) {
                $elementNonVoulu = substr($terme, 1);
                if (estUnIngredientValide($elementNonVoulu)) {
                    $elementsNonVoulus[] = $elementNonVoulu;
                } else {
                    $elementsInconnus[] = $elementNonVoulu ;
                }
            } else {
                if (strpos($terme, '+') === 0) {
                    $elementVoulu = substr($terme, 1);
                    if (estUnIngredientValide($elementVoulu)) {
                        $elementsVoulus[] = $elementVoulu;
                    } else {
                        $elementsInconnus[] = $elementVoulu ;
                    }
                } else {
                    if (estUnIngredientValide($terme)) {
                        $elementsVoulus[] = $terme;
                    } else {
                        $elementsInconnus[] = $terme;
                    }
                }
            }
        }
    }
    
    function afficherCocktails($elementsVoulus, $elementsNonVoulus) {
        global $Recettes;
        global $Hierarchie;
        global $texteRecherche;
        
        $elementsVoulusAvecSousCategorie = [];
        $elementsNonVoulusAvecSousCategorie = [];
        
        foreach($elementsVoulus as $elementVoulu){
            $elementsVoulusAvecSousCategorie = array_merge($elementsVoulusAvecSousCategorie, trouverToutesLesCategories($elementVoulu, $Hierarchie));
        }
        $elementsVoulusAvecSousCategorie = array_unique($elementsVoulusAvecSousCategorie);
        
        foreach($elementsNonVoulus as $elementNonVoulu){
            $elementsNonVoulusAvecSousCategorie = array_merge($elementsNonVoulusAvecSousCategorie, trouverToutesLesCategories($elementNonVoulu, $Hierarchie));
        }

        $elementsNonVoulusAvecSousCategorie = array_unique($elementsNonVoulusAvecSousCategorie);
        $cocktailsTrouves = [];
        
        foreach ($Recettes as $id => $recette) { 
            if (array_intersect($recette['index'], $elementsVoulusAvecSousCategorie) && !array_intersect($recette['index'], $elementsNonVoulusAvecSousCategorie)){
                $recette['id'] = $id;
                $recette['indiceSatisfaction'] = CalculerIndiceSatisfaction($elementsVoulusAvecSousCategorie, $recette);
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
                
                $imageCoeur = "Photos/Coeur_vide.png";
                if(isset($_SESSION["user"]["recettesFavoris"]) && in_array($cocktail['id'], $_SESSION["user"]["recettesFavoris"])){
                    $imageCoeur = "Photos/Coeur_plein.png";
                } 

                $id_html = 'recette-' . $cocktail['id'];
                
                $nomImage = str_replace(" ","_",$cocktail['titre']) . '.jpg';
                if(!file_exists("Photos/".$nomImage)) $nomImage = "default.jpg";
                
                $satisfaction = round($cocktail['indiceSatisfaction'], 2);
?>
                <div class="carte-cocktail" id="<?php echo $id_html; ?>">
                    <div class="carte-header">
                        <a href="index.php?page=recette&id=<?php echo $cocktail['id']; ?>" class="zone-cliquable">
                            <div class="carte-titre"><?php echo $cocktail['titre']; ?></div>
                        </a>
                        <a href="index.php?page=recherche&texteRecherche=<?php echo urlencode($texteRecherche); ?>&est_favori=<?php echo $cocktail['id']; ?>">
                            <img src="<?php echo $imageCoeur; ?>" class="image-coeur" alt="image coeur">
                        </a>
                    </div>
                    <a href="index.php?page=recette&id=<?php echo $cocktail['id']; ?>" class="zone-cliquable">
                        <img src="Photos/<?php echo $nomImage; ?>" class="image-cocktail" alt="image cocktail">
                        <ul class="liste-ingredients">
<?php
                        foreach($cocktail['index'] as $ing){
?>
                            <li><?php echo htmlspecialchars($ing); ?></li>
<?php
                        }
?>
                        </ul>
                        <div class="satisfaction">Satisfaction : <?php echo $satisfaction; ?>%</div>
                    </a>
                </div>
<?php
            } // Fin foreach cocktails
        } else {
?>
                <div>Aucun cocktail trouvé.</div>
<?php
        } // Fin if count($cocktailsTrouves)
    }

    // Initialisation
    $texteRecherche = isset($_GET['texteRecherche']) ? $_GET['texteRecherche'] : "";
    $messageErreur = "";
    $elementsNonVoulus = [];
    $elementsVoulus = [];
    $elementsInconnus = [];
    $afficherResultats = false;

    // Vérification de la syntaxe
    if (substr_count($texteRecherche, '"') % 2 == 1) {
        $messageErreur = 'Problème de syntaxe dans votre requête : nombre impair de double-quotes';
    } else {
        // Remplissage des tableaux
        remplirTableauxIngredients($elementsVoulus, $elementsNonVoulus, $elementsInconnus, $texteRecherche);
        
        // Vérification de l'existence des éléments
        if (empty($elementsNonVoulus) && empty($elementsVoulus)) {
            $messageErreur = 'Problème dans votre requête : recherche impossible';
        } else {
            $afficherResultats = true;
        }
    }
?>
<main>
<?php 
    if ($messageErreur !== "") { 
?>
            <div><?php echo $messageErreur; ?></div>
<?php 
    } 
?>
<?php 
    // Affichage des résultats si la recherche est valide
    if ($afficherResultats) { 
?>
            <div>
<?php afficherListes($elementsVoulus, $elementsNonVoulus, $elementsInconnus); ?>
            </div>
            
            <div class="affichage-cocktails">
<?php afficherCocktails($elementsVoulus, $elementsNonVoulus); ?>
            </div>
<?php 
    } 
?>
        </main>