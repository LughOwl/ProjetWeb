<aside>
    <?php
        function genererFilAriane($categorie, $hierarchie) {
            $filAriane = [];
            
            while ($categorie && $categorie !== 'Aliment') {
                array_unshift($filAriane, $categorie);
                $trouvee = false;
                foreach ($hierarchie as $cat => $data) {
                    if (isset($data['sous-categorie']) && in_array($categorie, $data['sous-categorie'])) {
                        $categorie = $cat;
                        $trouvee = true;
                        break;
                    }
                }
                if (!$trouvee) break;
            }
            array_unshift($filAriane, 'Aliment');
            
            $html = '';
            foreach ($filAriane as $index => $cat) {
                if ($index > 0) $html .= ' / ';
                $html .= '<a href="index.php?page=navigation&categorie=' . urlencode($cat) . '">';
                $html .= $cat;
                $html .= '</a>';
            }
            return $html;
        }

        echo '<div class="titre-page">Aliment courant</div>';
        echo '<p>' . genererFilAriane($categorieActuelle, $Hierarchie) . '</p>';

        if (isset($Hierarchie[$categorieActuelle]['sous-categorie'])) {
            echo 'Sous-catégories :<ul>';
            foreach ($Hierarchie[$categorieActuelle]['sous-categorie'] as $sousCat) {
                echo '<li>';
                echo '<a href="index.php?page=navigation&categorie=' . urlencode($sousCat) . '">';
                echo $sousCat;
                echo '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
    ?>
</aside>
<main>
    <div class="titre-page">Liste des cocktails</div>
    <div class="affichage-cocktails">
        <?php
            $cocktailsTrouves = [];
            
            function trouverToutesLesCategories($categorie, $hierarchie) {
                $toutes = [$categorie];
                if (isset($hierarchie[$categorie]['sous-categorie'])) {
                    foreach ($hierarchie[$categorie]['sous-categorie'] as $sousCat) {
                        $toutes = array_merge($toutes, trouverToutesLesCategories($sousCat, $hierarchie));
                    }
                }
                return $toutes;
            }

            $sousCategories = array_unique(trouverToutesLesCategories($categorieActuelle, $Hierarchie)); 

            foreach ($Recettes as $id => $recette) { 
                if (array_intersect($recette['index'], $sousCategories)) {
                    $recette['id'] = $id;
                    $cocktailsTrouves[] = $recette;
                }
            }

            if (count($cocktailsTrouves) > 0) {
                foreach ($cocktailsTrouves as $recette) {
                    
                    if(in_array($recette['id'], $_SESSION["user"]["recettesFavoris"])){
                        $imageCoeur = "Photos/Coeur_plein.png";
                    } else {
                        $imageCoeur = "Photos/Coeur_vide.png";
                    }

                    $id_html = 'recette-' . $recette['id'];

                    echo '<div class="carte-cocktail" id="' . $id_html . '">';
                        echo '<div class="carte-header">';
                            echo '<a href="index.php?page=recette&id=' . $recette['id'] . '" class="zone-cliquable">';
                                echo '<div class="carte-titre">' . $recette['titre'] . '</div>';
                            echo '</a>';
                            echo '<a href="index.php?categorie=' . urlencode($categorieActuelle) . '&est_favori=' . $recette['id'] . '">';
                                echo '<img src="'.$imageCoeur.'" class="image-coeur"/>';
                            echo '</a>';
                        echo '</div>';

                        echo '<a href="index.php?page=recette&id=' . $recette['id'] . '" class="zone-cliquable">';
                            $nomImage = str_replace(" ","_",$recette['titre']) . '.jpg';
                            if(!file_exists("Photos/".$nomImage)) $nomImage = "default.jpg";
                            echo '<img src="Photos/'.$nomImage.'" class="image-cocktail">';
                            echo '<ul class="liste-ingredients">';
                            foreach($recette['index'] as $ing){
                                echo '<li>' . htmlspecialchars($ing) . '</li>';
                            }
                            echo '</ul>';
                        echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucun cocktail trouvé.</p>';
            }
        ?>
    </div>
</main>