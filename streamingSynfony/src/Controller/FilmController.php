<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Commentaires;
use App\Entity\Films;
use App\Entity\Sagas;
use App\Entity\URLs;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class FilmController extends AbstractController
{
    /**
     * @Route("/films", name="films")
     */
    public function films(Request $request)
    {
        $idUploader = $request->query->get("idUploader");
        if ($idUploader == null) {
            $idUploader = "";
        }
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
        $categorieRepository = $em->getRepository(Categories::class);
        $sagaRepository = $em->getRepository(Sagas::class);
        $userRepository = $em->getRepository(Users::class);

        return $this->render('films/films.html.twig', [
            'controller_name' => 'FilmController',
            'films'           => $filmRepository->findWithoutSaga(),
            'me'              =>                $me,
            'categories'      => $categorieRepository->findAll(),
            'sagas'           => $sagaRepository->findAll(),
            'users'           => $userRepository->findAll(),
            'idUploader'      => $idUploader,
            'filtre'          => $filtre,
            'page'            => $page,
            'word'            => $word
        ]);
    }

    /**
     * @Route("/films/deleteFilm", name="deleteFilm")
     */
    public function deleteFilm(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $id = $request->request->get("id");

        $em = $this->getDoctrine()->getEntityManager();
        $filmRepository = $em->getRepository(Films::class);

        $film = $filmRepository->findbyId($id);
        if (sizeof($film) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Film inexistant"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $film = $film[0];

        if ($film->getUser()->getId() != $this->getUser()->getId() & ($this->getUser()->getPerm() != "admin" | $film->getUser()->getPerm() == "admin")) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'avez pas le droit de supprimer ce film"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $urlRepository = $em->getRepository(URLs::class);

        $urls = $urlRepository->findByFilmId($id);

        foreach ($urls as $url) {
            $em->remove($url);
        }

        $commentaireRepository = $em->getRepository(Commentaires::class);

        $commentaires = $commentaireRepository->findByFilmId($id);

        foreach ($commentaires as $commentaire) {
            $em->remove($commentaire);
        }
        unlink ("/root/projects/streamingWebSite/streamingSynfony/public".$film->getImage());

        $em->remove($film);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/films/deleteSaga", name="deleteSaga")
     */
    public function deleteSaga(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $id = $request->request->get("id");

        $em = $this->getDoctrine()->getEntityManager();
        $sagaRepository = $em->getRepository(Sagas::class);

        $saga = $sagaRepository->findbyId($id);
        if (sizeof($saga) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Saga inexistante"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $saga = $saga[0];
        if ($saga->getUser()->getId() != $this->getUser()->getId() & ($this->getUser()->getPerm() != "admin" | $saga->getUser()->getPerm() == "admin")) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'avez pas le droit de supprimer cette saga"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $commentaireRepository = $em->getRepository(Commentaires::class);
        $urlRepository = $em->getRepository(URLs::class);
        $filmRepository = $em->getRepository(Films::class);
        $films = $filmRepository->findBySagaId($id);
        foreach ($films as $film) {
            $urls = $urlRepository->findByFilmId($film->getId());
            foreach ($urls as $url) {
                $em->remove($url);
            }
            $commentaires = $commentaireRepository->findByFilmId($film->getId());
            foreach ($commentaires as $commentaire) {
                $em->remove($commentaire);
            }
            $em->remove($film);
            unlink ("/root/projects/streamingWebSite/streamingSynfony/public".$film->getImage());
        }
        unlink ("/root/projects/streamingWebSite/streamingSynfony/public".$saga->getImage());

        $em->remove($saga);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/films/view", name="filmsView")
     */
    public function view(Request $request)
    {
        $id = $request->query->get("id");

        $em = $this->getDoctrine()->getEntityManager();
        $filmRepository = $em->getRepository(Films::class);
        $film = $filmRepository->findbyId($id);
        if (sizeof($film) == 0) {
            return new Response(
                '<center><h1 style="color: red;">Ce film n\'existe pas</h1></center>'
            );
        }
        $film = $film[0];

        if ($this->getUser() == null) {
            $me = "none";
        } else {
            $me = $this->getUser();
        }

        return $this->render('films/view.html.twig', [
            'controller_name' => 'FilmController',
            'film'           =>               $film,
            'me'              =>                $me

        ]);
    }

    /**
     * @Route("/films/viewSaga", name="sagaView")
     */
    public function viewSaga(Request $request) {
        $idUploader = $request->query->get("idUploader");
        if ($idUploader == null) {
            $idUploader = "";
        }

        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $sagaRepository = $em->getRepository(Sagas::class);
        $categorieRepository = $em->getRepository(Categories::class);
        $userRepository = $em->getRepository(Users::class);
        $saga = $sagaRepository->findById($id);
        if (sizeof($saga) == 0) {
            return new Response(
                '<center><h1 style="color: red;">Cette saga n\'existe pas</h1></center>'
            );
        }
        $saga = $saga[0];

        if ($this->getUser() == null) {
            $me = "none";
        } else {
            $me = $this->getUser();
        }

        return $this->render('films/viewSaga.html.twig', [
            'controller_name' => 'FilmController',
            'saga'      =>       $saga,
            'me'        =>       $me,
            'categories'      => $categorieRepository->findAll(),
            'users'           => $userRepository->findAll(),
            'idUploader'      => $idUploader
        ]);
    }

    /**
     * @Route("/films/addView", name="filmAddView")
     */
    function addView(Request $request) {
        $id = $request->request->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $filmRepository = $em->getRepository(Films::class);
        $film = $filmRepository->findById($id);
        if (sizeof($film) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Ce film n'existe pas."]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $film = $film[0];
        $film->setVues($film->getVues()+1);
        $em->persist($film);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


}
