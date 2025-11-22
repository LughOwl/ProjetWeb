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