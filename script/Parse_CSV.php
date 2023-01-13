<?php 
$AgriProxiPDO = new PDO( 'mysql:dbname=agriproxi;host=localhost', 'agriproxi', 'agriproxi');
$villes = $AgriProxiPDO->query('SELECT * FROM ville')->fetchAll(PDO::FETCH_ASSOC);
$entreprises = $AgriProxiPDO->query('SELECT * FROM entreprise')->fetchAll(PDO::FETCH_ASSOC);

$csv = '..\agriproxiRessources\produits-sud-de-france5.csv';

function read($csv) {
    $file = fopen($csv,'r');
    //for ($i = 0; $i < 100 ; $i ++) {
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
            var_dump($sql);
            $PDO->prepare($sql)->execute();
            $tab[] = $name;
        }
    }
}

injectAdresse($csv, $AgriProxiPDO, $villes, $entreprises);