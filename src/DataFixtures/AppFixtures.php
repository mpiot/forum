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

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $categories = [
            [
                'title' => 'app_root_category',
                'children' => [
                    [
                        'title' => 'Site web',
                        'children' => [
                            ['title' => 'HTML/CSS'],
                            ['title' => 'Javascript'],
                            [
                                'title' => 'PHP',
                                'children' => [
                                    ['title' => 'Framework Symfony'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Programmation',
                        'children' => [
                            ['title' => 'Langage C'],
                            ['title' => 'Langage C++'],
                            ['title' => 'Langages.NET'],
                            ['title' => 'Langage Java'],
                            ['title' => 'Langage Python'],
                            ['title' => 'Base de données'],
                            ['title' => 'Mobile'],
                            ['title' => 'Autres langages (VBA, Ruby, ...)'],
                            ['title' => 'Discussions développement'],
                        ],
                    ],
                    [
                        'title' => 'Systèmes d\'exploitation',
                        'children' => [
                            ['title' => 'Windows'],
                            ['title' => 'Linux & FreeBSD'],
                            ['title' => 'Mac OS X'],
                        ],
                    ],
                ],
            ],
        ];

        $this->addCategories($categories, $manager);

        $manager->flush();
    }

    private function addCategories($categories, ObjectManager $manager, Category $parent = null)
    {
        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->setTitle($categoryData['title']);
            $category->setParent($parent);

            $manager->persist($category);

            if (array_key_exists('children', $categoryData)) {
                $this->addCategories($categoryData['children'], $manager, $category);
            }
        }
    }
}
