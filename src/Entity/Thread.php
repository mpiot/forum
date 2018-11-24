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

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThreadRepository")
 */
class Thread
{
    const NUM_ITEMS = 10;

    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @Gedmo\Slug(fields={"subject"})
     * @ORM\Column(length=128)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="threads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="integer")
     */
    private $numberPosts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="thread")
     */
    private $posts;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Post", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $firstPost;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Post")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $lastPost;

    public function __construct(Category $category)
    {
        $this->category = $category;
        $this->posts = new ArrayCollection();
        $this->numberPosts = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getNumberPosts(): ?int
    {
        return $this->numberPosts;
    }

    public function getNumberAnswers(): int
    {
        return $this->numberPosts - 1;
    }

    public function setNumberPosts(int $numberPosts): self
    {
        $this->numberPosts = $numberPosts;

        return $this;
    }

    public function increaseNumberPosts(int $number): self
    {
        $this->numberPosts += $number;

        return $this;
    }

    public function decreaseNumberPosts(int $number): self
    {
        $this->numberPosts -= $number;

        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getFirstPost(): ?Post
    {
        return $this->firstPost;
    }

    public function setFirstPost(?Post $post): self
    {
        $this->firstPost = $post;

        return $this;
    }

    public function getLastPost(): ?Post
    {
        return $this->lastPost;
    }

    public function setLastPost(?Post $post): self
    {
        $this->lastPost = $post;

        return $this;
    }
}
