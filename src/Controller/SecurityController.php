<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", methods={"GET", "POST"}, name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // redirect authenticated users to homepage
            return $this->redirectToRoute('category_index');
        }

        //get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
