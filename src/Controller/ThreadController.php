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
use App\Entity\Thread;
use App\Form\ThreadEditType;
use App\Form\ThreadType;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThreadController extends AbstractController
{
    /**
     * @Route("/forum/{slug}/new", name="thread_new", methods="GET|POST")
     * @Entity("category", expr="repository.findForNewThread(slug)")
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Category $category, Request $request): Response
    {
        $thread = new Thread($category);

        $form = $this->createForm(ThreadType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($thread);

            $em->flush();

            return $this->redirectToRoute('thread_show', [
                'categorySlug' => $category->getSlug(),
                'id' => $thread->getId(),
                'slug' => $thread->getSlug(),
            ]);
        }

        return $this->render('thread/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/forum/{categorySlug}/{id}-{slug}/{page}", requirements={"page"="\d+"}, defaults={"page"="1"}, name="thread_show", methods="GET")
     * @Entity("category", expr="repository.findForThreadShow(categorySlug)")
     * @Entity("thread", class="App\Entity\Thread", expr="repository.find(id)")
     * @Entity("posts", class="App\Entity\Post", expr="repository.findForShow(id, page)")
     */
    public function show(Category $category, Thread $thread, Pagerfanta $posts): Response
    {
        return $this->render('thread/show.html.twig', [
            'category' => $category,
            'thread' => $thread,
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/forum/{categorySlug}/{id}-{slug}/edit", name="thread_edit", methods="GET|POST")
     * @Entity("category", expr="repository.findForNewThread(categorySlug)")
     * @Security("is_granted('UPDATE', thread)")
     */
    public function edit(Category $category, Thread $thread, Request $request): Response
    {
        $form = $this->createForm(ThreadEditType::class, $thread);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('thread_show', [
                'categorySlug' => $category->getSlug(),
                'id' => $thread->getId(),
                'slug' => $thread->getSlug(),
            ]);
        }

        return $this->render('thread/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/forum/{categorySlug}/{id}", name="thread_delete", methods="DELETE")
     * @Security("is_granted('DELETE', thread)")
     */
    public function delete(Request $request, Thread $thread): Response
    {
        if ($this->isCsrfTokenValid('delete'.$thread->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($thread);
            $em->flush();
        }

        return $this->redirectToRoute('category_index');
    }
}
