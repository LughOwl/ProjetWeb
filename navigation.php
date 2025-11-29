<aside>
<?php
    function genererFilAriane($categorie, $hierarchie) {
        $filAriane = [];
        $categorieCourante = $categorie;
        
        $parentPrevu = isset($_GET['parent']) ? urldecode($_GET['parent']) : null;

        while ($categorieCourante && $categorieCourante !== 'Aliment') {
            array_unshift($filAriane, $categorieCourante);
            $trouvee = false;
            
            if ($parentPrevu && $categorieCourante === $categorie) {
                if (isset($hierarchie[$parentPrevu]['sous-categorie']) && in_array($categorieCourante, $hierarchie[$parentPrevu]['sous-categorie'])) {
                    $categorieCourante = $parentPrevu;
                    $trouvee = true;
                    $parentPrevu = null;
                }
            }
            if (!$trouvee) {
                foreach ($hierarchie as $cat => $data) {
                    if (isset($data['sous-categorie']) && in_array($categorieCourante, $data['sous-categorie'])) {
                        $categorieCourante = $cat;
                        $trouvee = true;
                        break;
                    }
                }
            }
            
            if (!$trouvee) break;
        }
        array_unshift($filAriane, 'Aliment');
        
        $html = '';
        foreach ($filAriane as $index => $cat) {
            if ($index > 0) $html .= ' / 
                ';
            $html .= '<a href="index.php?page=navigation&categorie=' . urlencode($cat) . '">';
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
                <?php echo genererFilAriane($categorieActuelle, $Hierarchie);?>
            
            </p>
<?php
    if (isset($Hierarchie[$categorieActuelle]['sous-categorie'])) {
?>
            <div>Sous-catégories :</div>
            <ul>
<?php 
        foreach ($Hierarchie[$categorieActuelle]['sous-categorie'] as $sousCat) {
?>
                <li>
                    <a href="index.php?page=navigation&categorie=<?php echo urlencode($sousCat); ?>&parent=<?php echo urlencode($categorieActuelle); ?>">
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
                        <a href="index.php?categorie=<?php 
                            echo urlencode($categorieActuelle); ?>&est_favori=<?php 
                            echo urlencode($recette['id']); ?><?php if(isset($_GET['parent'])) { 
                            echo '&parent=' . urlencode($_GET['parent']); } ?>">
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
        } // Fin foreach cocktails
    } else {
?>
            <p>Aucun cocktail trouvé.</p>
<?php
    } // Fin if count($cocktailsTrouves)
?>
            </div>
        </main>