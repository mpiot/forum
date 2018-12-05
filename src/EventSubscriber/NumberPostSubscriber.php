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
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class NumberPostSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preRemove,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $post = $args->getObject();

        if (!$post instanceof Post) {
            return;
        }

        // When add a new Post, increase to 1 the number of posts
        $post->getThread()->increaseNumberPosts(1);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $post = $args->getObject();

        if (!$post instanceof Post) {
            return;
        }

        if (null !== $post->getThread()) {
            // When remove a Post, decrease to 1 the number of posts
            $post->getThread()->decreaseNumberPosts(1);
        }
    }
}
