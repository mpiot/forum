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
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // Get entities scheduled for insertion
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            // Only for Post entities
            if ($entity instanceof Post) {
                $thread = $entity->getThread();

                // Call function to define lastActiveThread in Categories and parents
                $this->defineAsLastActiveThread($thread, $thread->getCategory(), $em, $uow);
            }
        }

        // Get entities scheduled for deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            // For Thread entities
            if ($entity instanceof Thread) {
                if ($entity->getId() === $entity->getCategory()->getLastActiveThread()->getId()) {
                    $previousLastThread = $em->getRepository(Thread::class)->findBeforeLastThreadForCategory($entity->getCategory());

                    // Call function to define the new lastActiveThread in Categories and parents
                    $this->defineAsLastActiveThread($previousLastThread, $entity->getCategory(), $em, $uow, $entity);
                }
            }

            // For Post entities
            if ($entity instanceof Post) {
                // If the post is in the lastActiveThread and is the lastPost of the Thread
                if (
                    $entity->getThread()->getCategory()->getLastActiveThread()->getId() === $entity->getThread()->getId()
                    && null !== $entity->getThread()->getPreviousLastPost()
                ) {
                    $previousLastPost = $entity->getThread()->getPreviousLastPost();
                    $previousLastThread = $em->getRepository(Thread::class)->findBeforeLastThreadForCategory($entity->getThread()->getCategory());

                    if ($previousLastThread->getLastPost()->getCreatedAt() < $previousLastPost->getCreatedAt()) {
                        // Then, we need to define the $previousLastThread as lastActiveThread for the Category
                        // And check when we rise up, the $category->getLastActiveThread() must match with $entity->getThread()
                        $this->defineAsLastActiveThread($previousLastThread, $entity->getThread()->getCategory(), $em, $uow, $entity->getThread());
                    }
                }
            }
        }

        // Get entities that are persisted
//        foreach ($uow->getScheduledEntityUpdates() as $entity) {
//            // Only on Post entity changes
//            if ($entity instanceof Post) {
//                // Get the changes
//                $changeSet = $uow->getEntityChangeSet($entity);
//                // Only when the activationCode change
//                if (array_key_exists('activationCode', $changeSet)) {
//                    // Then populate the new activation code
//                    $this->setActivationCode($entity);
//                    // And save it in database
//                    $classMetaData = $em->getClassMetadata(ActivationCode::class);
//                    $uow->computeChangeSet($classMetaData, $entity->getActivationCode());
//                }
//            }
//        }
    }

    private function defineAsLastActiveThread(Thread $thread, Category $category, EntityManagerInterface $em, UnitOfWork $uow, ?Thread $checkThread = null)
    {
        if (null === $checkThread ||
            $category->getLastActiveThread()->getId() === $checkThread->getId()
        ) {
            $category->setLastActiveThread($thread);

            // Save data
            $classMetaData = $em->getClassMetadata(Category::class);
            $uow->computeChangeSet($classMetaData, $category);

            // We need to rise up the tree, check if the previous last thread is older than the given (for deletion)
            if (null !== $parentCategory = $category->getParent()) {
                $this->defineAsLastActiveThread($thread, $parentCategory, $em, $uow, $checkThread);
            }
        }
    }
}
