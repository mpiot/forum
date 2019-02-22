<?php

/*
 * Copyright 2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Thread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Thread|null find($id, $lockMode = null, $lockVersion = null)
 * @method Thread|null findOneBy(array $criteria, array $orderBy = null)
 * @method Thread[]    findAll()
 * @method Thread[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function findLastUserThreads(int $userId, int $nbResults = 5)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(/* @lang DQL */
        'SELECT t, c
              FROM App\Entity\Thread t
              INNER JOIN t.createdBy u
              INNER JOIN t.category c
              WHERE u.id = :userId
              ORDER BY t.createdAt DESC'
        )
        ->setParameter('userId', $userId)
        ->setFirstResult(0)
        ->setMaxResults($nbResults);

        return $query->getResult();
    }

    public function countUserThreads(int $userId)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(/* @lang DQL */
        'SELECT COUNT(t)
              FROM App\Entity\Thread t
              INNER JOIN t.createdBy u
              WHERE u.id = :userId'
        )->setParameter('userId', $userId);

        return $query->getSingleScalarResult();
    }

    public function findForCategoryShow(Category $category, int $page = 1)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(/* @lang DQL */
        'SELECT t, p
              FROM App\Entity\Thread t
              INNER JOIN t.posts p
              INNER JOIN t.category c
              INNER JOIN t.lastPost lastPost
              WHERE c = :category
              ORDER BY lastPost.createdAt DESC'
        )->setParameter('category', $category);

        return $this->createPaginator($query, $page);
    }

    public function findBeforeLastThreadForCategory(Category $category)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(/* @lang DQL */
            'SELECT t, c
              FROM App\Entity\Thread t
              INNER JOIN t.category c
              INNER JOIN t.posts p
              WHERE c = :category
              ORDER BY p.createdAt DESC'
        )
        ->setParameter('category', $category)
        ->setFirstResult(1)
        ->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    private function createPaginator(Query $query, int $page): Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator->setMaxPerPage(Thread::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}
