<?php
// Pas besoin de include 'Donnees.inc.php' car nav.php l'a déjà fait

// Fonction pour générer le fil d'Ariane
function genererFilAriane($categorie, $hierarchie) {
    $filAriane = [];
    $catTemp = $categorie;
    
    // Remonter la hiérarchie
    while ($catTemp && $catTemp !== 'Aliment') {
        array_unshift($filAriane, $catTemp);
        $trouvee = false;
        foreach ($hierarchie as $cat => $data) {
            if (isset($data['sous-categorie']) && in_array($catTemp, $data['sous-categorie'])) {
                $catTemp = $cat;
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
        $html .= '<a href="nav.php?categorie=' . urlencode($cat) . '">';
        $html .= htmlspecialchars($cat);
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
        echo '<a href="nav.php?categorie=' . urlencode($sousCat) . '">';
        echo htmlspecialchars($sousCat);
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>';
}
?>