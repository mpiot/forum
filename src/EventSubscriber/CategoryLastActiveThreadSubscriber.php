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
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class CategoryLastActiveThreadSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $post = $args->getObject();

        if (!$post instanceof Post) {
            return;
        }

        // Update the category lastActiveThread
        $thread = $post->getThread();
        $category = $post->getThread()->getCategory();

        $this->updateCategoryLastActiveThread($thread, $category);

        $args->getObjectManager()->flush();
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $thread = $args->getObject();

        if (!$thread instanceof Thread) {
            return;
        }

        // Update the category lastActiveThread
        $category = $thread->getCategory();

        // If the last thread as been removed, the lastActive thread is set on null
        if (null !== $category->getLastActiveThread()) {
            return;
        }

        // Retrieve the last active thread
        $lastActiveThread = $args->getObjectManager()->getRepository(Thread::class)->findLastActive(1);

        if (0 === \count($lastActiveThread)) {
            return;
        }

        // Verify there are 2 values in the array (the second is the before last)
        $this->updateCategoryLastActiveThread($lastActiveThread[0], $category, true);

        $args->getObjectManager()->flush();
    }

    private function updateCategoryLastActiveThread(?Thread $thread, Category $category, bool $deletion = false)
    {
        $category->setLastActiveThread($thread);
        $parentCategory = $category->getParent();

        // We need to rise up the tree, check if the previous last thread is older than the given (for deletion)
        if (null !== $parentCategory && (false === $deletion || (true === $deletion && null === $parentCategory->getLastActiveThread()))) {
            $this->updateCategoryLastActiveThread($thread, $parentCategory, $deletion);
        }
    }
}
