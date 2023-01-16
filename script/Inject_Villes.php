<?php

$AgriProxiPDO = new PDO( 'mysql:dbname=agriproxi;host=localhost', 'agriproxi', 'agriproxi');

$villes = $AgriProxiPDO->query('SELECT * FROM villes_france_free')->fetchAll(PDO::FETCH_ASSOC);
$departements = $AgriProxiPDO->query('SELECT * FROM departement')->fetchAll(PDO::FETCH_ASSOC);

foreach($villes as $ville) {
    $code = $ville['departement_code'];
    $name = $ville['name'];
    $departementId = '';
    foreach($departements as $departement) {
        if ($code === $departement['code']) {
            $departementId = $departement['id'];
        }
    }
    $sql = 'INSERT INTO ville (name, departement_id) VALUES ("'.$name.'", '.$departementId.')';
    $AgriProxiPDO->prepare($sql)->execute();
}
