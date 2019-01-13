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

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findLastUserPosts(int $userId, int $nbResults = 5)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(/* @lang DQL */
        'SELECT p, t, c
              FROM App\Entity\Post p
              INNER JOIN p.createdBy u
              INNER JOIN p.thread t
              INNER JOIN t.category c
              WHERE u.id = :userId
              ORDER BY p.createdAt DESC'
        )->setParameter('userId', $userId)
        ->setFirstResult(0)
        ->setMaxResults($nbResults);

        return $query->getResult();
    }

    public function countUserPosts(int $userId)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(/* @lang DQL */
            'SELECT COUNT(p)
            FROM App\Entity\Post p
            INNER JOIN p.createdBy u
            WHERE u.id = :userId'
        )->setParameter('userId', $userId);

        return $query->getSingleScalarResult();
    }

    public function findForShow(int $threadId, int $page = 1)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(/* @lang DQL */
        'SELECT p
              FROM App\Entity\Post p
              INNER JOIN p.thread t
              LEFT JOIN p.createdBy u
              WHERE t.id = :threadId
              ORDER BY p.createdAt ASC'
        )->setParameter('threadId', $threadId);

        return $this->createPaginator($query, $page);
    }

    private function createPaginator(Query $query, int $page): Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator->setMaxPerPage(Post::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}
