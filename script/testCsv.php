<?php 

//Configuration de la connexion à la BDD
$AgriProxiPDO = new PDO( 'mysql:dbname=csv;host=localhost', 'root');

//Définition du fichier source pour remplir la base de donnée
$csv = '..\agriproxiRessources\produits-sud-de-france5.csv';

/**
 * Fonction permettant de parcourir le fichier .csv et mettre les valeurs dans un tableau pour traitement par la suite
 *
 * @param [type] $csv
 * @return array
 */
function read($csv) {
    $file = fopen($csv,'r');
    //for ($i = 0; $i < 1000 ; $i ++) {
        while (!feof($file)) { 
            $line[] = fgetcsv($file, null, ';');
        }
        fclose($file);
        return $line;
    }
// }

//Assignation de la fonction
$csv = read($csv);

function injectData($csv, $PDO){
    foreach($csv as $line){
        $tab = [];
        for($i = 0; $i < count($line); $i++){
            $line[$i] = str_replace('"', '', $line[$i]);
            $line[$i] = str_replace("\\", '', $line[$i]);
            $tab[] = $line[$i];
        }
        $sql = 'INSERT INTO test (produit_nom, produit_actif, produit_categorie1, produit_categorie2, produit_description, entreprise_id, produit_bio, siqo, entreprise_nom, entreprise_adresse, entreprise_code_postal, entreprise_ville, entreprise_descr_fr, coordonnees_gps, url_image) VALUES ("'.$tab[0].'", "'.$tab[1].'", "'.$tab[2].'", "'.$tab[3].'", "'.$tab[4].'", "'.$tab[5].'", "'.$tab[6].'", "'.$tab[7].'", "'.$tab[8].'", "'.$tab[9].'", "'.$tab[10].'", "'.$tab[11].'", "'.$tab[12].'", "'.$tab[13].'", "'.$tab[14].'")';
        echo $sql;
        $PDO->prepare($sql)->execute();
    }
}

//injectData($csv, $AgriProxiPDO);

// 1606 entreprises
// 10 683 produits