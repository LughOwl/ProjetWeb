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

    if (isset($_POST["login"]) && $_POST["login"] !== '') {
        if (loginValide($_POST["login"])) {
            $_SESSION["user"]["login"] = $_POST["login"];
        } else {
            $rapportErreur .= "Login invalide.<br/>";
        }
    }

    if (isset($_POST["password"]) && $_POST["password"] !== '') {
        $_SESSION["user"]["password"] = $_POST["password"];
    }

    if (isset($_POST["nom"]) && $_POST["nom"] !== '') {
        if (nomPrenomValide($_POST["nom"])) {
            $_SESSION["user"]["nom"] = $_POST["nom"];
        } else {
            $rapportErreur .= "Nom invalide.<br/>";
        }
    }

    if (isset($_POST["prenom"]) && $_POST["prenom"] !== '') {
        if (nomPrenomValide($_POST["prenom"])) {
            $_SESSION["user"]["prenom"] = $_POST["prenom"];
        } else {
            $rapportErreur .= "Prénom invalide.<br/>";
        }
    }

    if (isset($_POST["dateNaissance"]) && $_POST["dateNaissance"] !== '') {
        if (dateValide($_POST["dateNaissance"])) {
            $_SESSION["user"]["dateNaissance"] = $_POST["dateNaissance"];
        } else {
            $rapportErreur .= "Date de naissance invalide ou moins de 18 ans.<br/>";
        }
    }

    if (isset($_POST["sexe"])) { // Le champ <select> a toujours une valeur par défaut, donc on vérifie juste l'existence
        $_SESSION["user"]["sexe"] = $_POST["sexe"];
    }
}
?>

<form method="post" action="#">
    <h2>Inscription</h2>

    <label for="login">Login:</label>
    <input type="text" id="login" name="login" required 
           value="<?php echo isset($_SESSION["user"]["login"]) ? htmlspecialchars($_SESSION["user"]["login"]) : ''; ?>"
    /><br/>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required /><br/>

    <label for="nom">Nom:</label>
    <input type="text" id="nom" name="nom" 
           value="<?php echo isset($_SESSION["user"]["nom"]) ? htmlspecialchars($_SESSION["user"]["nom"]) : ''; ?>"
    /><br/>

    <label for="prenom">Prenom:</label>
    <input type="text" id="prenom" name="prenom" 
           value="<?php echo isset($_SESSION["user"]["prenom"]) ? htmlspecialchars($_SESSION["user"]["prenom"]) : ''; ?>"
    /><br/>

    <label for="dateNaissance">Date de Naissance:</label>
    <input type="date" id="dateNaissance" name="dateNaissance"
           value="<?php echo isset($_SESSION["user"]["dateNaissance"]) ? htmlspecialchars($_SESSION["user"]["dateNaissance"]) : ''; ?>"
    /><br/>

    <label for="sexe">Sexe:</label>
    <select id="sexe" name="sexe">
        <option value="Non defini">Non défini</option>
        <option value="Homme">Homme</option>
        <option value="Femme">Femme</option>
    </select><br/>

    <input type="submit" name="submit" value="S'inscrire"/>
</form>

<?php
    if (isset($_POST["submit"])) {
        remplirSession();
        $users = chargerUtilisateurs();
        if ($rapportErreur === "") {
            if (estPresentLogin($users, $_POST["login"])) {
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