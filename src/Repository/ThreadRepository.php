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
use App\Entity\User;
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

    public function findLastUserThreads(int $user, int $nbResults = 5)
    {
        $query = $this->createQueryBuilder('thread')
            ->innerJoin('thread.createdBy', 'user')
            ->innerJoin('thread.category', 'category')
                ->addSelect('category')
            ->where('user.id = :user')
            ->setParameter('user', $user)
            ->orderBy('thread.createdAt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->getQuery()
        ;

        return $query->getResult();
    }

    public function countUserThreads(int $user)
    {
        $query = $this->createQueryBuilder('thread')
            ->select('COUNT(thread)')
            ->innerJoin('thread.createdBy', 'user')
            ->where('user.id = :user')
            ->setParameter('user', $user)
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }

    public function findForCategoryShow(Category $category, int $page = 1)
    {
        $query = $this->createQueryBuilder('thread')
            ->leftJoin('thread.posts', 'posts')
                ->addSelect('posts')
            ->leftJoin('thread.category', 'category')
            ->leftJoin('thread.lastPost', 'last_post')
            ->orderBy('last_post.createdAt', 'DESC')
            ->where('category = :category')
                ->setParameter('category', $category)
            ->getQuery();

        return $this->createPaginator($query, $page);
    }

    public function findBeforeLastThreadForCategory(Category $category)
    {
        $query = $this->createQueryBuilder('thread')
            ->innerJoin('thread.category', 'category')
            ->innerJoin('thread.posts', 'posts')
            ->where('category = :category')
                ->setParameter('category', $category)
            ->orderBy('posts.createdAt', 'DESC')
            ->setFirstResult(1)
            ->setMaxResults(1)
            ->getQuery();

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
