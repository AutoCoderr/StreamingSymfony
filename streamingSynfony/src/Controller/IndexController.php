<?php

namespace App\Controller;

use App\Entity\Films;
use App\Entity\Series;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="acceuil")
     */
    public function index(Request $request)
    {
        if ($this->getUser() == null) {
            $me = "none";
        } else {
            $me = $this->getUser();
        }

        $page = $request->query->get("page");
        if ($page == null) {
            $page = 0;
        }
        if(!is_numeric($page)) {
            return $this->render('error.html.twig', [
                'me'       =>    $me,
                'error'    =>    "'page' doit être un nombre"
            ]);
        }
        $filtre = $request->query->get("filtre");
        if ($filtre == null) {
            $filtre = "pop";
        } else if ($filtre != "pop" & $filtre != "popinv") {
            return $this->render('error.html.twig', [
                'me'       =>    $me,
                'error'    =>    "Le filtre n'est pas valide"
            ]);
        }
        $word = $request->query->get("word");
        if ($word == null) {
            $word = "";
        }
        $em = $this->getDoctrine()->getEntityManager();
        $filmRepository = $em->getRepository(Films::class);
        $serieRepository = $em->getRepository(Series::class);
        return $this->render('index.html.twig', [
            'me'       =>        $me,
            'series' =>  $serieRepository->findAll(),
            'films' =>  $filmRepository->findAll(),
            'page'  => $page,
            'filtre' => $filtre,
            'word'   => $word
        ]);
    }
}
