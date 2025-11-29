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

    function remplirSessionEtVerifications() {
        global $rapportErreur;

        if (isset($_POST["nom-profil"]) && $_POST["nom-profil"] !== '') {
            if (nomPrenomValide($_POST["nom-profil"])) {
                $_SESSION["user"]["nom"] = $_POST["nom-profil"];
            } else {
                $rapportErreur .= "Nom invalide.<br/>";
            }
        }

        if (isset($_POST["prenom-profil"]) && $_POST["prenom-profil"] !== '') {
            if (nomPrenomValide($_POST["prenom-profil"])) {
                $_SESSION["user"]["prenom"] = $_POST["prenom-profil"];
            } else {
                $rapportErreur .= "Prénom invalide.<br/>";
            }
        }

        if (isset($_POST["dateNaissance-profil"]) && $_POST["dateNaissance-profil"] !== '') {
            if (dateValide($_POST["dateNaissance-profil"])) {
                $_SESSION["user"]["dateNaissance"] = $_POST["dateNaissance-profil"];
            } else {
                $rapportErreur .= "Date de naissance invalide ou moins de 18 ans.<br/>";
            }
        }

        if (isset($_POST["sexe-profil"])) {
            $_SESSION["user"]["sexe"] = $_POST["sexe-profil"];
        }
    }
    function verificationFormulaire(){
        global $rapportErreur;
        if (isset($_POST["submit-profil"])) {
            remplirSessionEtVerifications();
            if ($rapportErreur === "") {
                $utilisateurs = chargerUtilisateurs();
                modifierUtilisateur($_SESSION["user"]["login"], $utilisateurs);
                sauvegarderUtilisateurs($utilisateurs); 
                echo '<div class="enregistrement">Les enregistrements ont bien été pris en compte.</div>';              
            } else {
                echo '<div class="erreur">Erreurs lors de l\'enregistrement des modifications :<br/>'.$rapportErreur.'</div>';
            }
        }
    }
?>
<main class="style-formulaire">
    <form method="post" action="#">
        <div class="titre-page">Profil</div>

        <?php
            verificationFormulaire();
        ?>

        <label for="login">Login (non modifiable):</label>
        <input type="text" id="login" name="login" disabled
            value="<?php 
                if (isset($_SESSION["user"]["login"])) {
                    echo htmlspecialchars($_SESSION["user"]["login"]);
                } else {
                    echo '';
                }            
            ?>"
        />

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
        />

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
        />

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
        />

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
        </select>

        <input type="submit" name="submit-profil" value="Enregistrer les modifications" class="bouton-submit"/>
    </form>
</main>