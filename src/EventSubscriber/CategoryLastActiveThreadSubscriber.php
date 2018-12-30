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

namespace App\EventSubscriber;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Thread;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;

class CategoryLastActiveThreadSubscriber implements EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UnitOfWork
     */
    private $uow;

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $args->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();

        // Get entities scheduled for insertion
        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            // Only for Post entities
            if ($entity instanceof Post) {
                $thread = $entity->getThread();
                $category = $thread->getCategory();

                $this->addLastActiveThread($thread, $category);
            }
        }

        // Get entities scheduled for update
        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            // Only on Thread entity changes
            if ($entity instanceof Thread) {
                // Get the changes
                $changeSet = $this->uow->getEntityChangeSet($entity);

                // Only when the category change
                if (array_key_exists('category', $changeSet)) {
                    $previousCategory = $changeSet['category'][0];
                    $newCategory = $changeSet['category'][1];

                    // Remove the thread for the previous Category
                    $this->removeLastActiveThread($entity, $previousCategory);

                    // Add the thread to the new Category
                    $this->addLastActiveThread($entity, $newCategory, true);
                }
            }
        }

        // Get entities scheduled for deletions
        foreach ($this->uow->getScheduledEntityDeletions() as $entity) {
            // For Thread entities
            if ($entity instanceof Thread) {
                $category = $entity->getCategory();

                $this->removeLastActiveThread($entity, $category);
            }

            // For Post entities
            if ($entity instanceof Post) {
                $thread = $entity->getThread();
                $category = $thread->getCategory();

                $this->removeLastActiveThread($thread, $category, true);
            }
        }
    }

    private function addLastActiveThread(Thread $thread, Category $category, bool $check = false)
    {
        if (false === $check ||
            (null === $category->getLastActiveThread() || $thread->getLastPost()->getCreatedAt() > $category->getLastActiveThread()->getLastPost()->getCreatedAt())
        ) {
            $this->setAsLastActiveThread($thread, $category, $category->getLastActiveThread());
        }
    }

    private function removeLastActiveThread(Thread $thread, Category $category, bool $check = false)
    {
        // Check if the category last thread is the same than the deleted thread
        if ($category->getLastActiveThread()->getId() === $thread->getId()) {
            // Get the before last thread for the Category
            $previousLastThread = $this->em->getRepository(Thread::class)->findBeforeLastThreadForCategory($category);

            // In some case, when the last post of a Thread is deleted, we need to check if the last post from
            // the previousLastThread is newer than the previous last post from the actual Thread
            if (false === $check ||
                (null !== $thread->getPreviousLastPost() && $previousLastThread->getLastPost()->getCreatedAt() < $thread->getPreviousLastPost()->getCreatedAt())
            ) {
                $this->setAsLastActiveThread($previousLastThread, $category, $thread);
            }
        }
    }

    private function setAsLastActiveThread(?Thread $thread, Category $category, ?Thread $checkThread)
    {
        if (null === $checkThread ||
            null !==$category->getLastActiveThread() && $category->getLastActiveThread()->getId() === $checkThread->getId()
        ) {
            $category->setLastActiveThread($thread);

            // Save data
            $classMetaData = $this->em->getClassMetadata(Category::class);
            $this->uow->computeChangeSet($classMetaData, $category);

            // We need to rise up the tree, check if the previous last thread is older than the given (for deletion)
            if (null !== $parentCategory = $category->getParent()) {
                $this->setAsLastActiveThread($thread, $parentCategory, $checkThread);
            }
        }
    }
}
