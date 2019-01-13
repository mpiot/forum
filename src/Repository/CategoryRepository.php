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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends NestedTreeRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        $classMetaData = $em->getClassMetadata(Category::class);

        parent::__construct($em, $classMetaData);
    }

    public function findForCategoryIndex()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(/* @lang DQL */
            'SELECT c, children, sub_children, last_active_thread, last_active_thread_category, last_post, last_post_created_by
              FROM App\Entity\Category c
              INNER JOIN c.children children
              LEFT JOIN children.children sub_children
              LEFT JOIN children.lastActiveThread last_active_thread
              LEFT JOIN last_active_thread.category last_active_thread_category
              LEFT JOIN last_active_thread.lastPost last_post
              LEFT JOIN last_post.createdBy last_post_created_by
              WHERE c.level = 1
              ORDER BY c.left ASC, children.left ASC'
        );

        return $query->getResult();
    }

    public function findForCategoryShow(string $slug)
    {
        $category = $this->findOneBySlug($slug);

        if (null !== $category) {
            $id = $category->getId();

            $builder = $this->createQueryBuilder('category');
            $builder = $this->addAllParents($builder, $category->getLevel());

            $builder
                ->leftJoin('category.children', 'children')
                    ->addSelect('children')
                ->leftJoin('children.children', 'sub_children')
                    ->addSelect('sub_children')
                ->leftJoin('children.lastActiveThread', 'last_active_thread')
                    ->addSelect('last_active_thread')
                ->leftJoin('last_active_thread.category', 'last_active_thread_category')
                    ->addSelect('last_active_thread_category')
                ->leftJoin('last_active_thread.lastPost', 'last_post')
                    ->addSelect('last_post')
                ->leftJoin('last_post.createdBy', 'last_post_created_by')
                    ->addSelect('last_post_created_by')
                ->where('category.id = :id')
                ->setParameter('id', $id);

            $category = $builder->getQuery()->getOneOrNullResult();
        }

        return $category;
    }

    public function findForThreadShow(string $slug)
    {
        $category = $this->findOneBySlug($slug);

        if (null !== $category) {
            $id = $category->getId();

            $builder = $this->createQueryBuilder('category');
            $builder = $this->addAllParents($builder, $category->getLevel());

            $builder
                ->where('category.id = :id')
                ->setParameter('id', $id);

            $category = $builder->getQuery()->getOneOrNullResult();
        }

        return $category;
    }

    public function findForNewThread(string $slug)
    {
        $category = $this->findOneBySlug($slug);

        if (null !== $category) {
            $id = $category->getId();

            $builder = $this->createQueryBuilder('category');
            $builder = $this->addAllParents($builder, $category->getLevel());

            $builder
                ->leftJoin('category.children', 'children')
                ->addSelect('children')
                ->where('category.id = :id')
                ->setParameter('id', $id)
                ->andWhere('children IS NULL');

            $category = $builder->getQuery()->getOneOrNullResult();
        }

        return $category;
    }

    private function addAllParents(QueryBuilder $builder, int $categoryLevel): QueryBuilder
    {
        $builder
            ->leftJoin('category.parent', 'parent_0')
            ->addSelect('parent_0');

        for ($i = 1; $i < $categoryLevel; ++$i) {
            $builder
                ->leftJoin('parent_'.($i - 1).'.parent', 'parent_'.$i)
                ->addSelect('parent_'.$i);
        }

        return $builder;
    }
}
