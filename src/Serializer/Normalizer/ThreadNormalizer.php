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

namespace App\Serializer\Normalizer;

use Algolia\SearchBundle\Searchable;
use App\Entity\Post;
use App\Entity\Thread;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ThreadNormalizer implements NormalizerInterface
{
    private $purifier;

    public function __construct(\HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $thread = $object;

        $posts = [];
        if (0 !== $thread->getPosts()->count()) {
            /**
             * @var Post $post
             */
            foreach ($thread->getPosts() as $post) {
                $posts[] = $this->purifier->purify($post->getMessage());
            }
        } else {
            $posts[] = $this->purifier->purify($thread->getFirstPost()->getMessage());
        }

        return [
            'subject' => $thread->getSubject(),
            'slug' => $thread->getSlug(),
            'posts' => $posts,
            'categorySlug' => (string) $thread->getCategory()->getSlug(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        // Support Thread and only Algolia Search format
        return $data instanceof Thread && Searchable::NORMALIZATION_FORMAT === $format;
    }
}
