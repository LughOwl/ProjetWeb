<strong>Liste des cocktails</strong>
<?php
    include 'Donnees.inc.php';

    $cocktailsTrouves = [];
    $sousCategories = []; // Cette variable est déclarée ici

   
    $categorieActuelle = isset($_GET['categorie']) ? htmlspecialchars($_GET['categorie']) : 'Aucune catégorie spécifiée';
    
    
    function trouverToutesLesCategories($categorie, $hierarchie) {
        $toutesLesSousCategories = [$categorie]; // Ajoute la catégorie actuelle
        
        if (isset($hierarchie[$categorie]['sous-categorie'])) {
            $sousCategories = $hierarchie[$categorie]['sous-categorie'];
            
            foreach ($sousCategories as $sousCat) {
                $toutesLesSousCategories = array_merge($toutesLesSousCategories, trouverToutesLesCategories($sousCat, $hierarchie));
            }
        }
        return $toutesLesSousCategories;
    }

    // Ici, vous utilisez la variable $sousCategories pour stocker les aliments à rechercher
    $sousCategories = array_unique(trouverToutesLesCategories($categorieActuelle, $Hierarchie)); 

    // CORRECTION : On récupère l'ID de la recette ($recette_id) et on l'ajoute à la recette
    foreach ($Recettes as $recette_id => $recette) { 
        if (array_intersect($recette['index'], $sousCategories)) {
            // CORRECTION : Ajout de l'ID à la recette pour que afficherCocktail puisse l'utiliser
            $recette['id'] = $recette_id; 
            $cocktailsTrouves[] = $recette;
        }
    }

    function afficherImage($recette){
        $nomImage = str_replace(" ","_",$recette['titre']);
        $nomImage .= '.jpg';
        if(!(file_exists("Photos/".$nomImage))){
          $nomImage = "default.jpg";
        }
        echo '<img src="Photos/'.$nomImage.'" width= "70px" height= "100px">'.'</br>';
    }
    
    function afficherCocktail($recette) {
        // Cette variable est maintenant disponible grâce à la correction dans la boucle foreach
        $recette_id = $recette['id']; 
        $titre = htmlspecialchars($recette['titre']);
        
        $coeur_path = 'Photos/Coeur_vide.png';
        $coeur_alt = 'Ajouter aux favoris';
        
        // --- CODE JAVASCRIPT DIRECT (SIMULATION) ---
        
        $js_code = "
            let img = document.getElementById('coeur-".$recette_id."');
            if (img.src.includes('Coeur_vide.png')) {
                img.src = 'Photos/Coeur_plein.png';
                img.alt = 'Retirer des favoris';
            } else {
                img.src = 'Photos/Coeur_vide.png';
                img.alt = 'Ajouter aux favoris';
            }
            return false;
        ";
        // --- FIN CODE JAVASCRIPT DIRECT ---
        
        echo '<div class="cocktail-carte" style="border: 1px solid #ccc; border-radius: 5px; margin: 10px; padding: 15px; width: 150px; height: 300px; display: flex; flex-direction: column; align-items: center; float: left;">';
        
        echo '<div style = "display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 5px; font-size: 0.9em;">';
        
        // --- Titre ---
        echo '<span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' . $titre . '</span>';
        
        // --- Le bouton CŒUR cliquable avec JS direct ---
        echo '<button 
                class="bouton-coeur"
                data-recette-id="' . $recette_id . '"
                onclick="' . $js_code . '" 
                style="background: none; border: none; cursor: pointer; padding: 0; margin-left: 5px; flex-shrink: 0;"
              >';
        // L'ID 'coeur-' est l'identifiant que le JavaScript utilise
        echo '<img id="coeur-' . $recette_id . '" src="' . $coeur_path . '" alt="' . $coeur_alt . '" width="20" height="20" style="vertical-align: middle;">';
        echo '</button>';
        
        echo '</div>'; 

        afficherImage($recette);
        
        echo '<div style="font-size: 0.8em; margin-top: 10px; text-align: left; width: 100%; height: 100px; overflow: hidden;">';
        foreach($recette['index'] as $ingredient){
            echo '• ' . htmlspecialchars($ingredient) . '</br>';
        }
        echo '</div>';
        
        echo '</div>';
    }

    if (count($cocktailsTrouves) > 0) {
        foreach ($cocktailsTrouves as $recette) {
            afficherCocktail($recette);
        }
    } else {
        echo '<p>Aucun cocktail trouvé correspondant à la catégorie et ses sous-catégories.</p>';
    }
?>