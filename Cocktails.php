<strong>Liste des cocktails</strong>
<?php
    include 'Donnees.inc.php';

    $cocktailsTrouves = [];
    $sousCategories = []; // Cette variable est déclarée ici
    $i = 0;
   
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

    function afficherCocktail($recette,$i) {

        $buttonId = 'bouton'.$i;
        $imageId = 'Coeur'.$i;

        echo '<div style="border: 1px solid #ccc; border-radius: 5px; margin: 10px 0; padding: 15px; width: 150px; height: 300px; align-items: center;">';
        echo '<div style = "display: inline;">';
        echo $recette['titre'];
        //bouton coeur
        echo'<button id="'.$buttonId.'" onclick="ChangerCoeur(\''.$imageId.'\')">';
        echo'<img id="'.$imageId.'" src="Photos/Coeur_vide.png" width="50px" height="50px"/>';
        echo'</button>';
        echo '</div>'.'</br>';
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
            afficherCocktail($recette,$i);
            $i = $i + 1;
        }
    } else {
        echo '<p>Aucun cocktail trouvé correspondant à la catégorie et ses sous-catégories.</p>';
    }
?>