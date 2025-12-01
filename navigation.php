<aside>
<?php
    // Code HTML et PHP indenté étrangement pour respecter l'indentaion lorsqu'on fait clic droit puis "page source"
    
    function genererFilAriane($cheminString) {
        $categories = explode('_', $cheminString);
        
        $html = '';
        $cheminEnCours = [];

        foreach ($categories as $index => $cat) {
            $cheminEnCours[] = $cat;
            $lienVersCetteEtape = implode('_', $cheminEnCours);

            if ($index > 0) {
                $html .= ' / ';
            }
            
            $html .= '<a href="index.php?page=navigation&chemin=' . urlencode($lienVersCetteEtape) . '">';
            $html .= $cat;
            $html .= '</a>';
        }
        return $html;
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
?>
            <div class="titre-page">Aliment courant</div>
            <p>
                <?php echo genererFilAriane($cheminCourant);?>
            
            </p>
<?php
    if (isset($Hierarchie[$categorieActuelle]['sous-categorie'])) {
?>
            <div>Sous-catégories :</div>
            <ul>
<?php 
        foreach ($Hierarchie[$categorieActuelle]['sous-categorie'] as $sousCat) {
            $nouveauChemin = $cheminCourant . '_' . $sousCat;
?>
                <li>
                    <a href="index.php?page=navigation&chemin=<?php echo urlencode($nouveauChemin); ?>">
                        <?php echo $sousCat; ?>
                    
                    </a>
                </li>
<?php
        }
?>
            </ul>
<?php
    }
?>
        </aside>
        <main>
<?php
    $cocktailsTrouves = [];
    $sousCategories = array_unique(trouverToutesLesCategories($categorieActuelle, $Hierarchie)); 

    foreach ($Recettes as $id => $recette) { 
        if (array_intersect($recette['index'], $sousCategories)) {
            $recette['id'] = $id;
            $cocktailsTrouves[] = $recette;
        }
    }
?>
            <div class="titre-page">Liste des cocktails</div>
            <div class="affichage-cocktails">
<?php
    if (count($cocktailsTrouves) > 0) {
        foreach ($cocktailsTrouves as $recette) {
            $imageCoeur = "Photos/Coeur_vide.png";
            if(isset($_SESSION["user"]["recettesFavoris"]) && in_array($recette['id'], $_SESSION["user"]["recettesFavoris"])){
                $imageCoeur = "Photos/Coeur_plein.png";
            } 

            $id_html = 'recette-' . $recette['id'];
            
            $nomImage = str_replace(" ","_",$recette['titre']) . '.jpg';
            if(!file_exists("Photos/".$nomImage)) $nomImage = "default.jpg";
?>
                <div class="carte-cocktail" id="<?php echo $id_html; ?>">
                    <div class="carte-header">
                        <a href="index.php?page=recette&id=<?php echo $recette['id']; ?>" class="zone-cliquable">
                            <div class="carte-titre"><?php echo $recette['titre']; ?></div>
                        </a>
                        <a href="index.php?chemin=<?php 
                            echo urlencode($cheminCourant); ?>&est_favori=<?php 
                            echo urlencode($recette['id']); ?>">
                            <img src="<?php echo $imageCoeur; ?>" class="image-coeur" alt="image coeur">
                        </a>
                    </div>
                    <a href="index.php?page=recette&id=<?php echo $recette['id']; ?>" class="zone-cliquable">
                        <img src="Photos/<?php echo $nomImage; ?>" class="image-cocktail" alt="image cocktail">
                        <ul class="liste-ingredients">
<?php
            foreach($recette['index'] as $ing){
?>
                            <li><?php echo htmlspecialchars($ing); ?></li>
<?php
            }
?>
                        </ul>
                    </a>
                </div>
<?php
        }
    } else {
?>
            <p>Aucun cocktail trouvé.</p>
<?php
    }
?>
            </div>
        </main>