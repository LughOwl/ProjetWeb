<?php
    function nomPrenomValide($nomPrenom){
        $pattern = '/^\p{L}+(?:[-’]\p{L}+)*(?:\s+\p{L}+(?:[-’]\p{L}+)*)*$/u';
        return preg_match($pattern, $nomPrenom);
    }

    function dateValide($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return false;
        }
        $ageMin = new DateTime("-18 years");
        return $d <= $ageMin;
    }

    $rapportErreur = "";

    function remplirSession() {
        global $rapportErreur;

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

        if (isset($_POST["sexe-inscription"])) {
            $_SESSION["user"]["sexe"] = $_POST["sexe-inscription"];
        }
    }
?>
<main class="profil-form">
    <form method="post" action="#">
        <h2>Profil</h2>

        <label for="login">Login:</label>
        <input type="text" id="login" name="login" disabled
            value="<?php 
                if (isset($_SESSION["user"]["login"])) {
                    echo htmlspecialchars($_SESSION["user"]["login"]);
                } else {
                    echo '';
                }            
            ?>"
        /><br/>

        <label for="password">Password : non-modifiable</label><br/>

        <label for="nom-profil">Nom:</label>
        <input type="text" id="nom-profil" name="nom-profil" 
            value="<?php
                if (isset($_POST["nom-profil"])) {
                    echo htmlspecialchars($_POST["nom-profil"]);
                } else if (isset($_SESSION["user"]["nom"])) {
                    echo htmlspecialchars($_SESSION["user"]["nom"]);
                } else {
                    echo '';
                }
            ?>"
        /><br/>

        <label for="prenom-profil">Prenom:</label>
        <input type="text" id="prenom-profil" name="prenom-profil" 
            value="<?php 
                if (isset($_POST["prenom-profil"])) {
                    echo htmlspecialchars($_POST["prenom-profil"]);
                } else if (isset($_SESSION["user"]["prenom"])) {
                    echo htmlspecialchars($_SESSION["user"]["prenom"]);
                } else {
                    echo '';
                }
            ?>"
        /><br/>

        <label for="dateNaissance-profil">Date de Naissance:</label>
        <input type="date" id="dateNaissance-profil" name="dateNaissance-profil"
            value="<?php 
                if (isset($_POST["dateNaissance-profil"])) {
                    echo htmlspecialchars($_POST["dateNaissance-profil"]);
                } else if (isset($_SESSION["user"]["dateNaissance"])) {
                    echo htmlspecialchars($_SESSION["user"]["dateNaissance"]);
                } else {
                    echo '';
                }
            ?>"
        /><br/>

        <label for="sexe-profil">Sexe:</label>
        <select id="sexe-profil" name="sexe-profil">
            <option value="Non defini" <?php 
                if (isset($_POST["sexe-profil"]) && $_POST["sexe-profil"] == "Non defini") {
                    echo 'selected';
                }
                else if (isset($_SESSION["user"]["sexe"]) && $_SESSION["user"]["sexe"] == "Non defini") {
                    echo 'selected';
                }
            ?>>Non défini</option>
            
            <option value="Homme" <?php 
                if (isset($_POST["sexe-profil"]) && $_POST["sexe-profil"] == "Homme") {
                    echo 'selected';
                }
                else if (isset($_SESSION["user"]["sexe"]) && $_SESSION["user"]["sexe"] == "Homme") {
                    echo 'selected';
                }
            ?>>Homme</option>
            
            <option value="Femme" <?php 
                if (isset($_POST["sexe-profil"]) && $_POST["sexe-profil"] == "Femme") {
                    echo 'selected';
                }
                else if (isset($_SESSION["user"]["sexe"]) && $_SESSION["user"]["sexe"] == "Femme") {
                    echo 'selected';
                }
            ?>>Femme</option>
        </select><br/>

        <input type="submit" name="submit-profil" value="Enregistrer les modifications"/>
    </form>
    <?php
        if (isset($_POST["submit-profil"])) {
            remplirSession();
            $users = chargerUtilisateurs();
            if ($rapportErreur === "") {
                $utilisateur = [
                    "login" => $_SESSION["user"]["login"],
                    "password" => password_hash($_SESSION["user"]["password"], PASSWORD_DEFAULT)
                ];
                if(isset($_SESSION["user"]["nom"])){
                    $utilisateur["nom"] = $_SESSION["user"]["nom"];
                }
                else{
                    $utilisateur["nom"] = "";
                }
                if(isset($_SESSION["user"]["prenom"])){
                    $utilisateur["prenom"] = $_SESSION["user"]["prenom"];
                }
                else{
                    $utilisateur["prenom"] = "";
                }
                if(isset($_SESSION["user"]["dateNaissance"])){
                    $utilisateur["dateNaissance"] = $_SESSION["user"]["dateNaissance"];
                }
                else{
                    $utilisateur["dateNaissance"] = "";
                }
                if(isset($_SESSION["user"]["sexe"])){
                    $utilisateur["sexe"] = $_SESSION["user"]["sexe"];
                }
                else{
                    $utilisateur["sexe"] = "";
                }
                if(isset($_SESSION["user"]["recettesFavoris"])){
                    $utilisateur["recettesFavoris"] = $_SESSION["user"]["recettesFavoris"];
                }
                else{
                    $utilisateur["recettesFavoris"] = "";
                }
                supprimerUtilisateur($_SESSION["user"]["login"], $users);
                $users[] = $utilisateur;
                sauvegarderUtilisateurs($users);
                header("Location: index.php?page=profil");                
            }else {
                echo "<br><strong>Erreurs lors de l'enregistrement des modifications :</strong><br/>" . $rapportErreur;
            }
        }
    ?>
</main>