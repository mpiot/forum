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

    public function findLastActive(int $number = 1)
    {
        $query = $this->createQueryBuilder('thread')
            ->leftJoin('thread.lastPost', 'last_post')
            ->orderBy('last_post.createdAt', 'DESC')
            ->setMaxResults($number)
            ->getQuery();

        return $query->getResult();
    }

    private function createPaginator(Query $query, int $page): Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator->setMaxPerPage(Thread::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}
