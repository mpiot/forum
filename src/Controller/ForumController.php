<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class ForumController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @Entity("categories", class="App\Entity\Category", expr="repository.findAllSorted()")
     */
    public function index(array $categories)
    {
        return $this->render('forum/homepage.html.twig', [
            'categories' => $categories,
        ]);
    }
}
