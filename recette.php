<?php
if (!isset($_GET['id']) || !isset($Recettes[$_GET['id']])) {
    echo '<p>Cocktail non trouvé.</p>';
    exit();
}

$id = $_GET['id'];
$cocktail = $Recettes[$id];

if (in_array($id, $_SESSION["user"]["recettesFavoris"])) {
    $imageCoeur = "Photos/Coeur_plein.png";
} else {
    $imageCoeur = "Photos/Coeur_vide.png";
}
        ?><main>
            <div class="recette-header">
                <div class="titre-page"><?php echo htmlspecialchars($cocktail['titre']); ?></div>
                <a href="index.php?page=recette&id=<?php echo $id; ?>&est_favori=<?php echo $id; ?>">
                    <img src="<?php echo $imageCoeur; ?>" class="image-coeur"/>
                </a>
            </div>
            
            <div class="recette-content">
                <div class="recette-image">
                    <?php
                    $nomImage = str_replace(" ", "_", $cocktail['titre']) . '.jpg';
                    if (!file_exists("Photos/" . $nomImage)) {
                        $nomImage = "default.jpg";
                    }
                    ?><img src="Photos/<?php echo $nomImage; ?>">
                </div>
                
                <div class="recette-ingredients">
                    <div class="titre-page">Ingrédients</div>
                    <ul>
                    <?php
                        $ingredients = explode('|', $cocktail['ingredients']);
                        foreach ($ingredients as $ingredient) {
                        ?>    <li><?php echo htmlspecialchars(trim($ingredient)); ?></li>
                    <?php }
                    ?></ul>
                </div>
                
                <div class="recette-preparation">
                    <div class="titre-page">Préparation</div>
                    <div><?php echo htmlspecialchars($cocktail['preparation']); ?></div>
                </div>
                
                <div class="recette-categories">
                    <div class="titre-page">Catégories</div>
                    <ul>
                    <?php
                        foreach ($cocktail['index'] as $categorie) {
                        ?>    <li><?php echo htmlspecialchars($categorie); ?></li>
                    <?php }
                    ?></ul>
                </div>
            </div>
        </main>