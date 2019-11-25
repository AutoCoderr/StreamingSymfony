<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Commentaires;
use App\Entity\Episodes;
use App\Entity\Saisons;
use App\Entity\Series;
use App\Entity\URLs;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SerieController extends AbstractController
{

    /**
     * @Route("/series/deleteEpisode", name="deleteEpisode")
     */
    public function deleteEpisode(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $id = $request->request->get("id");

        $em = $this->getDoctrine()->getEntityManager();
        $episodeRepository = $em->getRepository(Episodes::class);

        $episode = $episodeRepository->findById($id);
        if (sizeof($episode) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Episode inexistant"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $episode = $episode[0];

        if ($episode->getUser()->getId() != $this->getUser()->getId() & ($this->getUser()->getPerm() != "admin" | $episode->getUser()->getPerm() == "admin")) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'avez pas le droit de supprimer cet episode"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $urlRepository = $em->getRepository(URLs::class);

        $urls = $urlRepository->findByEpisodeId($id);

        foreach ($urls as $url) {
            $em->remove($url);
        }

        $commentaireRepository = $em->getRepository(Commentaires::class);

        $commentaires = $commentaireRepository->findByEpisodeId($id);
        foreach ($commentaires as $commentaire) {
            $em->remove($commentaire);
        }

        $em->remove($episode);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/series/deleteSaison", name="deleteSaison")
     */
    public function deleteSaison(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $id = $request->request->get("id");

        $em = $this->getDoctrine()->getEntityManager();
        $saisonRepository = $em->getRepository(Saisons::class);

        $saison = $saisonRepository->findById($id);
        if (sizeof($saison) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Saison inexistante"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $saison = $saison[0];
        if ($saison->getUser()->getId() != $this->getUser()->getId() & ($this->getUser()->getPerm() != "admin" | $saison->getUser()->getPerm() == "admin")) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'avez pas le droit de supprimer cette saison"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $commentaireRepository = $em->getRepository(Commentaires::class);
        $urlRepository = $em->getRepository(URLs::class);
        $episodeRepository = $em->getRepository(Episodes::class);
        $episodes = $episodeRepository->findBySaisonId($id);
        foreach ($episodes as $episode) {
            $urls = $urlRepository->findByEpisodeId($episode->getId());
            foreach ($urls as $url) {
                $em->remove($url);
            }
            $commentaires = $commentaireRepository->findByEpisodeId($episode->getId());
            foreach ($commentaires as $commentaire) {
                $em->remove($commentaire);
            }
            $em->remove($episode);
        }
        unlink ("/root/projects/streamingWebSite/streamingSynfony/public".$saison->getImage());
        $em->remove($saison);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/series/deleteSerie", name="deleteSerie")
     */
    public function deleteSerie(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $id = $request->request->get("id");

        $em = $this->getDoctrine()->getEntityManager();
        $serieRepository = $em->getRepository(Series::class);

        $serie = $serieRepository->findById($id);
        if (sizeof($serie) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Saison inexistante"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $serie = $serie[0];
        if ($serie->getUser()->getId() != $this->getUser()->getId() & ($this->getUser()->getPerm() != "admin" | $serie->getUser()->getPerm() == "admin")) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'avez pas le droit de supprimer cette serie"]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }


        $commentaireRepository = $em->getRepository(Commentaires::class);
        $urlRepository = $em->getRepository(URLs::class);
        $episodeRepository = $em->getRepository(Episodes::class);
        $saisonRepository = $em->getRepository(Saisons::class);
        $saisons = $saisonRepository->findBySerieId($id);
        foreach ($saisons as $saison) {
            $episodes = $episodeRepository->findBySaisonId($saison->getId());
            foreach ($episodes as $episode) {
                $urls = $urlRepository->findByEpisodeId($episode->getId());
                foreach ($urls as $url) {
                    $em->remove($url);
                }
                $commentaires = $commentaireRepository->findByEpisodeId($episode->getId());
                foreach ($commentaires as $commentaire) {
                    $em->remove($commentaire);
                }
                $em->remove($episode);
            }
            $em->remove($saison);
            unlink ("/root/projects/streamingWebSite/streamingSynfony/public".$saison->getImage());
        }
        unlink ("/root/projects/streamingWebSite/streamingSynfony/public".$serie->getImage());
        $em->remove($serie);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/series/viewEpisode", name="serieViewEpisode")
     */
    public function viewEpisode(Request $request) {
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $episodeRepository = $em->getRepository(Episodes::class);
        $episode = $episodeRepository->findById($id);
        if (gettype($episode) == "array" & sizeof($episode) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Episode non existant"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $episode = $episode[0];

        if ($this->getUser() == null) {
            $me = "none";
        } else {
            $me = $this->getUser();
        }

        return $this->render('series/viewEpisode.html.twig', [
            'controller_name' => 'SerieController',
            'me'              =>                $me,
            'episode'          =>             $episode
        ]);
    }

    /**
     * @Route("/series/saisons/episodes", name="episodes")
     */
    public function episodes(Request $request) {
        $idUploader = $request->query->get("idUploader");
        if ($idUploader == null) {
            $idUploader = "";
        }
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $saisonRepository = $em->getRepository(Saisons::class);
        $saison = $saisonRepository->findById($id);
        $userRepository = $em->getRepository(Users::class);
        if (gettype($saison) == "array" & sizeof($saison) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Saison non existante"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $saison = $saison[0];
        if ($this->getUser() == null) {
            $me = "none";
        } else {
            $me = $this->getUser();
        }

        return $this->render('series/episodes.html.twig', [
            'controller_name' => 'SerieController',
            'me'              =>                $me,
            'saison'          =>                 $saison,
            'users'           => $userRepository->findAll(),
            'idUploader'      => $idUploader
        ]);
    }

    /**
     * @Route("/series/saisons", name="saisons")
     */
    public function saison(Request $request) {
        $idUploader = $request->query->get("idUploader");
        if ($idUploader == null) {
            $idUploader = "";
        }

        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $serieRepository = $em->getRepository(Series::class);
        $userRepository = $em->getRepository(Users::class);
        $serie = $serieRepository->findById($id);
        if (gettype($serie) == "array" & sizeof($serie) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Serie non existante"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $serie = $serie[0];
        if ($this->getUser() == null) {
            $me = "none";
        } else {
            $me = $this->getUser();
        }

        return $this->render('series/saisons.html.twig', [
            'controller_name' => 'SerieController',
            'me'              =>                $me,
            'serie'          =>                 $serie,
            'users'           => $userRepository->findAll(),
            'idUploader'      => $idUploader
        ]);
    }

    /**
     * @Route("/series", name="series")
     */
    public function series(Request $request)
    {
        $idUploader = $request->query->get("idUploader");
        if ($idUploader == null) {
            $idUploader = "";
        }
        $em = $this->getDoctrine()->getEntityManager();
        $serieRepository = $em->getRepository(Series::class);
        $categorieRepository = $em->getRepository(Categories::class);
        $userRepository = $em->getRepository(Users::class);

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

        return $this->render('series/series.html.twig', [
            'controller_name' => 'SerieController',
            'me'              =>                $me,
            'series'          => $serieRepository->findAll(),
            'categories'      => $categorieRepository->findAll(),
            'users'           => $userRepository->findAll(),
            'idUploader'      => $idUploader,
            'page'            => $page,
            'filtre'          => $filtre,
            'word'            => $word
        ]);
    }

    /**
     * @Route("/series/addViewEpisode", name="episodeAddView")
     */
    function addView(Request $request) {
        $id = $request->request->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $episodeRepository = $em->getRepository(Episodes::class);
        $episode = $episodeRepository->findById($id);
        if (sizeof($episode) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cet episode n'existe pas."]]));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $episode = $episode[0];
        $episode->setVues($episode->getVues()+1);
        $em->persist($episode);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
