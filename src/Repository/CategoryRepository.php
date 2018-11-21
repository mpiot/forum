<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findMainCategoriesWithSub()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.parent', 'parent')
            ->leftJoin('c.children', 'children')
                ->addSelect('children')
            ->where('c.parent is NULL')
            ->orderBy('c.position', 'ASC')
            ->addOrderBy('children.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSubCategory(int $id)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.parent', 'parent')
            ->where('c.id = :id')
                ->setParameter('id', $id)
            ->andWhere('c.parent is not NULL')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
