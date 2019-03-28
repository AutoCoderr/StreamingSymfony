<?php

namespace App\Controller;

use App\Entity\Commentaires;
use App\Entity\Episodes;
use App\Entity\Films;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireController extends AbstractController
{

    /**
     * @Route("/commentaires/edit", name="EditCommentaire")
     */
    public function editCommentaire(Request $request) {
        $id = $request->request->get("id");
        $content = $request->request->get("content");

        if ($content == null & $content == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Aucun contenu spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $em = $this->getDoctrine()->getEntityManager();
        $commentaireRepository = $em->getRepository(Commentaires::class);
        $commentaire = $commentaireRepository->findById($id);
        if (gettype($commentaire) == "array" & sizeof($commentaire) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Ce commentaire n'existe pas"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $commentaire = $commentaire[0];
        if ($commentaire->getUser()->getId() != $this->getUser()->getId()) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Ce commentaire n'est pas le votre"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        if ($this->getUser()->getPerm() != "moderateur") {
            $content = str_replace("<surlign>","",$content);
            $content = str_replace("</surlign>","",$content);
        }

        $commentaire->setContenu($content);
        $em->persist($commentaire);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }

    /**
     * @Route("/commentaires/suppr", name="SupprCommentaire")
     */
    function supprCommentaire(Request $request) {
        $id = $request->request->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $commentaireRepository = $em->getRepository(Commentaires::class);
        $commentaire = $commentaireRepository->findById($id);
        if (gettype($commentaire) == "array" & sizeof($commentaire) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Ce commentaire n'existe pas"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $commentaire = $commentaire[0];
        if (($commentaire->getUser()->getId() != $this->getUser()->getId()) &&
            (($this->getUser()->getPerm() != "moderateur" && $this->getUser()->getPerm() != "admin") |
            $commentaire->getUser()->getPerm() == "admin" | ($commentaire->getUser()->getPerm() == "moderateur" && $this->getUser()->getPerm() == "moderateur"))) {

            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Ce commentaire n'est pas le votre"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $em->remove($commentaire);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/commentaires/send", name="SendCommentaire")
     */
    public function sendCommentaire(Request $request) {
        $commentaireContenu = $request->request->get("commentaire");
        if ($commentaireContenu == null | $commentaireContenu == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Commentaire vide"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $idMetrage = $request->request->get("idMetrage");
        $typeMetrage = $request->request->get("typeMetrage");
        $surligned = $request->request->get("surlign");

        if ($this->getUser()->getPerm() != "moderateur" && $this->getUser()->getPerm() != "admin") {
            $commentaireContenu = str_replace("<surlign>","",$commentaireContenu);
            $commentaireContenu = str_replace("</surlign>","",$commentaireContenu);
        } else if ($surligned == "true") {
            $commentaireContenu = "<surlign>".$commentaireContenu."</surlign>";
        }

        $commentaire = new Commentaires();
        $commentaire->setContenu($commentaireContenu);
        $commentaire->setDateTime(new \DateTime(date("Y-m-d H:i")));
        $commentaire->setUser($this->getUser());
        $em = $this->getDoctrine()->getEntityManager();

        switch($typeMetrage) {
            case "film":
                $filmRepository = $em->getRepository(Films::class);
                $film = $filmRepository->findById($idMetrage);
                if (gettype($film) == "array" & sizeof($film) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Film non trouvé"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $commentaire->setFilm($film[0]);

                $em->persist($commentaire);
                $em->flush();

                $commentaireRepository = $em->getRepository(Commentaires::class);
                $lastComment = $commentaireRepository->getLastComment($this->getUser()->getId());
                if (gettype($lastComment) == "array" & sizeof($lastComment) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Commentaire uploadé mais non retrouvé"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $lastComment = $lastComment[0];

                $response = new Response(json_encode(["rep" => "success", "id" => $lastComment->getId()]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
                break;
            case "episode":
                $episodeRepository = $em->getRepository(Episodes::class);
                $episode = $episodeRepository->findById($idMetrage);
                if (gettype($episode) == "array" & sizeof($episode) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Episode non trouvé"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $episode = $episode[0];
                $commentaire->setEpisode($episode);

                $em->persist($commentaire);
                $em->flush();

                $commentaireRepository = $em->getRepository(Commentaires::class);
                $lastComment = $commentaireRepository->getLastComment($this->getUser()->getId());

                if (gettype($lastComment) == "array" & sizeof($lastComment) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Commentaire uploadé mais non retrouvé"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $lastComment = $lastComment[0];

                $response = new Response(json_encode(["rep" => "success", "id" => $lastComment->getId()]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
                break;
        }

        $response = new Response(json_encode(["rep" => "failed", "errors" => ["Aucun type trouvé"]]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
