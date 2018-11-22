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

    public function findMainCategoriesWithSub()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.parent', 'parent')
            ->leftJoin('c.children', 'children')
                ->addSelect('children')
            ->leftJoin('children.children', 'sub_children')
                ->addSelect('sub_children')
            ->where('c.level = 1')
            ->orderBy('c.left', 'ASC')
            ->addOrderBy('children.left', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSubCategory(int $id)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.parent', 'parent')
                ->addSelect('parent')
            ->leftJoin('c.children', 'children')
                ->addSelect('children')
            ->leftJoin('children.children', 'sub_children')
                ->addSelect('sub_children')
            ->where('c.id = :id')
                ->setParameter('id', $id)
            ->andWhere('c.level > 1')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
