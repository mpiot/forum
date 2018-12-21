<?php

namespace App\Serializer\Normalizer;

use Algolia\SearchBundle\Searchable;
use App\Entity\Post;
use App\Entity\Thread;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ThreadNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        $posts = [];
        /**
         * @var Post $post
         */
        foreach ($object->getPosts() as $post) {
            $posts[] = $post->getMessage();
        }

        return [
            'subject' => $object->getSubject(),
            'posts' => $posts,
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        // Support Thread and only Algolia Search format
         return $data instanceof Thread && Searchable::NORMALIZATION_FORMAT === $format;
    }
}
