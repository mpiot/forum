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

use Algolia\SearchBundle\IndexManagerInterface;
use App\Entity\Thread;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="quick_search")
     */
    public function quickSearch(Request $request, IndexManagerInterface $index): Response
    {
        $query = $request->get('query');

        if (null !== $query) {
            $results = $index->rawSearch($query, Thread::class);
        } else {
            $results = null;
        }

        return $this->render('search/quick_search.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
