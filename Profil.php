<?php
    // Code HTML et PHP indenté étrangement pour respecter l'indentaion lorsqu'on fait clic droit puis "page source"
    
    function nomPrenomValide($nomOuPrenom){
        return preg_match("/^[A-ZÀ-ÖØ-Þa-zà-öø-ÿ]+(?:[ '-][A-ZÀ-ÖØ-Þa-zà-öø-ÿ]+)*$/", $nomOuPrenom);
    }

    function dateValide($date) {
        $dates = explode('-', $date);
        if (count($dates) !== 3) {
            return false;
        }
        list($annee, $mois, $jour) = $dates;
        if (!checkdate($mois, $jour, $annee)) {
            return false;
        }
        $dateActuelle = new DateTime($date);
        $ageMin = new DateTime("-18 years");
        return $dateActuelle <= $ageMin;
    }

    $rapportErreur = "";

    if (isset($_POST["nom-profil"]) && $_POST["nom-profil"] !== '') {
        if (nomPrenomValide($_POST["nom-profil"])) {
            $_SESSION["user"]["nom"] = $_POST["nom-profil"];
        } else {
            $rapportErreur .= "Nom invalide.<br>
                    ";
        }
    }

    if (isset($_POST["prenom-profil"]) && $_POST["prenom-profil"] !== '') {
        if (nomPrenomValide($_POST["prenom-profil"])) {
            $_SESSION["user"]["prenom"] = $_POST["prenom-profil"];
        } else {
            $rapportErreur .= "Prénom invalide.<br>
                    ";
        }
    }

    if (isset($_POST["dateNaissance-profil"]) && $_POST["dateNaissance-profil"] !== '') {
        if (dateValide($_POST["dateNaissance-profil"])) {
            $_SESSION["user"]["dateNaissance"] = $_POST["dateNaissance-profil"];
        } else {
            $rapportErreur .= "Date de naissance invalide ou moins de 18 ans.<br>
                    ";
        }
    }

    if (isset($_POST["sexe-profil"])) {
        $_SESSION["user"]["sexe"] = $_POST["sexe-profil"];
    }
    
    $valeurLogin = '';
    $valeurNom = '';
    $valeurPrenom = '';
    $valeurDate = '';
    $valeurSexe = '';
    $messageResultat = '';
    
    if (isset($_SESSION["user"]["login"])) {
        $valeurLogin = $_SESSION["user"]["login"];
    } else {
        $valeurLogin = '';
    }
    
    if (isset($_SESSION["user"]["nom"])) {
        $valeurNom = $_SESSION["user"]["nom"];
    } else {
        $valeurNom = '';
    }

    if (isset($_SESSION["user"]["prenom"])) {
        $valeurPrenom = $_SESSION["user"]["prenom"];
    } else {
        $valeurPrenom = '';
    }

    if (isset($_SESSION["user"]["dateNaissance"])) {
        $valeurDate = $_SESSION["user"]["dateNaissance"];
    } else {
        $valeurDate = '';
    }

    if (isset($_SESSION["user"]["sexe"])) {
        $valeurSexe = $_SESSION["user"]["sexe"];
    } else {
        $valeurSexe = "Non defini";
    }

    if (isset($_POST["submit-profil"])) {
        if (isset($_POST["nom-profil"])) {
            $valeurNom = $_POST["nom-profil"];
        }

        if (isset($_POST["prenom-profil"])) {
            $valeurPrenom = $_POST["prenom-profil"];
        }

        if (isset($_POST["dateNaissance-profil"])) {
            $valeurDate = $_POST["dateNaissance-profil"];
        }

        if (isset($_POST["sexe-profil"])) {
            $valeurSexe = $_POST["sexe-profil"];
        }

        if ($rapportErreur === "") {
            $utilisateurs = chargerUtilisateurs();
            modifierUtilisateur($_SESSION["user"]["login"], $utilisateurs);
            sauvegarderUtilisateurs($utilisateurs); 
            $messageResultat = '                <div class="enregistrement">Les enregistrements ont bien été pris en compte.</div>';              
        } else {
            $messageResultat = '                <div class="erreur">
                    Erreurs lors de l\'enregistrement des modifications :<br>
                    '.$rapportErreur.'
                </div>';
        }
    }
?>
<main class="style-formulaire">
            <div class="titre-page">Profil</div>
<?php
    if ($messageResultat !== "") {
        echo $messageResultat . "\n";
    }
    if (!isset($_SESSION["user"]["login"])){
?>
            <div>Tu n'est pas connecté(e).</div>
<?php 
    } else {
?>
            <form method="post" action="#">
                <label for="login">Login (non modifiable):</label>
                <input type="text" id="login" name="login" disabled value="<?php echo htmlspecialchars($valeurLogin); ?>">

                <label for="nom-profil">Nom:</label>
                <input type="text" id="nom-profil" name="nom-profil" value="<?php echo htmlspecialchars($valeurNom); ?>">

                <label for="prenom-profil">Prenom:</label>
                <input type="text" id="prenom-profil" name="prenom-profil" value="<?php echo htmlspecialchars($valeurPrenom); ?>">

                <label for="dateNaissance-profil">Date de Naissance:</label>
                <input type="date" id="dateNaissance-profil" name="dateNaissance-profil" value="<?php echo htmlspecialchars($valeurDate); ?>">

                <label for="sexe-profil">Sexe:</label>
                <select id="sexe-profil" name="sexe-profil">
                    <option value="Non defini" <?php if ($valeurSexe == "Non defini") { echo 'selected'; } ?>>Non défini</option>
                    <option value="Homme" <?php if ($valeurSexe == "Homme") { echo 'selected'; } ?>>Homme</option>
                    <option value="Femme" <?php if ($valeurSexe == "Femme") { echo 'selected'; } ?>>Femme</option>
                </select>

                <input type="submit" name="submit-profil" value="Enregistrer les modifications" class="bouton-submit">
            </form>
<?php 
    }
?>
        </main>