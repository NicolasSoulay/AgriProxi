<?php 

//Configuration de la connexion à la BDD
$AgriProxiPDO = new PDO( 'mysql:dbname=agriproxi;host=localhost', 'root');

//Requêtes sur les différentes tables pour gérer les clés étrangères par la suite
$villes = $AgriProxiPDO->query('SELECT * FROM ville')->fetchAll(PDO::FETCH_ASSOC);
$entreprises = $AgriProxiPDO->query('SELECT * FROM entreprise')->fetchAll(PDO::FETCH_ASSOC);
$categories = $AgriProxiPDO->query('SELECT * FROM categorie')->fetchAll(PDO::FETCH_ASSOC);
$subCategories = $AgriProxiPDO->query('SELECT * FROM sous_categorie')->fetchAll(PDO::FETCH_ASSOC);
$produits = $AgriProxiPDO->query('SELECT * FROM produit')->fetchAll(PDO::FETCH_ASSOC);
$appellations = $AgriProxiPDO->query('SELECT * FROM appellation')->fetchAll(PDO::FETCH_ASSOC);

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

/**
 * Injection des entreprises
 *
 * @param [type] $csv
 * @param [type] $PDO
 * @return void
 */
function injectEntreprise($csv, $PDO)  {
    $tab = [];
    foreach ($csv as $line) {
        $name = $line[8];
        if (!in_array($name, $tab)) {
            $description = $line[12];
            $sql = 'INSERT INTO entreprise (name, description, type_entreprise_id) VALUES ("'.$name.'", "'.$description.'", 1)';
            $PDO->prepare($sql)->execute();
            $tab[] = $name;
        }
    }
}
// injectEntreprise($csv, $AgriProxiPDO);


/**
 * injection des adresses
 *
 * @param [type] $csv
 * @param [type] $PDO
 * @param [type] $villes
 * @param [type] $entreprises
 * @return void
 */
function injectAdresse($csv, $PDO, $villes, $entreprises)  {
    $tab = [];
    foreach ($csv as $line) {
        $name = $line[8];
        if (!in_array($name, $tab)) {
            $label = $line[9];
            $zipCode = $line[10];
            $gps = $line[13];
            $latitude = explode(',', $gps)[0];
            $longitude = substr(explode(',',$gps)[1],1);
            $entreprise_id = '';
            $ville_id = '';
            $villeFormate = strtoupper(
                str_replace(' ', '-', $line[11])
            );
            $villeFormate = explode("-", $villeFormate);
            foreach ($villeFormate as $key => $value) {
                if ($value === 'ST') {
                    $villeFormate[$key] = 'SAINT';
                }
                if ($value === 'STE') {
                    $villeFormate[$key] = 'SAINTE';
                }
            }
            $villeFormate = implode('-', $villeFormate);
            foreach($entreprises as $entreprise){
                if($entreprise['name'] === $name){
                    $entreprise_id = $entreprise['id'];
                }
            }
            foreach($villes as $ville){
                if($ville['name'] === $villeFormate){
                    $ville_id = $ville['id'];
                }
            }
            $sql = 'INSERT INTO adresse (label, zip_code, latitude, longitude, ville_id, entreprise_id) VALUES ("'.$label.'", "'.$zipCode.'", "'.$latitude.'", "'.$longitude.'", '.$ville_id.', '.$entreprise_id.')';
            $PDO->prepare($sql)->execute();
            $tab[] = $name;
        }
    }
}
//injectAdresse($csv, $AgriProxiPDO, $villes, $entreprises);


/**
 * Injection des Catégories
 *
 * @param [type] $csv
 * @param [type] $PDO
 * @return void
 */
function injectCategorie($csv, $PDO){
    $tab = [];
    foreach ($csv as $line) {
        $categorie = $line[2];
        if (!in_array($categorie, $tab)) {
            $sql = 'INSERT INTO categorie (name) VALUES ("'.$categorie.'")';
            $PDO->prepare($sql)->execute();
            //var_dump ($categorie);
            $tab[] = $categorie;
        }
    }
}
//injectCategorie($csv,$AgriProxiPDO);


/**
 * Injection des Sous Catégories
 *
 * @param [type] $csv
 * @param [type] $PDO
 * @param [type] $categories
 * @return void
 */
function injectSousCategorie($csv, $PDO, $categories){
    $tab = [];
    foreach ($csv as $line) {
        $subCategorie = $line[3];
        if (!in_array($subCategorie, $tab)) {
            $categorieId= '';
            foreach($categories as $categorie){
                if($line[2] === $categorie['name']){
                    $categorieId = $categorie['id'];
                }
            }
            $sql = 'INSERT INTO sous_categorie (name, categorie_id) VALUES ("'.$subCategorie.'",'.$categorieId.')';
            $PDO->prepare($sql)->execute();
            var_dump ($subCategorie);
            $tab[] = $subCategorie;
        }
    }
}
//injectSousCategorie($csv, $AgriProxiPDO, $categories);


/**
 * Injection des produits
 *
 * @param [type] $csv
 * @param [type] $PDO
 * @param [type] $entreprises
 * @param [type] $subCategories
 * @return void
 */
function injectProduit($csv, $PDO, $entreprises, $subCategories){
    $tab = [];
        $compteur = '1';//
    foreach ($csv as $line) {
        $productName = $line[0];
        if (!in_array($productName, $tab)) {
            $subCategorieId= '';
            $entrepriseId = '';
            $inStock = $line[1];
            $description = $line[4];
            $urlImage = $line [14];
            foreach($subCategories as $subCategorie){
                if($line[3] === $subCategorie['name']){
                    $subCategorieId = $subCategorie['id'];
                }
            }
            foreach($entreprises as $entreprise){
                if($line[8] === $entreprise['name']){
                    $entrepriseId = $entreprise['id'];
                }
            }
            $productName = str_replace('"', '', $productName);
            $productName = str_replace("\\", '', $productName);
            $description = str_replace('"', '', $description);
            $description = str_replace("\\", '', $description);
            $sql = 'INSERT INTO produit (entreprise_id, name, in_stock, description, image_url, sub_categorie_id) VALUES ('.$entrepriseId.',"'.$productName.'","'.$inStock.'","'.$description.'","'.$urlImage.'",'.$subCategorieId.')';
                echo $compteur.' '.$sql;//
            $PDO->prepare($sql)->execute();
            $tab[] = $productName;
                $compteur++;//
        }
    }
}
//injectProduit($csv, $AgriProxiPDO, $entreprises, $subCategories);


/**
 * Injection des Appellations
 *
 * @param [type] $csv
 * @param [type] $PDO
 * @return void
 */
function injectAppellation($csv, $PDO){
    $tab = [];
    foreach ($csv as $line) {
        $appellation = $line[7];
        if (!in_array($appellation, $tab)) {
            $sql = 'INSERT INTO appellation (name) VALUES ("'.$appellation.'")';
            $PDO->prepare($sql)->execute();
            //var_dump ($appellation);
            $tab[] = $appellation;
        }
    }
}
//injectAppellation($csv, $AgriProxiPDO);

/**
 * Alimentation de la table d'association entre produit et appellation
 *
 * @param [type] $produits
 * @param [type] $appellations
 * @return void
 */
function injectProduitAppellation($csv, $produits, $appellations){

}
//injectProduitAppellation($csv, $produits, $appellations);