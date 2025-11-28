<?php
    // Initialise le tableau user si nécessaire
    if (!isset($_SESSION["user"]) || !is_array($_SESSION["user"])) {
        $_SESSION["user"] = [];
    }

    /* --- VALIDATION DES CHAMPS --- */

    function loginValide($login) {
        // Le login doit commencer par une lettre et contenir des lettres/chiffres
        return preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $login);
    }


    function nomPrenomValide($nomPrenom){
        // Correction de l'expression régulière pour être plus robuste (gestion des espaces multiples)
        // Permet: Lettres accentuées (\p{L}), tirets/apostrophes encadrés par des lettres, espaces multiples.
        $pattern = '/^\p{L}+(?:[-’]\p{L}+)*(?:\s+\p{L}+(?:[-’]\p{L}+)*)*$/u';
        return preg_match($pattern, $nomPrenom);
    }

    function dateValide($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        // 1. Vérifie si le format est valide ET correspond exactement à l'entrée
        if (!$d || $d->format('Y-m-d') !== $date) {
            return false;
        }
        // 2. Vérifie l'âge minimum (18 ans)
        $ageMin = new DateTime("-18 years");
        return $d <= $ageMin;
    }

    function estPresentLogin($users,$login){
        foreach($users as $utilisateurs){
        if($utilisateurs["login"] == $login){
            return true;
        }
        }
        return false;
    }
    $rapportErreur = "";

    /* --- TRAITEMENT --- */

    function remplirSession() {
        global $rapportErreur;

        // Sécurisation
        if (!isset($_SESSION["user"]) || !is_array($_SESSION["user"])) {
            $_SESSION["user"] = [];
        }

        // Remplacement de !empty() par isset()
        // Pour simuler le comportement de !empty() (ne pas stocker de chaînes vides), 
        // on vérifie si la variable existe ET qu'elle n'est pas une chaîne vide.

        if (isset($_POST["login-inscription"]) && $_POST["login-inscription"] !== '') {
            if (loginValide($_POST["login-inscription"])) {
                $_SESSION["user"]["login"] = $_POST["login-inscription"];
            } else {
                $rapportErreur .= "Login invalide.<br/>";
            }
        }

        if (isset($_POST["password-inscription"]) && $_POST["password-inscription"] !== '') {
            $_SESSION["user"]["password"] = $_POST["password-inscription"];
        }

        if (isset($_POST["nom-inscription"]) && $_POST["nom-inscription"] !== '') {
            if (nomPrenomValide($_POST["nom-inscription"])) {
                $_SESSION["user"]["nom"] = $_POST["nom-inscription"];
            } else {
                $rapportErreur .= "Nom invalide.<br/>";
            }
        }

        if (isset($_POST["prenom-inscription"]) && $_POST["prenom-inscription"] !== '') {
            if (nomPrenomValide($_POST["prenom-inscription"])) {
                $_SESSION["user"]["prenom"] = $_POST["prenom-inscription"];
            } else {
                $rapportErreur .= "Prénom invalide.<br/>";
            }
        }

        if (isset($_POST["dateNaissance-inscription"]) && $_POST["dateNaissance-inscription"] !== '') {
            if (dateValide($_POST["dateNaissance-inscription"])) {
                $_SESSION["user"]["dateNaissance"] = $_POST["dateNaissance-inscription"];
            } else {
                $rapportErreur .= "Date de naissance invalide ou moins de 18 ans.<br/>";
            }
        }

        if (isset($_POST["sexe-inscription"])) { // Le champ <select> a toujours une valeur par défaut, donc on vérifie juste l'existence
            $_SESSION["user"]["sexe"] = $_POST["sexe-inscription"];
        }
    }
