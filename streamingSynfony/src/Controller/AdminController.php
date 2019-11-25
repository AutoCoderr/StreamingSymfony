<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/viewInfos", name="adminViewInfos")
     */
    public function viewInfos(Request $request) {
        if ($this->getUser() == null) {
            return $this->redirectToRoute("login");
        } else if ($this->getUser()->getPerm() != "admin") {
            return $this->redirectToRoute("acceuil");
        }

        $id = $request->query->get("id");
        if ($id == null | $id == "") {
            return $this->render('error.html.twig', [
                'error' => "Id non spécifié",
                'me'              => $this->getUser(),
            ]);
        }
        $em = $this->getDoctrine()->getEntityManager();
        $userRepository = $em->getRepository(Users::class);
        $user = $userRepository->findById($id);
        if (sizeof($user) == 0) {
            return $this->render('error.html.twig', [
                'error' => "Cet utilisateur n'existe pas",
                'me'              => $this->getUser(),
            ]);
        }
        $user = $user[0];

        if ($this->getUser()->getId() != $user->getId() & ($this->getUser()->getPerm() != "admin" | $user->getPerm() == "admin")) {
            return $this->render('error.html.twig', [
                'error' => "Vous n'avez pas les permissions pour accéder à ses infos",
                'me'              => $this->getUser(),
            ]);
        }
        return $this->render('admin/viewInfos.html.twig', [
            'controller_name' => 'AdminController',
            'me'              => $this->getUser(),
            'user'           => $user
        ]);
    }

    /**
     * @Route("/admin/userList", name="adminUserList")
     */
    public function userList()
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute("login");
        } else if ($this->getUser()->getPerm() != "moderateur" & $this->getUser()->getPerm() != "admin") {
            return $this->redirectToRoute("acceuil");
        }
        $em = $this->getDoctrine()->getEntityManager();
        $userRepository = $em->getRepository(Users::class);
        return $this->render('admin/userList.html.twig', [
            'controller_name' => 'AdminController',
            'me'              => $this->getUser(),
            'users'           => $userRepository->findAll()
        ]);
    }

    /**
     * @Route("/admin/banUnBan", name="adminBanUnBan")
     */
    public function banUnBan(Request $request) {
        $id = $request->request->get("id");
        if ($id == null | $id == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Id non spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $val = $request->request->get("val");
        if ($val == null | $val == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Valeur non spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $userToCedeId = $request->request->get("userToCedeId");
        if ($userToCedeId == null) {
            $userToCedeId = "";
        }

        $em = $this->getDoctrine()->getEntityManager();
        $userRepository = $em->getRepository(Users::class);
        $user = $userRepository->findById($id);
        if (sizeof($user) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Utilisateur non existant"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $user = $user[0];

        if ($this->getUser()->getPerm() != "moderateur" & $this->getUser()->getPerm() != "admin") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécessaires"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        if ($user->getPerm() == "admin" | ($user->getPerm() == "moderateur"  & $this->getUser()->getPerm() == "moderateur")) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne pouvez pas bannir/de-bannir cet utilisateur"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $user->setBanned((($val == "1") ? true : false));

        $em->persist($user);

        if ($userToCedeId != "" & $val == "1") {
            $userToCede = $userRepository->findById($userToCedeId);
            if (sizeof($userToCede) == 0) {
                $response = new Response(json_encode(["rep" => "failed", "errors" => ["L'utilisateur à qui vous souhaitez céder les métrages n'existe pas"]]));
                $response->headers->set('Content-Type', 'application/json');
                $em->flush();
                return $response;
            }
            $userToCede = $userToCede[0];
            if ($userToCede->getId() == $user->getId()) {
                $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne pouvez pas céder ses métrages à lui même"]]));
                $response->headers->set('Content-Type', 'application/json');
                $em->flush();
                return $response;
            }
            if (($userToCede->getPerm() != "admin" & $userToCede->getPerm() != "uploader") | $userToCede->getBanned() == 1) {
                $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous pouvez pas céder ses métrages cette personne"]]));
                $response->headers->set('Content-Type', 'application/json');
                $em->flush();
                return $response;
            }

            foreach ($user->getFilms() as $film) {
                $user->removeFilm($film);
                $film->setUser($userToCede);
            }
            foreach ($user->getSagas() as $saga) {
                $user->removeSaga($saga);
                $saga->setUser($userToCede);
            }
            foreach ($user->getEpisodes() as $episode) {
                $user->removeEpisode($episode);
                $episode->setUser($userToCede);
            }
            foreach ($user->getSaisons() as $saison) {
                $user->removeSaison($saison);
                $saison->setUser($userToCede);
            }
            foreach ($user->getSeries() as $serie) {
                $user->removeSeries($serie);
                $serie->setUser($userToCede);
            }
        }

        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
