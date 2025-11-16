<?php
include 'Donnees.inc.php';

// Récupérer la catégorie actuelle depuis les paramètres GET
$categorieActuelle = isset($_GET['categorie']) ? $_GET['categorie'] : 'Aliment';

// Fonction pour générer le fil d'Ariane
function genererFilAriane($categorie, $hierarchie) {
    $filAriane = [];
    
    // Remonter la hiérarchie jusqu'à la racine
    while ($categorie && $categorie !== 'Aliment') {
        array_unshift($filAriane, $categorie);
        
        // Trouver la super-catégorie
        $trouvee = false;
        foreach ($hierarchie as $cat => $data) {
            if (isset($data['sous-categorie']) && in_array($categorie, $data['sous-categorie'])) {
                $categorie = $cat;
                $trouvee = true;
                break;
            }
        }
        
        if (!$trouvee) {
            break;
        }
    }
    
    // Ajouter la racine au début
    array_unshift($filAriane, 'Aliment');
    
    // Générer le HTML du fil d'Ariane
    $html = '';
    
    foreach ($filAriane as $index => $categorie) {
        if ($index > 0) {
            $html .= ' / ';
        }
        
        if ($index === count($filAriane) - 1) {
            // Catégorie actuelle (non cliquable)
            $html .= '<span>' . htmlspecialchars($categorie) . '</span>';
        } else {
            // Lien vers la catégorie avec appel JavaScript
            $html .= '<a href="javascript:void(0)" onclick="chargerCategorie(\'' . addslashes($categorie) . '\')">';
            $html .= htmlspecialchars($categorie);
            $html .= '</a>';
        }
    }
    
    return $html;
}

// Fonction pour vérifier si une catégorie a des sous-catégories
function aDesSousCategories($categorie, $hierarchie) {
    if (!isset($hierarchie[$categorie])) {
        return false;
    }
    
    $data = $hierarchie[$categorie];
    return isset($data['sous-categorie']) && !empty($data['sous-categorie']);
}

// Fonction pour afficher la hiérarchie
function afficherHierarchie($categorie, $hierarchie) {
    if (!isset($hierarchie[$categorie])) {
        return '';
    }
    
    $html = '';
    $data = $hierarchie[$categorie];
    
    // Afficher les sous-catégories
    if (isset($data['sous-categorie']) && !empty($data['sous-categorie'])) {
        $html .= '<ul style="margin: 0;">';
        
        foreach ($data['sous-categorie'] as $sousCategorie) {
            $html .= '<li>';
            // Lien avec appel JavaScript
            $html .= '<a href="javascript:void(0)" onclick="chargerCategorie(\'' . addslashes($sousCategorie) . '\')">';
            $html .= htmlspecialchars($sousCategorie);
            $html .= '</a>';
            $html .= '</li>';
        }
        
        $html .= '</ul>';
    }
    
    return $html;
}
?>

<strong>Aliment courant</strong>
<p id="Fil-dAriane"><?php echo genererFilAriane($categorieActuelle, $Hierarchie); ?></p>
<?php if (aDesSousCategories($categorieActuelle, $Hierarchie)): ?>
    Sous-catégories :
    <?php echo afficherHierarchie($categorieActuelle, $Hierarchie); ?>
<?php endif; ?>