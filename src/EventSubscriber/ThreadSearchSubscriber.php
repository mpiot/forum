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

use App\Entity\Post;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class ThreadSearchSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->indexThread($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->indexThread($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->indexThread($args, true);
    }

    private function indexThread(LifecycleEventArgs $args, bool $postRemoved = false)
    {
        $post = $args->getObject();

        if (!$post instanceof Post) {
            return;
        }

        if (true === $postRemoved && null === $post->getThread()) {
            return;
        }

        $thread = $post->getThread();
        $thread->setUpdatedAt(new \DateTime());

        $args->getObjectManager()->persist($thread);
        $args->getObjectManager()->flush();
    }
}
