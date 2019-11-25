<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Episodes;
use App\Entity\Films;
use App\Entity\Sagas;
use App\Entity\Saisons;
use App\Entity\Series;
use App\Entity\URLs;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModifController extends AbstractController
{

    /**
     * @Route("/modif/episode", name="modifEpisode")
     */
    public function modifEpisode(Request $request)
    {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error'   => "Vous n'êtes pas connecté"
            ]);
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            return $this->render('error.html.twig', [
                'error'   => "Vous ne disposez pas des permissions necéssaires",
                'me'      => $this->getUser()
            ]);
        }
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $episodeRepository = $em->getRepository(Episodes::class);
        $episode = $episodeRepository->findById($id);
        if (sizeof($episode) == 0) {
            return $this->render('error.html.twig', [
                'error'   => "Cet episode n'existe pas ".$id,
                'me'      => $this->getUser()
            ]);
        }
        $episode = $episode[0];
        if ($episode->getUser()->getId() != $this->getUser()->getId()) {
            return $this->render('error.html.twig', [
                'error'   => "Cet episode n'est pas uploadé par vous",
                'me'      => $this->getUser()
            ]);
        }
        $saisonRepository = $em->getRepository(Saisons::class);
        $userRepository = $em->getRepository(Users::class);
        if ($this->getUser()->getPerm() == "uploader") {
            return $this->render('modif/modifEpisode.html.twig', [
                'controller_name' => "ModifController",
                'me' => $this->getUser(),
                'episode' => $episode,
                'users' => $userRepository->findAll(),
                'saisons' => $saisonRepository->findBySerieId($episode->getSaison()->getSerie()->getId())
            ]);
        } else {
            return $this->render('modif/modifEpisodeOnlyProprio.html.twig', [
                'controller_name' => "ModifController",
                'me' => $this->getUser(),
                'episode' => $episode,
                'users' => $userRepository->findAll(),
            ]);
        }
    }

    /**
     * @Route("/modif/applyEpisode", name="modifApplyEpisode")
     */
    public function applyEpisode(Request $request)
    {

        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $id = $request->request->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $episodeRepository = $em->getRepository(Episodes::class);
        $episode = $episodeRepository->findById($id);
        if (sizeof($episode) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cet episode n'existe pas : ".$id]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $episode = $episode[0];
        if ($episode->getUser()->getId() != $this->getUser()->getId()) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cet episode n'est pas uploadé par vous"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $errors = [];
        $idProprio = $request->request->get("idProprio");
        if ($idProprio == "" | $idProprio == null) {
            $errors[] = "Proprietaire non renseigné";
        }
        if ($this->getUser()->getPerm() == "uploader") {
            $urls = explode(",", $request->request->get("URLs"));
            if (sizeof($urls) == 0) {
                $errors[] = "Aucune URL spécifiée";
            }
            $idSaison = $request->request->get("idSaison");
            if ($idSaison == "" | $idSaison == null) {
                $errors[] = "Saison non spécifiée";
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
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            if ($idProprio != $this->getUser()->getId()) {
                $userRepository = $em->getRepository(Users::class);
                $user = $userRepository->findById($idProprio);
                if (gettype($user) == "array" & sizeof($user) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Utilisateur non existant"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $user = $user[0];
                if ($user->getPerm() != "uploader") {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["La personne à qui vous souhaitez donner cet episode<br/>n'est pas 'uploader'"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            }


            if ($idProprio != $this->getUser()->getId()) {
                $episode->setUser($user);
            }
            if ($this->getUser()->getPerm() == "uploader") {
                $saisonRepository = $em->getRepository(Saisons::class);
                $saison = $saisonRepository->findById($idSaison);
                if (gettype($saison) == "array" & sizeof($saison) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Saison non existante"]]));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                $saison = $saison[0];
                if ($saison->getUser()->getId() != $this->getUser()->getId() &
                    $saison->getId() != $episode->getSaison()->getId()) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette saison ne vous appartiens pas"]]));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }

                $episode->setTitre($titre);
                $episode->setDuree(new \DateTime($duree));
                $episode->setSynopsis($synopsis);
                $episode->setSaison($saison);
                $urlRepository = $em->getRepository(URLs::class);
                $urlsOrigin = $urlRepository->findByEpisodeId($id);
                foreach ($urlsOrigin as $url) {
                    $em->remove($url);
                }
                foreach ($urls as $url) {
                    $urlObject = new URLs();
                    $urlObject->setLien($url);
                    $urlObject->setEpisode($episode);
                    $em->persist($urlObject);
                }
            }

            $em->persist($episode);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * @Route("/modif/saison", name="modifSaison")
     */
    public function modifSaison(Request $request)
    {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error'   => "Vous n'êtes pas connecté"
            ]);
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            return $this->render('error.html.twig', [
                'error'   => "Vous ne disposez pas des permissions necéssaires",
                'me'      => $this->getUser()
            ]);
        }
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $saisonRepository = $em->getRepository(Saisons::class);
        $saison = $saisonRepository->findById($id);
        if (sizeof($saison) == 0) {
            return $this->render('error.html.twig', [
                'error'   => "Cette saison n'existe pas ".$id,
                'me'      => $this->getUser()
            ]);
        }
        $saison = $saison[0];
        if ($saison->getUser()->getId() != $this->getUser()->getId()) {
            return $this->render('error.html.twig', [
                'error'   => "Cette saison n'est pas uploadé par vous",
                'me'      => $this->getUser()
            ]);
        }
        $serieRepository = $em->getRepository(Series::class);
        $userRepository = $em->getRepository(Users::class);
        if ($this->getUser()->getPerm() == "uploader") {
            return $this->render('modif/modifSaison.html.twig', [
                'controller_name' => "ModifController",
                'me'      => $this->getUser(),
                'saison'    => $saison,
                'users'        => $userRepository->findAll(),
                'series'       => $serieRepository->findAll()
            ]);
        } else {
            return $this->render('modif/modifSaisonOnlyProprio.html.twig', [
                'controller_name' => "ModifController",
                'me'      => $this->getUser(),
                'saison'    => $saison,
                'users'        => $userRepository->findAll(),
            ]);
        }
    }

    /**
     * @Route("/modif/applySaison", name="modifApplySaison")
     */
    public function applySaison(Request $request)
    {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $id = $request->request->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $saisonRepository = $em->getRepository(Saisons::class);
        $saison = $saisonRepository->findById($id);
        if (sizeof($saison) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette serie n'existe pas"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $saison = $saison[0];
        if ($saison->getUser()->getId() != $this->getUser()->getId()) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette serie n'est pas uploadée par vous"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $errors = array();

        if ($this->getUser()->getPerm() == "uploader") {
            $nom = $request->request->get("nom");
            if ($nom == "" | $nom == null) {
                $errors[] = "Nom non renseigné";
            }
            if (strlen($nom) > 50) {
                $errors[] = "Le nom ne doit pas faire plus de 50 caractères";
            }
            $idSerie = $request->request->get("idSerie");
            if ($idSerie == "" | $idSerie == null) {
                $errors[] = "Serie non renseigné";
            }
        }
        $allChildren = $request->request->get("allChildren");
        if ($allChildren == "" | $allChildren == null) {
            $errors[] = "Variable boolenne de la case à cocher non spécifiée";
        }
        $idProprio = $request->request->get("idProprio");
        if ($idProprio == "" | $idProprio == null) {
            $errors[] = "Proprietaire non renseigné";
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            if ($idProprio != $this->getUser()->getId()) {
                $userRepository = $em->getRepository(Users::class);
                $user = $userRepository->findById($idProprio);
                if (gettype($user) == "array" & sizeof($user) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Utilisateur non existant"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $user = $user[0];
                if ($user->getPerm() != "uploader") {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["La personne à qui vous souhaitez donner cette saison<br/>n'est pas 'uploader'"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }

                $saison->setUser($user);
                if ($allChildren == "true") {
                    $episodeRepository = $em->getRepository(Episodes::class);
                    $episodes = $episodeRepository->findBySaisonId($id);
                    foreach ($episodes as $episode) {
                        if ($episode->getUser()->getId() == $this->getUser()->getId()) {
                            $episode->setUser($user);
                            $em->persist($episode);
                        }
                    }
                }
            }

            if ($this->getUser()->getPerm() == "uploader") {
                $serieRepository = $em->getRepository(Series::class);
                $serie = $serieRepository->findById($idSerie);
                if (gettype($serie) == "array" & sizeof($serie) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Serie non existante"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $serie = $serie[0];
                if ($serie->getUser()->getId() != $this->getUser()->getId() &
                    $serie->getId() != $saison->getSerie()->getId()) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette serie ne vous appartiens pas"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }

                $saison->setNom($nom);
                $saison->setSerie($serie);
                $image = $request->files->get('image');

                if ($image != null) {
                    $image = $image->getPathName();
                    if (explode("/", mime_content_type($image))[0] != "image") {
                        $errors[] = "Ce fichier n'est pas une image";
                        unlink($image);
                    }
                    $ext = "." . explode("/", mime_content_type($image))[1];
                    $n = random_int(1, 10 ** 15);
                    $destination = "/root/projects/streamingWebSite/streamingSynfony/public/imgs/sagas/";
                    while (file_exists($destination . $n . $ext)) {
                        $n = random_int(1, 10 ** 15);
                    }
                    $destination = $destination . $n . $ext;
                    move_uploaded_file($image, $destination);
                    $oldImage = $saison->getImage();
                    $saison->setImage("/imgs/sagas/" . $n . $ext);
                    unlink("/root/projects/streamingWebSite/streamingSynfony/public" . $oldImage);
                }
            }

            $em->persist($saison);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * @Route("/modif/serie", name="modifSerie")
     */
    public function modifSerie(Request $request)
    {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error'   => "Vous n'êtes pas connecté"
            ]);
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            return $this->render('error.html.twig', [
                'error'   => "Vous ne disposez pas des permissions necéssaires",
                'me'      => $this->getUser()
            ]);
        }
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $serieRepository = $em->getRepository(Series::class);
        $serie = $serieRepository->findById($id);
        if (sizeof($serie) == 0) {
            return $this->render('error.html.twig', [
                'error'   => "Cette serie n'existe pas ".$id,
                'me'      => $this->getUser()
            ]);
        }
        $serie = $serie[0];
        if ($serie->getUser()->getId() != $this->getUser()->getId()) {
            return $this->render('error.html.twig', [
                'error'   => "Cette serie n'est pas uploadé par vous",
                'me'      => $this->getUser()
            ]);
        }
        $userRepository = $em->getRepository(Users::class);
        $categorieRepository = $em->getRepository(Categories::class);
        if ($this->getUser()->getPerm() == "uploader") {
            return $this->render('modif/modifSerie.html.twig', [
                'controller_name' => "ModifController",
                'me'      => $this->getUser(),
                'serie'    => $serie,
                'categories'   => $categorieRepository->findAll(),
                'users'        => $userRepository->findAll()
            ]);
        } else {
            return $this->render('modif/modifSerieOnlyProprio.html.twig', [
                'controller_name' => "ModifController",
                'me'      => $this->getUser(),
                'serie'    => $serie,
                'users'        => $userRepository->findAll()
            ]);
        }

    }

    /**
     * @Route("/modif/applySerie", name="modifApplySerie")
     */
    public function applySerie(Request $request)
    {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $id = $request->request->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $serieRepository = $em->getRepository(Series::class);
        $serie = $serieRepository->findById($id);
        if (sizeof($serie) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette serie n'existe pas"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $serie = $serie[0];
        if ($serie->getUser()->getId() != $this->getUser()->getId()) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette serie n'est pas uploadée par vous"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $errors = array();

        $nom = $request->request->get("nom");
        if ($this->getUser()->getPerm() == "uploader") {
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
        }

        $idProprio = $request->request->get("idProprio");
        if ($idProprio == "" | $idProprio == null) {
            $errors[] = "Proprietaire non renseigné";
        }
        $allChildren = $request->request->get("allChildren");
        if ($allChildren == "" | $allChildren == null) {
            $errors[] = "Variable boolenne de la case à cocher non spécifiée";
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            if ($idProprio != $this->getUser()->getId()) {
                $userRepository = $em->getRepository(Users::class);
                $user = $userRepository->findById($idProprio);
                if (gettype($user) == "array" & sizeof($user) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Utilisateur non existant"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $user = $user[0];
                if ($user->getPerm() != "uploader") {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["La personne à qui vous souhaitez donner cette serie<br/>n'est pas 'uploader'"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }

                $serie->setUser($user);
                if ($allChildren == "true") {
                    $saisonRepository = $em->getRepository(Saisons::class);
                    $saisons = $saisonRepository->findBySerieId($id);
                    foreach ($saisons as $saison) {
                        if ($saison->getUser()->getId() == $this->getUser()->getId()) {
                            $saison->setUser($user);
                            $em->persist($saison);

                            $episodeRepository = $em->getRepository(Episodes::class);
                            $episodes = $episodeRepository->findBySaisonId($saison->getId());
                            foreach ($episodes as $episode) {
                                if ($episode->getUser()->getId() == $this->getUser()->getId()) {
                                    $episode->setUser($user);
                                    $em->persist($episode);
                                }
                            }
                        }
                    }
                }
            }

            if ($this->getUser()->getPerm() == "uploader") {
                $categorieRepository = $em->getRepository(Categories::class);
                $categorie = $categorieRepository->findById($idCategorie);
                if (gettype($categorie) == "array" & sizeof($categorie) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Categorie non existante"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $categorie = $categorie[0];

                $serie->setNom($nom);
                $serie->setSynopsis($synopsis);
                $serie->setPrenomAuteur($prenomAuteur);
                $serie->setNomAuteur($nomAuteur);
                $serie->setCategorie($categorie);
                $image = $request->files->get('image');

                if ($image != null) {
                    $image = $image->getPathName();
                    if (explode("/", mime_content_type($image))[0] != "image") {
                        $errors[] = "Ce fichier n'est pas une image";
                        unlink($image);
                    }
                    $ext = "." . explode("/", mime_content_type($image))[1];
                    $n = random_int(1, 10 ** 15);
                    $destination = "/root/projects/streamingWebSite/streamingSynfony/public/imgs/sagas/";
                    while (file_exists($destination . $n . $ext)) {
                        $n = random_int(1, 10 ** 15);
                    }
                    $destination = $destination . $n . $ext;
                    move_uploaded_file($image, $destination);
                    $oldImage = $serie->getImage();
                    $serie->setImage("/imgs/sagas/" . $n . $ext);
                    unlink("/root/projects/streamingWebSite/streamingSynfony/public" . $oldImage);
                }
            }

            $em->persist($serie);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * @Route("/modif/saga", name="modifSaga")
     */
    public function modifSaga(Request $request)
    {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error'   => "Vous n'êtes pas connecté"
            ]);
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            return $this->render('error.html.twig', [
                'error'   => "Vous ne disposez pas des permissions necéssaires",
                'me'      => $this->getUser()
            ]);
        }
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $sagaRepository = $em->getRepository(Sagas::class);
        $saga = $sagaRepository->findById($id);
        if (sizeof($saga) == 0) {
            return $this->render('error.html.twig', [
                'error'   => "Cette saga n'existe pas ".$id,
                'me'      => $this->getUser()
            ]);
        }
        $saga = $saga[0];
        if ($saga->getUser()->getId() != $this->getUser()->getId()) {
            return $this->render('error.html.twig', [
                'error'   => "Cette saga n'est pas uploadé par vous",
                'me'      => $this->getUser()
            ]);
        }
        $userRepository = $em->getRepository(Users::class);
        $categorieRepository = $em->getRepository(Categories::class);
        if ($this->getUser()->getPerm() == "uploader") {
            return $this->render('modif/modifSaga.html.twig', [
                'controller_name' => "ModifController",
                'me'      => $this->getUser(),
                'saga'    => $saga,
                'categories'   => $categorieRepository->findAll(),
                'users'        => $userRepository->findAll()
            ]);
        } else {
            return $this->render('modif/modifSagaOnlyProprio.html.twig', [
                'controller_name' => "ModifController",
                'me'      => $this->getUser(),
                'saga'    => $saga,
                'users'        => $userRepository->findAll()
            ]);
        }
    }
    /**
     * @Route("/modif/applySaga", name="modifApplySaga")
     */
    public function applySaga(Request $request)
    {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $id = $request->request->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $sagaRepository = $em->getRepository(Sagas::class);
        $saga = $sagaRepository->findById($id);
        if (sizeof($saga) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette saga n'existe pas"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $saga = $saga[0];
        if ($saga->getUser()->getId() != $this->getUser()->getId()) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette saga n'est pas uploadée par vous"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $errors = array();

        if ($this->getUser()->getPerm() == "uploader") {
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
        }

        $idProprio = $request->request->get("idProprio");
        if ($idProprio == "" | $idProprio == null) {
            $errors[] = "Proprietaire non renseigné";
        }
        $allChildren = $request->request->get("allChildren");
        if ($allChildren == "" | $allChildren == null) {
            $errors[] = "Variable boolenne de la case à cocher non spécifiée";
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $em = $this->getDoctrine()->getEntityManager();
            if ($idProprio != $this->getUser()->getId()) {
                $userRepository = $em->getRepository(Users::class);
                $user = $userRepository->findById($idProprio);
                if (gettype($user) == "array" & sizeof($user) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Utilisateur non existant"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $user = $user[0];
                if ($user->getPerm() != "uploader") {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["La personne à qui vous souhaitez donner cette saga<br/>n'est pas 'uploader'"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $saga->setUser($user);
                if ($allChildren == "true") {
                    $filmRepository = $em->getRepository(Films::class);
                    $films = $filmRepository->findBySagaId($id);
                    foreach ($films as $film) {
                        if ($film->getUser()->getId() == $this->getUser()->getId()) {
                            $film->setUser($user);
                            $em->persist($film);
                        }
                    }
                }
            }

            if ($this->getUser()->getPerm() == "uploader") {
                $categorieRepository = $em->getRepository(Categories::class);
                $categorie = $categorieRepository->findById($idCategorie);
                if (gettype($categorie) == "array" & sizeof($categorie) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Categorie non existante"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $categorie = $categorie[0];

                $saga->setNom($nom);
                $saga->setSynopsis($synopsis);
                $saga->setPrenomAuteur($prenomAuteur);
                $saga->setNomAuteur($nomAuteur);
                $saga->setCategorie($categorie);
                $image = $request->files->get('image');

                if ($image != null) {
                    $image = $image->getPathName();
                    if (explode("/", mime_content_type($image))[0] != "image") {
                        $errors[] = "Ce fichier n'est pas une image";
                        unlink($image);
                    }
                    $ext = "." . explode("/", mime_content_type($image))[1];
                    $n = random_int(1, 10 ** 15);
                    $destination = "/root/projects/streamingWebSite/streamingSynfony/public/imgs/sagas/";
                    while (file_exists($destination . $n . $ext)) {
                        $n = random_int(1, 10 ** 15);
                    }
                    $destination = $destination . $n . $ext;
                    move_uploaded_file($image, $destination);
                    $oldImage = $saga->getImage();
                    $saga->setImage("/imgs/sagas/" . $n . $ext);
                    unlink("/root/projects/streamingWebSite/streamingSynfony/public" . $oldImage);
                }
            }

            $em->persist($saga);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * @Route("/modif/film", name="modifFilm")
     */
    public function modifFilm(Request $request)
    {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error'   => "Vous n'êtes pas connecté"
            ]);
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            return $this->render('error.html.twig', [
                'error'   => "Vous ne disposez pas des permissions necéssaires",
                'me'      => $this->getUser()
            ]);
        }
        $id = $request->query->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $filmRepository = $em->getRepository(Films::class);
        $film = $filmRepository->findById($id);
        if (sizeof($film) == 0) {
            return $this->render('error.html.twig', [
                'error'   => "Ce film n'existe pas ".$id,
                'me'      => $this->getUser()
            ]);
        }
        $film = $film[0];
        if ($film->getUser()->getId() != $this->getUser()->getId()) {
            return $this->render('error.html.twig', [
                'error'   => "Ce film n'est pas uploadé par vous",
                'me'      => $this->getUser()
            ]);
        }
        $sagaRepository = $em->getRepository(Sagas::class);
        $categorieRepository = $em->getRepository(Categories::class);
        $userRepository = $em->getRepository(Users::class);
        if ($this->getUser()->getPerm() == "uploader") {
            return $this->render('modif/modifFilm.html.twig', [
                'controller_name' => "ModifController",
                'me'      => $this->getUser(),
                'film'    => $film,
                'sagas'   => $sagaRepository->findAll(),
                'categories'   => $categorieRepository->findAll(),
                'users'        => $userRepository->findAll()
            ]);
        } else {
            return $this->render('modif/modifFilmOnlyProprio.html.twig', [
                'controller_name' => "ModifController",
                'me'      => $this->getUser(),
                'film'    => $film,
                'users'        => $userRepository->findAll()
            ]);
        }
    }

    /**
     * @Route("/modif/applyFilm", name="modifApplyFilm")
     */
    public function applyFilm(Request $request)
    {

        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "uploader" & $this->getUser()->getPerm() != "admin") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous ne disposez pas des permissions nécéssaire"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $id = $request->request->get("id");
        $em = $this->getDoctrine()->getEntityManager();
        $filmRepository = $em->getRepository(Films::class);
        $film = $filmRepository->findById($id);
        if (sizeof($film) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Ce film n'existe pas"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $film = $film[0];
        if ($film->getUser()->getId() != $this->getUser()->getId()) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Ce film n'est pas uploadé par vous"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $errors = [];

        if ($this->getUser()->getPerm() == "uploader") {
            $urls = explode(",", $request->request->get("URLs"));
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
        }

        $idProprio = $request->request->get("idProprio");
        if ($idProprio == "" | $idProprio == null) {
            $errors[] = "Proprietaire non renseigné";
        }

        if (sizeof($errors) > 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => $errors]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            if ($idProprio != $this->getUser()->getId()) {
                $userRepository = $em->getRepository(Users::class);
                $user = $userRepository->findById($idProprio);
                if (gettype($user) == "array" & sizeof($user) == 0) {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["Utilisateur non existant"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $user = $user[0];
                if ($user->getPerm() != "uploader") {
                    $response = new Response(json_encode(["rep" => "failed", "errors" => ["La personne à qui vous souhaitez donner ce film<br/>n'est pas 'uploader'"]]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
                $film->setUser($user);
            }

            if ($this->getUser()->getPerm() == "uploader") {
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
                        if (($film->getSaga() != null & $saga->getId() != $film->getSaga()->getId()) |
                            $film->getSaga() == null) {
                            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette saga ne vous appartiens pas"]]));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        }
                    }
                }

                $film->setTitre($titre);
                $film->setDuree(new \DateTime($duree));
                $film->setSynopsis($synopsis);
                $film->setDateSortie(new \DateTime($dateS));
                if ($idSaga == "") {
                    $film->setPrenomAuteur($prenomAuteur);
                    $film->setNomAuteur($nomAuteur);
                    $film->setCategorie($categorie);
                    $film->setSaga(null);
                } else {
                    $film->setSaga($saga);
                }
                $urlRepository = $em->getRepository(URLs::class);
                $urlsOrigin = $urlRepository->findByFilmId($id);
                foreach ($urlsOrigin as $url) {
                    $em->remove($url);
                }
                foreach ($urls as $url) {
                    $urlObject = new URLs();
                    $urlObject->setLien($url);
                    $urlObject->setFilm($film);
                    $em->persist($urlObject);
                }
                $image = $request->files->get('image');

                if ($image != null) {
                    $image = $image->getPathName();
                    if (explode("/", mime_content_type($image))[0] != "image") {
                        $errors[] = "Ce fichier n'est pas une image";
                        unlink($image);
                    }
                    $ext = "." . explode("/", mime_content_type($image))[1];
                    $n = random_int(1, 10 ** 15);
                    $destination = "/root/projects/streamingWebSite/streamingSynfony/public/imgs/films/";
                    while (file_exists($destination . $n . $ext)) {
                        $n = random_int(1, 10 ** 15);
                    }
                    $destination = $destination . $n . $ext;
                    move_uploaded_file($image, $destination);
                    $oldImage = $film->getImage();
                    $film->setImage("/imgs/films/" . $n . $ext);
                    unlink("/root/projects/streamingWebSite/streamingSynfony/public" . $oldImage);
                }
            }

            $em->persist($film);
            $em->flush();

            $response = new Response(json_encode(["rep" => "success"]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
