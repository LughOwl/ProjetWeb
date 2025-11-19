<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <?php 
        for($i = 0 ; $i < 5 ; $i++){
        echo'<button id="1'.$i.'"><img id="Coeur'.$i.'" src="Photos/Coeur_vide.png" width="50px" height="50px"/></button>';
        echo '<script>';
        echo 'document.getElementById("1'.$i.'").onclick = function(){
             let img = document.getElementById("Coeur'.$i.'");
                    if (img.src.includes("Coeur_vide.png")) {
                        img.src = "Photos/Coeur_plein.png";
                    } else {
                        img.src = "Photos/Coeur_vide.png";
                    }
        };';
        echo '</script>';
    }
    ?>
</body>
</html>