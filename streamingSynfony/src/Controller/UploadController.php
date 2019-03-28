<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Commentaires;
use App\Entity\Episodes;
use App\Entity\Films;
use App\Entity\Sagas;
use App\Entity\Saisons;
use App\Entity\Series;
use App\Entity\URLs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    /**
     * @Route("/upload", name="upload")
     */
    public function index()
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute("login");
        } else if ($this->getUser()->getPerm() != "uploader") {
            return $this->redirectToRoute("acceuil");
        }

        $em = $this->getDoctrine()->getEntityManager();
        $categorieRepository = $em->getRepository(Categories::class);
        $sagaRepository = $em->getRepository(Sagas::class);
        $serieRepository = $em->getRepository(Series::class);
        return $this->render('upload/upload.html.twig', [
            'controller_name' => 'UploadController',
            'me'         =>       $this->getUser(),
            'categories'  =>       $categorieRepository->findAll(),
            'sagas'       =>       $sagaRepository->findAll(),
            'series'       =>       $serieRepository->findAll()
        ]);
    }

    /**
     * @Route("/upload/sendSaison", name="uploadSendSaison")
     */
    public function sendSaison(Request $request)
    {

        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $errors = [];
        $nom = $request->request->get("nom");
        if ($nom == "" | $nom == null) {
            $errors[] = "Nom non renseigné";
        }
        if (strlen($nom) > 50) {
            $errors[] = "Le nom de doit pas faire plus de 50 caractères";
        }
        $idSerie = $request->request->get("idSerie");
        if ($idSerie == "" | $idSerie == null) {
            $errors[] = "Serie non spécifiée";
        }

        $image = $request->files->get('image');
        if ($image == null) {
            $errors[] = "Image non renseigné";
        } else {
            $image = $image->getPathName();
            if (explode("/" , mime_content_type($image))[0] != "image") {
                $errors[] = "Ce fichier n'est pas une image";
                unlink($image);
            }
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            $serieRepository = $em->getRepository(Series::class);
            $serie = $serieRepository->findById($idSerie);
            if (gettype($serie) == "array" & sizeof($serie) == 0) {
                $response = new Response(json_encode(["rep" => "failed", "errors" => ["Serie non existante"]]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
            $serie = $serie[0];
            if ($serie->getUser()->getId() != $this->getUser()->getId()) {
                $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette serie ne vous appartient pas"]]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            $saison = new Saisons();
            $saison->setUser($this->getUser());
            $saison->setNom($nom);
            $saison->setSerie($serie);
            $ext = ".".explode("/" , mime_content_type($image))[1];
            $n = random_int(1, 10**15);
            $destination = "/root/projects/streamingWebSite/streamingSynfony/public/imgs/saisons/";
            while(file_exists($destination.$n.$ext)) {
                $n = random_int(1, 10**15);
            }
            $destination = $destination.$n.$ext;
            move_uploaded_file ( $image , $destination);
            $saison->setImage("/imgs/saisons/".$n.$ext);

            $em->persist($saison);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * @Route("/upload/sendEpisode", name="uploadSendEpisode")
     */
    public function sendEpisode(Request $request)
    {

        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $errors = [];
        $urls = explode(",",$request->request->get("URLs"));
        if (sizeof($urls) == 0) {
            $errors[] = "Aucune URL spécifiée";
        }
        $idSaison = $request->request->get("idSaison");
        if ($idSaison == null | $idSaison == "") {
            $errors[] = "Saison non spécifié";
        }
        $titre = $request->request->get("titre");
        if ($titre == "" | $titre == null) {
            $errors[] = "Titre non renseigné";
        }
        if (strlen($titre) > 50) {
            $errors[] = "Le titre ne doit pas faire plus de 50 caractères";
        }
        $duree = $request->request->get("duree");
        if ($duree == "" | $duree == null) {
            $errors[] = "Durée non renseigné";
        }
        $synopsis = $request->request->get("synopsis");
        if ($synopsis == "" | $synopsis == null) {
            $errors[] = "Synopsis non renseigné";
        }
        if (strlen($synopsis) > 500) {
            $errors[] = "Le synopsis ne doit pas faire plus de 500 caractères";
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            $saisonRepository = $em->getRepository(Saisons::class);
            $saison = $saisonRepository->findById($idSaison);
            if (gettype($saison) == "array" & sizeof($saison) == 0) {
                $response = new Response(json_encode(["rep" => "failed", "errors" => ["Saison non existante"]]));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            $saison = $saison[0];
            if ($saison->getUser()->getId() != $this->getUser()->getId()) {
                $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette saison ne vous appartient pas"]]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            $episode = new Episodes();
            $episode->setUser($this->getUser());
            $episode->setTitre($titre);
            $episode->setDuree(new \DateTime($duree));
            $episode->setSynopsis($synopsis);
            $episode->setSaison($saison);
            foreach ($urls as $url) {
                $urlObject = new URLs();
                $urlObject->setLien($url);
                $urlObject->setEpisode($episode);
                $em->persist($urlObject);
            }

            $em->persist($episode);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * @Route("/upload/sendFilm", name="uploadSendFilm")
     */
    public function sendFilm(Request $request)
    {

        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $errors = [];
        $urls = explode(",",$request->request->get("URLs"));
        if (sizeof($urls) == 0) {
            $errors[] = "Aucune URL spécifiée";
        }
        $idSaga = $request->request->get("idSaga");
        if ($idSaga == null) {
            $idSaga = "";
        }
        $titre = $request->request->get("titre");
        if ($titre == "" | $titre == null) {
            $errors[] = "Titre non renseigné";
        }
        if (strlen($titre) > 30) {
            $errors[] = "Le film ne doit pas faire plus de 30 caractères";
        }
        $idCategorie = $request->request->get("idCategorie");
        if (($idCategorie == "" | $idCategorie == null) & $idSaga == "") {
            $errors[] = "Catégorie non renseigné";
        }
        $duree = $request->request->get("duree");
        if ($duree == "" | $duree == null) {
            $errors[] = "Durée non renseigné";
        }
        $synopsis = $request->request->get("synopsis");
        if ($synopsis == "" | $synopsis == null) {
            $errors[] = "Synopsis non renseigné";
        }
        if (strlen($synopsis) > 500) {
            $errors[] = "Le synopsis ne doit pas faire plus de 500 caractères";
        }
        $dateS = $request->request->get("dateS");
        if ($dateS == "" | $dateS == null) {
            $errors[] = "Date de sortie non renseigné";
        }
        $prenomAuteur = $request->request->get("prenomAuteur");
        if (($prenomAuteur == "" | $prenomAuteur == null) & $idSaga == "") {
            $errors[] = "Prénom de l'auteur non renseigné";
        }
        if (strlen($prenomAuteur) > 30) {
            $errors[] = "Le prénom de l'auteur ne doit pas faire plus de 30 caractères";
        }

        $nomAuteur = $request->request->get("nomAuteur");
        if (($nomAuteur == "" | $nomAuteur == null) & $idSaga == "") {
            $errors[] = "Nom de l'auteur non renseigné";
        }
        if (strlen($nomAuteur) > 30) {
            $errors[] = "Le nom de l'auteur ne doit pas faire plus de 30 caractères";
        }

        $image = $request->files->get('image');
        if ($image == null) {
            $errors[] = "Image non renseigné";
        } else {
            $image = $image->getPathName();
            if (explode("/" , mime_content_type($image))[0] != "image") {
                $errors[] = "Ce fichier n'est pas une image";
                unlink($image);
            }
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            if ($idSaga == "") {
                $categorieRepository = $em->getRepository(Categories::class);
                $categorie = $categorieRepository->findById($idCategorie);
                if (gettype($categorie) == "array" & sizeof($categorie) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Categorie non existante"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $categorie = $categorie[0];
            } else {
                $sagaRepository = $em->getRepository(Sagas::class);
                $saga = $sagaRepository->findById($idSaga);
                if (gettype($saga) == "array" & sizeof($saga) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Saga non existante"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $saga = $saga[0];
                if ($saga->getUser()->getId() != $this->getUser()->getId()) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette saga ne vous appartiens pas"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            }

            $film = new Films();
            $film->setUser($this->getUser());
            $film->setTitre($titre);
            $film->setDuree(new \DateTime($duree));
            $film->setSynopsis($synopsis);
            $film->setDateSortie(new \DateTime($dateS));
            if ($idSaga == "") {
                $film->setPrenomAuteur($prenomAuteur);
                $film->setNomAuteur($nomAuteur);
                $film->setCategorie($categorie);
            } else {
                $film->setSaga($saga);
            }
            foreach ($urls as $url) {
                $urlObject = new URLs();
                $urlObject->setLien($url);
                $urlObject->setFilm($film);
                $em->persist($urlObject);
            }
            $ext = ".".explode("/" , mime_content_type($image))[1];
            $n = random_int(1, 10**15);
            $destination = "/root/projects/streamingWebSite/streamingSynfony/public/imgs/films/";
            while(file_exists($destination.$n.$ext)) {
                $n = random_int(1, 10**15);
            }
            $destination = $destination.$n.$ext;
            move_uploaded_file ( $image , $destination);
            $film->setImage("/imgs/films/".$n.$ext);

            $em->persist($film);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * @Route("/upload/sendSerie", name="uploadSendSerie")
     */

    public function sendSerie(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $errors = array();
        $nom = $request->request->get("nom");
        if ($nom == "" | $nom == null) {
            $errors[] = "Nom non renseigné";
        }
        if (strlen($nom) > 20) {
            $errors[] = "Le nom ne doit pas faire plus de 20 caractères";
        }
        $idCategorie = $request->request->get("idCategorie");
        if ($idCategorie == "" | $idCategorie == null) {
            $errors[] = "Catégorie non renseigné";
        }
        $synopsis = $request->request->get("synopsis");
        if ($synopsis == "" | $synopsis == null) {
            $errors[] = "Synopsis non renseigné";
        }
        if (strlen($synopsis) > 500) {
            $errors[] = "Le synopsis ne doit pas faire plus de 500 caractères";
        }
        $prenomAuteur = $request->request->get("prenomAuteur");
        if ($prenomAuteur == "" | $prenomAuteur == null) {
            $errors[] = "Prénom de l'auteur non renseigné";
        }
        if (strlen($prenomAuteur) > 20) {
            $errors[] = "Le prénom de l'auteur ne doit pas faire plus de 20 caractères";
        }
        $dateS = $request->request->get("dateS");
        if ($dateS == "" | $dateS == null) {
            $errors[] = "Date de sortie non renseigné";
        }
        $nomAuteur = $request->request->get("nomAuteur");
        if ($nomAuteur == "" | $nomAuteur == null) {
            $errors[] = "Nom de l'auteur non renseigné";
        }
        if (strlen($nomAuteur) > 20) {
            $errors[] = "Le nom de l'auteur ne doit pas faire plus de 20 caractères";
        }
        $image = $request->files->get('image');
        if ($image == null) {
            $errors[] = "Image non renseigné";
        } else {
            $image = $image->getPathName();
            if (explode("/" , mime_content_type($image))[0] != "image") {
                $errors[] = "Ce fichier n'est pas une image";
                unlink($image);
            }
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            $categorieRepository = $em->getRepository(Categories::class);
            $categorie = $categorieRepository->findById($idCategorie);
            if (gettype($categorie) == "array" & sizeof($categorie) == 0) {
                $response = new Response(json_encode(["rep" => "failed", "errors" => ["Categorie non existante"]]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
            $categorie = $categorie[0];

            $serie = new Series();
            $serie->setUser($this->getUser());
            $serie->setNom($nom);
            $serie->setDateSortie(new \DateTime($dateS));
            $serie->setSynopsis($synopsis);
            $serie->setPrenomAuteur($prenomAuteur);
            $serie->setNomAuteur($nomAuteur);
            $serie->setCategorie($categorie);

            $ext = ".".explode("/" , mime_content_type($image))[1];
            $n = random_int(1, 10**15);
            $destination = "/root/projects/streamingWebSite/streamingSynfony/public/imgs/series/";
            while(file_exists($destination.$n.$ext)) {
                $n = random_int(1, 10**15);
            }
            $destination = $destination.$n.$ext;
            move_uploaded_file ( $image , $destination);
            $serie->setImage("/imgs/series/".$n.$ext);

            $em->persist($serie);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * @Route("/upload/sendSaga", name="uploadSendSaga")
     */

    public function sendSaga(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $errors = array();
        $nom = $request->request->get("nom");
        if ($nom == "" | $nom == null) {
            $errors[] = "Nom non renseigné";
        }
        if (strlen($nom) > 20) {
            $errors[] = "Le nom ne doit pas faire plus de 20 caractères";
        }
        $idCategorie = $request->request->get("idCategorie");
        if ($idCategorie == "" | $idCategorie == null) {
            $errors[] = "Catégorie non renseigné";
        }
        $synopsis = $request->request->get("synopsis");
        if ($synopsis == "" | $synopsis == null) {
            $errors[] = "Synopsis non renseigné";
        }
        if (strlen($synopsis) > 500) {
            $errors[] = "Le synopsis ne doit pas faire plus de 500 caractères";
        }
        $prenomAuteur = $request->request->get("prenomAuteur");
        if ($prenomAuteur == "" | $prenomAuteur == null) {
            $errors[] = "Prénom de l'auteur non renseigné";
        }
        if (strlen($prenomAuteur) > 20) {
            $errors[] = "Le prénom de l'auteur ne doit pas faire plus de 20 caractères";
        }
        $nomAuteur = $request->request->get("nomAuteur");
        if ($nomAuteur == "" | $nomAuteur == null) {
            $errors[] = "Nom de l'auteur non renseigné";
        }
        if (strlen($nomAuteur) > 20) {
            $errors[] = "Le nom de l'auteur ne doit pas faire plus de 20 caractères";
        }
        $image = $request->files->get('image');
        if ($image == null) {
            $errors[] = "Image non renseigné";
        } else {
            $image = $image->getPathName();
            if (explode("/" , mime_content_type($image))[0] != "image") {
                $errors[] = "Ce fichier n'est pas une image";
                unlink($image);
            }
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            $categorieRepository = $em->getRepository(Categories::class);
            $categorie = $categorieRepository->findById($idCategorie);
            if (gettype($categorie) == "array" & sizeof($categorie) == 0) {
                $response = new Response(json_encode(["rep" => "failed", "errors" => ["Categorie non existante"]]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
            $categorie = $categorie[0];

            $saga = new Sagas();
            $saga->setUser($this->getUser());
            $saga->setNom($nom);
            $saga->setSynopsis($synopsis);
            $saga->setPrenomAuteur($prenomAuteur);
            $saga->setNomAuteur($nomAuteur);
            $saga->setCategorie($categorie);

            $ext = ".".explode("/" , mime_content_type($image))[1];
            $n = random_int(1, 10**15);
            $destination = "/root/projects/streamingWebSite/streamingSynfony/public/imgs/sagas/";
            while(file_exists($destination.$n.$ext)) {
                $n = random_int(1, 10**15);
            }
            $destination = $destination.$n.$ext;
            move_uploaded_file ( $image , $destination);
            $saga->setImage("/imgs/sagas/".$n.$ext);

            $em->persist($saga);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
