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
use App\Entity\Post;
use App\Entity\Thread;
use App\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/forum/{categorySlug}/{id}-{slug}/reply", name="thread_reply", methods="GET|POST")
     * @Entity("category", expr="repository.findOneBySlug(categorySlug)")
     * @Entity("thread", expr="repository.find(id)")
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Category $category, Thread $thread, Request $request): Response
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread->addPost($post);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('thread_show', [
                'categorySlug' => $category->getSlug(),
                'id' => $thread->getId(),
                'slug' => $thread->getSlug(),
            ]);
        }

        return $this->render('post/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/forum/{categorySlug}/{threadId}-{threadSlug}/edit-{id}", name="post_edit", methods="GET|POST")
     * @Entity("category", expr="repository.findOneBySlug(categorySlug)")
     * @Entity("thread", expr="repository.find(threadId)")
     * @Entity("post", expr="repository.find(id)")
     * @Security("is_granted('UPDATE', post)")
     */
    public function edit(Category $category, Thread $thread, Post $post, Request $request): Response
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);

            $em->flush();

            return $this->redirectToRoute('thread_show', [
                'categorySlug' => $category->getSlug(),
                'id' => $thread->getId(),
                'slug' => $thread->getSlug(),
            ]);
        }

        return $this->render('post/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/forum/{categorySlug}/{threadId}-{threadSlug}/delete-{id}", name="post_delete", methods="DELETE")
     * @Entity("category", expr="repository.findOneBySlug(categorySlug)")
     * @Entity("thread", expr="repository.find(threadId)")
     * @Entity("post", expr="repository.find(id)")
     * @Security("is_granted('DELETE', post)")
     */
    public function delete(Request $request, Category $category, Thread $thread, Post $post): Response
    {
        // The user can't delete a post if it's the first message, delete thread
        if ($post->isMainPost()) {
            $this->addFlash('danger', 'This post can\'t be deleted.');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $thread->removePost($post);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'The post as been successfully deleted.');
        }

        return $this->redirectToRoute('thread_show', [
            'categorySlug' => $category->getSlug(),
            'id' => $thread->getId(),
            'slug' => $thread->getSlug(),
        ]);
    }
}
