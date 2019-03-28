<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="acceuil")
     */
    public function index()
    {
        if ($this->getUser() == null) {
            $me = "none";
        } else {
            $me = $this->getUser();
        }
        return $this->render('index.html.twig', [
            'me'       =>        $me
        ]);
    }
}
