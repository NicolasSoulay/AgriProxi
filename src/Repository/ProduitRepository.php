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
     * @return Produit[] Returns an array of Produit objects
     */
    public function findBySubCategorie($subCategorie): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.subCategorie = :val')
            ->setParameter('val', $subCategorie)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Produit[] Returns an array of Produit objects
     */
    public function findByCategorie($categorie): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.subCategorie', 'sc')
            ->join('sc.categorie', 'c')
            ->andWhere('c.id = :val')
            ->setParameter('val', $categorie)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Produit[] Returns an array of Produit objects
     */
    public function findByEntrepriseId($entrepriseId)
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.ligneDevis', 'ligneDevis')
            ->join('ligneDevis.devis', 'd')
            ->join('d.entreprise', 'e')
            ->where('e.id = :entrepriseId')
            ->orderBy('e.id', 'ASC')
            ->setParameter('entrepriseId', $entrepriseId)
            ->getQuery();
        return $query->getResult();
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
