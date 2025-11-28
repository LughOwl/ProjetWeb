<aside>
    <?php
        // Fonction pour générer le fil d'Ariane
        function genererFilAriane($categorie, $hierarchie) {
            $filAriane = [];
            
            // Remonter la hiérarchie
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
            
            // Génération HTML avec des liens PHP classiques
            $html = '';
            foreach ($filAriane as $index => $cat) {
                if ($index > 0) $html .= ' / ';
                // LIEN STANDARD
                $html .= '<a href="index.php?page=navigation&categorie=' . urlencode($cat) . '">';
                $html .= $cat;
                $html .= '</a>';
            }
            return $html;
        }

        // Affichage
        echo '<strong>Aliment courant</strong>';
        echo '<p>' . genererFilAriane($categorieActuelle, $Hierarchie) . '</p>';

        if (isset($Hierarchie[$categorieActuelle]['sous-categorie'])) {
            echo 'Sous-catégories :<ul>';
            foreach ($Hierarchie[$categorieActuelle]['sous-categorie'] as $sousCat) {
                echo '<li>';
                // LIEN STANDARD
                echo '<a href="index.php?page=navigation&categorie=' . urlencode($sousCat) . '">';
                echo $sousCat;
                echo '</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
    ?>
</aside>

<main class="affichage-cocktails">
    <strong>Liste des cocktails</strong>
    <?php
        $cocktailsTrouves = [];
        
        // Fonction récursive pour trouver tous les sous-ingrédients
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

        // Recherche des cocktails
        foreach ($Recettes as $id => $recette) { 
            // On vérifie si la recette contient un des ingrédients
            if (array_intersect($recette['index'], $sousCategories)) {
                $recette['id'] = $id; // On garde l'ID (0, 1, 2...)
                $cocktailsTrouves[] = $recette;
            }
        }

        // Affichage
        if (count($cocktailsTrouves) > 0) {
            foreach ($cocktailsTrouves as $recette) {
                
                // Gestion du Cœur (Vide ou Plein) selon la session PHP
                if(in_array($recette['id'], $_SESSION["user"]["recettesFavoris"])){
                    $imageCoeur = "Photos/Coeur_plein.png";
                } else {
                    $imageCoeur = "Photos/Coeur_vide.png";
                }

                $id_html = 'recette-' . $recette['id'];

                echo '<div class="cocktail-card" id="' . $id_html . '">';
                echo '<strong>' . $recette['titre'] . '</strong> ';
                
                // LE LIEN MAGIQUE : On recharge la page en gardant la catégorie + action toggle
                echo '<a href="index.php?categorie=' . urlencode($categorieActuelle) . '&est_favori=' . $recette['id'] . '">';
                echo '<img src="'.$imageCoeur.'" width="20px" height="20px" style="vertical-align:middle;"/>';
                echo '</a><br><br>';

                // Image du cocktail
                $nomImage = str_replace(" ","_",$recette['titre']) . '.jpg';
                if(!file_exists("Photos/".$nomImage)) $nomImage = "default.jpg";
                echo '<img src="Photos/'.$nomImage.'" width="70" height="100"><br>';
                
                // Ingrédients
                echo '<ul style="font-size:0.8em; padding-left:15px;">';
                foreach($recette['index'] as $ing){
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