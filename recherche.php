<main class="affichage-cocktails">
    <?php
        $texteRecherche = $_POST['texteRecherche'];
        
        preg_match_all('/("[^"]+"|\S+)/', $texteRecherche, $matches);
        $termes = $matches[0];

        $termes_sans_guillemets = array_map(function($termes) {
            return trim($termes, '"');
        }, $termes);

        foreach ($termes_sans_guillemets as $terme) {
            if (str_starts_with($terme, '-')) {
                $elementsNonVoulus[] = substr($terme, 1);
            } else {
                if (str_starts_with($terme, '+')) {
                    $elementsVoulus[] = substr($terme, 1);
                } else {
                    $elementsVoulus[] = $terme;
                }
            }
        }

    ?>
</main>