<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ViewAllController extends AbstractController
{
    /**
     * @Route("/view/allEpisodes", name="viewAllEpisodes")
     */
    public function episodes(Request $request) {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error' => "Vous n'êtes pas connecté",
                'me'              => $this->getUser(),
            ]);
        }
        if ($this->getUser()->getPerm() != "admin" & $this->getUser()->getPerm() != "uploader") {
            return $this->render('error.html.twig', [
                'error'           => "Vous n'avez pas les permissions nessecaires",
                'me'              => $this->getUser(),
            ]);
        }
        $idUser = $request->query->get("idUser");
        if ($idUser == null | $idUser == "") {
            return $this->render('view_all/episodes.html.twig', [
                'me'        => $this->getUser(),
                'user'      => $this->getUser()
            ]);
        }
        if ($this->getUser()->getPerm() != "admin" & $idUser != $this->getUser()->getId()) {
            return $this->render('error.html.twig', [
                'error'           => "Vous devez être admin pour voir les métrages de quelqu'un d'autre",
                'me'              => $this->getUser(),
            ]);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $userRepository = $em->getRepository(Users::class);
        $user = $userRepository->findById($idUser);
        if (sizeof($user) == 0) {
            return $this->render('error.html.twig', [
                'error' => "Utilisateur non-existent",
                'me'              => $this->getUser(),
            ]);
        }
        $user = $user[0];

        return $this->render('view_all/episodes.html.twig', [
            'me'        => $this->getUser(),
            'user'       => $user
        ]);
    }

    /**
     * @Route("/view/allSaisons", name="viewAllSaisons")
     */
    public function saisons(Request $request) {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error' => "Vous n'êtes pas connecté",
                'me'              => $this->getUser(),
            ]);
        }
        $idUser = $request->query->get("idUser");
        if ($idUser == null | $idUser == "") {
            return $this->render('view_all/saisons.html.twig', [
                'me'        => $this->getUser(),
                'user'      => $this->getUser()
            ]);
        }
        $em = $this->getDoctrine()->getEntityManager();
        $userRepository = $em->getRepository(Users::class);
        $user = $userRepository->findById($idUser);
        if (sizeof($user) == 0) {
            return $this->render('error.html.twig', [
                'error' => "Utilisateur non-existent",
                'me'              => $this->getUser(),
            ]);
        }
        $user = $user[0];

        return $this->render('view_all/saisons.html.twig', [
            'me'        => $this->getUser(),
            'user'       => $user
        ]);
    }

    /**
     * @Route("/view/allSeries", name="viewAllSeries")
     */
    public function series(Request $request) {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error' => "Vous n'êtes pas connecté",
                'me'              => $this->getUser(),
            ]);
        }
        $em = $this->getDoctrine()->getEntityManager();
        $categorieRepository = $em->getRepository(Categories::class);
        $idUser = $request->query->get("idUser");
        if ($idUser == null | $idUser == "") {
            return $this->render('view_all/series.html.twig', [
                'me'        => $this->getUser(),
                'user'      => $this->getUser(),
                'categories' => $categorieRepository->findAll()
            ]);
        }
        $userRepository = $em->getRepository(Users::class);
        $user = $userRepository->findById($idUser);
        if (sizeof($user) == 0) {
            return $this->render('error.html.twig', [
                'error' => "Utilisateur non-existent",
                'me'              => $this->getUser(),
            ]);
        }
        $user = $user[0];

        return $this->render('view_all/series.html.twig', [
            'me'        => $this->getUser(),
            'user'       => $user,
            'categories' => $categorieRepository->findAll()
        ]);
    }

    /**
     * @Route("/view/allFilms", name="viewAllFilms")
     */
    public function films(Request $request) {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error' => "Vous n'êtes pas connecté",
                'me'              => $this->getUser(),
            ]);
        }
        $em = $this->getDoctrine()->getEntityManager();
        $categorieRepository = $em->getRepository(Categories::class);
        $idUser = $request->query->get("idUser");
        if ($idUser == null | $idUser == "") {
            return $this->render('view_all/films.html.twig', [
                'me'        => $this->getUser(),
                'user'      => $this->getUser(),
                'categories' => $categorieRepository->findAll()
            ]);
        }
        $userRepository = $em->getRepository(Users::class);
        $user = $userRepository->findById($idUser);
        if (sizeof($user) == 0) {
            return $this->render('error.html.twig', [
                'error' => "Utilisateur non-existent",
                'me'              => $this->getUser(),
            ]);
        }
        $user = $user[0];

        return $this->render('view_all/films.html.twig', [
            'me'        => $this->getUser(),
            'user'       => $user,
            'categories' => $categorieRepository->findAll()
        ]);
    }

    /**
     * @Route("/view/allSagas", name="viewAllSagas")
     */
    public function sagas(Request $request) {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error' => "Vous n'êtes pas connecté",
                'me'              => $this->getUser(),
            ]);
        }
        $em = $this->getDoctrine()->getEntityManager();
        $categorieRepository = $em->getRepository(Categories::class);
        $idUser = $request->query->get("idUser");
        if ($idUser == null | $idUser == "") {
            return $this->render('view_all/sagas.html.twig', [
                'me'        => $this->getUser(),
                'user'      => $this->getUser(),
                'categories' => $categorieRepository->findAll()
            ]);
        }
        $userRepository = $em->getRepository(Users::class);
        $user = $userRepository->findById($idUser);
        if (sizeof($user) == 0) {
            return $this->render('error.html.twig', [
                'error' => "Utilisateur non-existent",
                'me'              => $this->getUser(),
            ]);
        }
        $user = $user[0];

        return $this->render('view_all/sagas.html.twig', [
            'me'        => $this->getUser(),
            'user'       => $user,
            'categories' => $categorieRepository->findAll()
        ]);
    }
}
