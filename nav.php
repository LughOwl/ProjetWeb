<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script>

    function ouvrirHierarchie() {
        fetch("Hierarchie.php")
        .then(response => response.text())
        .then(hierarchie => {
             document.getElementById('hierarchie').innerHTML = hierarchie;
        })
    }

    </script>

</head>
<body>
    <nav style="border: 1px solid black; display: inline ; padding: 10px">
        <button type="button" onClick="ouvrirHierarchie()">Navigation</button>
        <button type="button">Recette <img src="Photos/Coeur_plein.png" width="15px" height="15px"/></button>
        Recherche: 
        <form style="display: inline;" action="" method="POST">
            <input type="search" />
            <button type="submit"><img src="Photos/Loupe.png" width="15px" height="15px"/></button>
        </form>
        <button>Zone de connexion</button>
    </nav>
    <div style="display: flex ; gap : 10px;">
        <aside style="border: 1px solid black; padding: 10px; margin: 15px 0px;" id="hierarchie"></aside>
        <main style="border: 1px solid black; padding: 10px; margin: 15px 0px;" id="cocktail"></main>
    </div>
</body>
</html>