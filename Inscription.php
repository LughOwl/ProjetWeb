<?php
    if (!isset($_SESSION["user"]) || !is_array($_SESSION["user"])) {
        $_SESSION["user"] = [];
    }

    $messageResultat = "";
    $valeurLogin = "";
    $valeurNom = "";
    $valeurPrenom = "";
    $valeurDate = "";
    $valeurSexe = "Non defini";

    function loginValide($login) {
        return preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $login);
    }

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

    function estPresentLogin($users, $login){
        foreach($users as $utilisateurs){
            if($utilisateurs["login"] == $login){
                return true;
            }
        }
        return false;
    }

    if (isset($_POST["submit-inscription"])) {
        $rapportErreur = "";

        if (isset($_POST["login-inscription"]) && $_POST["login-inscription"] !== '') {
            $valeurLogin = $_POST["login-inscription"];
            if (loginValide($_POST["login-inscription"])) {
                $_SESSION["user"]["login"] = $_POST["login-inscription"];
            } else {
                $rapportErreur .= "Login invalide<br>
                    ";
            }
        }

        if (isset($_POST["password-inscription"]) && $_POST["password-inscription"] !== '') {
            $_SESSION["user"]["password"] = $_POST["password-inscription"];
        }

        if (isset($_POST["nom-inscription"]) && $_POST["nom-inscription"] !== '') {
            $valeurNom = $_POST["nom-inscription"];
            if (nomPrenomValide($_POST["nom-inscription"])) {
                $_SESSION["user"]["nom"] = $_POST["nom-inscription"];
            } else {
                $rapportErreur .= "Nom invalide<br>
                    ";
            }
        }

        if (isset($_POST["prenom-inscription"]) && $_POST["prenom-inscription"] !== '') {
            $valeurPrenom = $_POST["prenom-inscription"];
            if (nomPrenomValide($_POST["prenom-inscription"])) {
                $_SESSION["user"]["prenom"] = $_POST["prenom-inscription"];
            } else {
                $rapportErreur .= "Prénom invalide<br>
                    ";
            }
        }

        if (isset($_POST["dateNaissance-inscription"]) && $_POST["dateNaissance-inscription"] !== '') {
            $valeurDate = $_POST["dateNaissance-inscription"];
            if (dateValide($_POST["dateNaissance-inscription"])) {
                $_SESSION["user"]["dateNaissance"] = $_POST["dateNaissance-inscription"];
            } else {
                $rapportErreur .= "Date de naissance invalide ou moins de 18 ans<br>
                    ";
            }
        }

        if (isset($_POST["sexe-inscription"])) {
            $_SESSION["user"]["sexe"] = $_POST["sexe-inscription"];
            $valeurSexe = $_POST["sexe-inscription"];
        }

        $users = chargerUtilisateurs();
        
        if ($rapportErreur === "") {
            if (estPresentLogin($users, $_POST["login-inscription"])){
                $messageResultat = '        <div class="erreur">login déjà présent</div>';
            } else {
                $nouvelUtilisateur = [
                    "login" => $_SESSION["user"]["login"],
                    "password" => password_hash($_SESSION["user"]["password"], PASSWORD_DEFAULT),
                ];

                if (isset($_SESSION["user"]["nom"])) {
                    $nouvelUtilisateur["nom"] = $_SESSION["user"]["nom"];
                } else {
                    $nouvelUtilisateur["nom"] = "";
                }

                if (isset($_SESSION["user"]["prenom"])) {
                    $nouvelUtilisateur["prenom"] = $_SESSION["user"]["prenom"];
                } else {
                    $nouvelUtilisateur["prenom"] = "";
                }

                if (isset($_SESSION["user"]["dateNaissance"])) {
                    $nouvelUtilisateur["dateNaissance"] = $_SESSION["user"]["dateNaissance"];
                } else {
                    $nouvelUtilisateur["dateNaissance"] = "";
                }

                if (isset($_SESSION["user"]["sexe"])) {
                    $nouvelUtilisateur["sexe"] = $_SESSION["user"]["sexe"];
                } else {
                    $nouvelUtilisateur["sexe"] = "";
                }
                
                if (isset($_SESSION["user"]["recettesFavoris"])) {
                    $nouvelUtilisateur["recettesFavoris"] = $_SESSION["user"]["recettesFavoris"];
                } else {
                    $nouvelUtilisateur["recettesFavoris"] = [];
                }

                $users[] = $nouvelUtilisateur;
                sauvegarderUtilisateurs($users);
                
                header("Location: index.php");
                exit();
            }
        } else {
            $messageResultat = '        <div class="erreur">
                    Erreurs lors de l\'inscription :<br>
                    ' . $rapportErreur . '
                </div>';
        }
    }
        ?><main class="style-formulaire">
            <form method="post" action="#">
                <div class="titre-page">Inscription</div>

        <?php 
            if ($messageResultat !== "") {
                echo $messageResultat . "\n";
            }
        ?>

                <label for="login">Login:</label>
                <input type="text" id="login" name="login-inscription" required value="<?php echo htmlspecialchars($valeurLogin); ?>">

                <label for="password-inscription">Password:</label>
                <input type="password" id="password-inscription" name="password-inscription" required >

                <label for="nom-inscription">Nom:</label>
                <input type="text" id="nom-inscription" name="nom-inscription" value="<?php echo htmlspecialchars($valeurNom); ?>">

                <label for="prenom-inscription">Prenom:</label>
                <input type="text" id="prenom-inscription" name="prenom-inscription" value="<?php echo htmlspecialchars($valeurPrenom); ?>">

                <label for="dateNaissance-inscription">Date de Naissance:</label>
                <input type="date" id="dateNaissance-inscription" name="dateNaissance-inscription" value="<?php echo htmlspecialchars($valeurDate); ?>">

                <label for="sexe-inscription">Sexe:</label>
                <select id="sexe-inscription" name="sexe-inscription">
                    <option value="Non defini" <?php if ($valeurSexe == "Non defini") { echo 'selected'; } ?>>Non défini</option>
                    <option value="Homme" <?php if ($valeurSexe == "Homme") { echo 'selected'; } ?>>Homme</option>
                    <option value="Femme" <?php if ($valeurSexe == "Femme") { echo 'selected'; } ?>>Femme</option>
                </select>

                <input type="submit" name="submit-inscription" value="S'inscrire" class="bouton-submit">
            </form>
        </main>