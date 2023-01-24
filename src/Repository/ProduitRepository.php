<?php

namespace App\Repository;

use App\Entity\Produit;
use App\Entity\SousCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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


    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
