<?php 

//Configuration de la connexion à la BDD
$AgriProxiPDO = new PDO( 'mysql:dbname=agriproxi;host=localhost', 'root', 'root');

//Récupération des produits de la BDD
$produits = $AgriProxiPDO->query('SELECT id, image_url FROM produit')->fetchAll(PDO::FETCH_ASSOC);

//Récupération des toutes les images de la table produit;
function injectImage($produits, $Pdo){
    for($i = 0; $i < count($produits); $i++){
        $urlImageProduit = $produits[$i]["image_url"];
        $idProduit = $produits[$i]["id"];
        $fileName = basename($urlImageProduit.'.png');
        if($urlImageProduit){
            file_put_contents($fileName, file_get_contents($urlImageProduit));
            echo "Fichier téléchargé avec succès"; 
            echo PHP_EOL;
        }     
        else{ 
            echo "Le téléchargement n'a pas pu être effectué"; 
            continue;
        }
        $fileName = '/uploads/photos/'.$fileName;
        $sql="UPDATE produit SET image_url = '$fileName' WHERE id = $idProduit";
        $Pdo->prepare($sql)->execute();
    }
}

injectImage($produits, $AgriProxiPDO);