?>
<main class="inscription-form">
    <form method="post" action="#">
        <h2>Inscription</h2>

        <label for="login">Login:</label>
        <input type="text" id="login" name="login-inscription" required 
            value="<?php
                if (isset($_POST["login-inscription"])) {
                    echo htmlspecialchars($_POST["login-inscription"]);
                } 
                else {
                    echo '';
                }
            ?>"
        /><br/>

        <label for="password-inscription">Password:</label>
        <input type="password" id="password-inscription" name="password-inscription" required /><br/>

        <label for="nom-inscription">Nom:</label>
        <input type="text" id="nom-inscription" name="nom-inscription" 
            value="<?php 
                if (isset($_POST["nom-inscription"])) {
                    echo htmlspecialchars($_POST["nom-inscription"]);
                } 
                else {
                    echo '';
                }
            ?>"
        /><br/>

        <label for="prenom-inscription">Prenom:</label>
        <input type="text" id="prenom-inscription" name="prenom-inscription" 
            value="<?php 
                if (isset($_POST["prenom-inscription"])) {
                    echo htmlspecialchars($_POST["prenom-inscription"]);
                }
                else {
                    echo '';
                }
            ?>"
        /><br/>

        <label for="dateNaissance-inscription">Date de Naissance:</label>
        <input type="date" id="dateNaissance-inscription" name="dateNaissance-inscription"
            value="<?php 
                if (isset($_POST["dateNaissance-inscription"])) {
                    echo htmlspecialchars($_POST["dateNaissance-inscription"]);
                } 
                else {
                    echo '';
                }
            ?>"
        /><br/>

        <label for="sexe-inscription">Sexe:</label>
        <select id="sexe-inscription" name="sexe-inscription">
            <option value="Non defini" <?php 
                if (isset($_POST["sexe-inscription"]) && $_POST["sexe-inscription"] == "Non defini") {
                    echo 'selected';
                }
            ?>>Non défini</option>
            
            <option value="Homme" <?php 
                if (isset($_POST["sexe-inscription"]) && $_POST["sexe-inscription"] == "Homme") {
                    echo 'selected';
                }
            ?>>Homme</option>
            
            <option value="Femme" <?php 
                if (isset($_POST["sexe-inscription"]) && $_POST["sexe-inscription"] == "Femme") {
                    echo 'selected';
                }
            ?>>Femme</option>
        </select><br/>

        <input type="submit" name="submit-inscription" value="S'inscrire"/>
    </form>
    <?php
        if (isset($_POST["submit-inscription"])) {
            remplirSession();
            $users = chargerUtilisateurs();
            if ($rapportErreur === "") {
                if (estPresentLogin($users, $_POST["login-inscription"])){
                    echo "login déjà présent";
                }
                else{
                    $nouvelUtilisateur = [
                        "login" => $_SESSION["user"]["login"],
                        "password" => password_hash($_SESSION["user"]["password"], PASSWORD_DEFAULT)
                    ];
                    if(isset($_SESSION["user"]["nom"])){
                        $nouvelUtilisateur["nom"] = $_SESSION["user"]["nom"];
                    }
                    else{
                        $nouvelUtilisateur["nom"] = "";
                    }
                    if(isset($_SESSION["user"]["prenom"])){
                        $nouvelUtilisateur["prenom"] = $_SESSION["user"]["prenom"];
                    }
                    else{
                        $nouvelUtilisateur["prenom"] = "";
                    }
                    if(isset($_SESSION["user"]["dateNaissance"])){
                        $nouvelUtilisateur["dateNaissance"] = $_SESSION["user"]["dateNaissance"];
                    }
                    else{
                        $nouvelUtilisateur["dateNaissance"] = "";
                    }
                    if(isset($_SESSION["user"]["sexe"])){
                        $nouvelUtilisateur["sexe"] = $_SESSION["user"]["sexe"];
                    }
                    else{
                        $nouvelUtilisateur["sexe"] = "";
                    }
                    if(isset($_SESSION["user"]["recettesFavoris"])){
                        $nouvelUtilisateur["recettesFavoris"] = $_SESSION["user"]["recettesFavoris"];
                    }
                    else{
                        $nouvelUtilisateur["recettesFavoris"] = "";
                    }
                    $users[] = $nouvelUtilisateur;
                    sauvegarderUtilisateurs($users);
                    header("Location: index.php");
                }                 
            }else {
                echo "<br><strong>Erreurs lors de l'inscription :</strong><br/>" . $rapportErreur;
            }
        }
    ?>
</main>

