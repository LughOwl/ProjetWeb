<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cocktails</title>

    <script>
        function chargerCategorie(categorie) {
            // Charger la hiérarchie avec la catégorie sélectionnée
            fetch("Hierarchie.php?categorie=" + encodeURIComponent(categorie))
            .then(response => response.text())
            .then(hierarchie => {
                document.getElementById('contenu-hierarchie').innerHTML = hierarchie;
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('contenu-hierarchie').innerHTML = '<p>Erreur lors du chargement de la hiérarchie.</p>';
            });
        }

        function ouvrirHierarchie() {
            // Charger la hiérarchie racine
            chargerCategorie('Aliment');
        }

        // Charger la hiérarchie au démarrage
        document.addEventListener('DOMContentLoaded', function() {
            ouvrirHierarchie();
        });
    </script>
</head>
<body>
    <nav>
        <button type="button" onClick="ouvrirHierarchie()">Navigation</button>
        <button type="button">Recette <img src="Photos/Coeur_plein.png" width="15px" height="15px"/></button>
        Recherche: 
        <form style="display: inline;" action="" method="POST">
            <input type="search" />
            <button type="submit"><img src="Photos/Loupe.png" width="15px" height="15px"/></button>
        </form>
        <button>Zone de connexion</button>
    </nav>
    
    <aside id="contenu-hierarchie" style="border: 1px solid black; padding: 10px; margin-top: 10px; width: 200px;">
        <p>Chargement de la hiérarchie...</p>
    </aside>
    
    <main>
        <!-- Contenu principal ici -->
    </main>
</body>
</html>