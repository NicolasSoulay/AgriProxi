<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function save(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * retourne au tableau d'entitées Produit a partir d'un id de sous-categorie
     * 
     * @param string $subCategorieId
     * @return Produit[] 
     */
    public function findBySubCategorie(string $subCategorieId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.subCategorie = :val')
            ->setParameter('val', $subCategorieId)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * retourne au tableau d'entitées Produit a partir d'un id de categorie
     * 
     * @param string $categorieId
     * @return Produit[] Returns an array of Produit objects
     */
    public function findByCategorie(string $categorieId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.subCategorie', 'sc')
            ->join('sc.categorie', 'c')
            ->andWhere('c.id = :val')
            ->setParameter('val', $categorieId)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * la fonction aurais servi a recuperer des produits a partir de plusieurs critere de recherche, en assemblant une requete SQL native
     * pour ensuite retourner toutes les informations des produits au format Json
     * 
     * @param string $idCat
     * @param string $idSubCat
     * @param string $latitudeMax
     * @param string $longitudeMax
     * @param null|string $filtreName // null = pas selectioné, '1' = ASC sinon DESC
     * @param null|string $filtreEntreprise // null = pas selectioné, '1' = ASC sinon = DESC
     * @return string[] $produits
     */
    public function findByMultipleCriteria(mixed $idCat = null, mixed $idSubCat = null, mixed $latitudeMax = null, mixed $longitudeMax = null, mixed $filtreName = null, mixed $filtreEntreprise = null, mixed $filtreDistance = null)
    {
        $sql = "SELECT p.name, p.in_stock, p.description, p.image_url, e.name, e.description, a.label, a.complement, a.zip_code, a.latitude, a.longitude, v.name FROM produit p ";

        //ici on gere les critere de selection
        if ($idSubCat) {
            $sql .= "WHERE p.sub_categorie_id = $idSubCat ";
        }
        if ($idCat) {
            $sql .= "JOIN subCategorie sc ON p.sub_categorie_id = sc.id
                JOIN categorie c ON sc.categorie_id = c.id
                WHERE c.id = $idCat ";
        }

        //ici on fais les jointures necessaires
        $sql .= "JOIN entreprise e ON p.entreprise_id = e.id
            JOIN adresse a ON e.adresse_id = a.id
            JOIN ville v ON a.ville_id = v.id ";

        //ici on gere la distance
        if ($latitudeMax) {
            $sql .= "WHERE a.latitude < $latitudeMax AND a.longitude < $longitudeMax ";
        }

        //ici on gere le tri
        if ($filtreName) {
            switch ($filtreName) {
                case 1:
                    $sql .= "ORDER BY p.name ASC ";
                    break;
                default:
                    $sql .= "ORDER BY p.name DSC ";
            }
        }

        if ($filtreEntreprise) {
            switch ($filtreEntreprise) {
                case 1:
                    $sql .= "ORDER BY e.name ASC ";
                    break;
                default:
                    $sql .= "ORDER BY e.name DSC ";
            }
        }

        if ($filtreDistance) {
            switch ($filtreDistance) {
                case 1:
                    $sql .= "la formule magique pour trier les produits du plus proche au plus loin ";
                    break;
                default:
                    $sql .= "la formule magique pour trier les produits du plus loin au plus proche ";
            }
        }
        $sql += ";";
    }
}
