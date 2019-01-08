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
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/category")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class CategoryAdminController extends AbstractController
{
    /**
     * @Route("/", name="category_admin_index", methods="GET")
     */
    public function index(CategoryRepository $categoryRepository): Response
    {
        $rootNode = $categoryRepository->findOneBy(['title' => 'app_root_category']);
        dump($rootNode);

        $categories = $categoryRepository->childrenHierarchy($rootNode);

        return $this->render('category_admin/index.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/new", name="category_admin_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('category_admin_index');
        }

        return $this->render('category_admin/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="category_admin_edit", methods="GET|POST")
     */
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('category_admin_index', ['id' => $category->getId()]);
        }

        return $this->render('category_admin/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="category_admin_delete", methods="DELETE")
     */
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();
        }

        return $this->redirectToRoute('category_admin_index');
    }

    /**
     * @Route("/{id}/move-up", name="category_move_up", methods="GET")
     */
    public function moveUp(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Category::class);

        $repository->moveUp($category, 1);

        return $this->redirectToRoute('category_admin_index');
    }

    /**
     * @Route("/{id}/move-down", name="category_move_down", methods="GET")
     */
    public function moveDown(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(Category::class);

        $repository->moveDown($category, 1);

        return $this->redirectToRoute('category_admin_index');
    }
}
