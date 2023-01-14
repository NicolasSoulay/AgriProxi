<?php 
$AgriProxiPDO = new PDO( 'mysql:dbname=agriproxi;host=localhost', 'root');
$villes = $AgriProxiPDO->query('SELECT * FROM ville')->fetchAll(PDO::FETCH_ASSOC);
$entreprises = $AgriProxiPDO->query('SELECT * FROM entreprise')->fetchAll(PDO::FETCH_ASSOC);
$categories = $AgriProxiPDO->query('SELECT * FROM categorie')->fetchAll(PDO::FETCH_ASSOC);
$subCategories = $AgriProxiPDO->query('SELECT * FROM sous_categorie')->fetchAll(PDO::FETCH_ASSOC);

$csv = '..\agriproxiRessources\produits-sud-de-france5.csv';

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

$csv = read($csv);

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

function injectProduit($csv, $PDO, $entreprises, $subCategories){
    $tab = [];
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
            //$productName = str_replace(`\`, '', $productName);
            $description = str_replace('"', '', $description);
            $sql = 'INSERT INTO produit (entreprise_id, name, in_stock, description, image_url, sub_categorie_id) VALUES ('.$entrepriseId.',"'.$productName.'","'.$inStock.'","'.$description.'","'.$urlImage.'",'.$subCategorieId.')';
            $PDO->prepare($sql)->execute();
            $tab[] = $productName;
        }
    }
}

injectProduit($csv, $AgriProxiPDO, $entreprises, $subCategories);