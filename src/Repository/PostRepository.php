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

    public function findLastUserPosts(int $user, int $nbResults = 5)
    {
        $query = $this->createQueryBuilder('post')
            ->innerJoin('post.createdBy', 'user')
            ->innerJoin('post.thread', 'thread')
                ->addSelect('thread')
            ->innerJoin('thread.category', 'category')
                ->addSelect('category')
            ->where('user.id = :user')
            ->setParameter('user', $user)
            ->orderBy('post.createdAt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->getQuery()
        ;

        return $query->getResult();
    }

    public function countUserPosts(int $user)
    {
        $query = $this->createQueryBuilder('post')
            ->select('COUNT(post)')
            ->innerJoin('post.createdBy', 'user')
            ->where('user.id = :user')
            ->setParameter('user', $user)
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }

    public function findForShow(int $id, int $page = 1)
    {
        $query = $this->createQueryBuilder('post')
            ->leftJoin('post.thread', 'thread')
            ->leftJoin('post.createdBy', 'post_created_by')
                ->addSelect('post_created_by')
            ->where('thread.id = :id')
                ->setParameter('id', $id)
            ->orderBy('post.createdAt', 'ASC')
            ->getQuery();

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
