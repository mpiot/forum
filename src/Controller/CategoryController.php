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

namespace App\Controller;

use App\Entity\Category;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="category_index")
     * @Entity("categories", class="App\Entity\Category", expr="repository.findForCategoryIndex()")
     */
    public function index(array $categories): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/forum/{slug}/{page}", requirements={"page"="\d+"}, defaults={"page"="1"}, name="category_show", methods="GET")
     * @Entity("category", class="App\Entity\Category", expr="repository.findForCategoryShow(slug)")
     * @Entity("threads", class="App\Entity\Thread", expr="repository.findForCategoryShow(category, page)")
     */
    public function show(Category $category, Pagerfanta $threads): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
            'threads' => $threads,
        ]);
    }
}
