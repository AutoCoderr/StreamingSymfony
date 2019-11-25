<?php

namespace App\Controller;

use App\Entity\Categories;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    /**
     * @Route("/categories/liste", name="listeCategories")
     */
    public function index()
    {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error' => 'Vous devez être connecter pour accéder à cette page',
                'me'              => $this->getUser()
            ]);
        } else if ($this->getUser()->getPerm() != "admin") {
            return $this->render('error.html.twig', [
                'error' => 'Vous devez être admin pour accéder à cette page',
                'me'              => $this->getUser()
            ]);
        }
        $em = $this->getDoctrine()->getEntityManager();
        $categorieRepository = $em->getRepository(Categories::class);
        return $this->render('categories/categories.html.twig', [
            'controller_name' => 'CategorieController',
            'me'              => $this->getUser(),
            'categories'      => $categorieRepository->findAll()
        ]);
    }

    /**
     * @Route("/categorie/rename", name="renameCategorie")
     */
    function rename(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'ête pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "admin") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous devez être  admin"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $id = $request->request->get("id");
        if ($id == null | $id == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Id non spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $newName = $request->request->get("newName");
        if ($newName == "" | $newName == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Nouveau nom non spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        if (strlen($newName) > 30) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Une categorie ne peut pas faire plus de 30 caractères"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $em = $this->getDoctrine()->getEntityManager();
        $categorieRepository = $em->getRepository(Categories::class);
        $categorie = $categorieRepository->findById($id);
        if (sizeof($categorie) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Cette categorie n'existe pas"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $categorie = $categorie[0];
        $categorie->setNom($newName);

        $em->persist($categorie);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/categorie/add", name="addCategorie")
     */
    function add(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'ête pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "admin") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous devez être  admin"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $name = $request->request->get("name");
        if ($name == null | $name == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Nom non spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        if (strlen($name) > 30) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Une categorie ne peut pas faire plus de 30 caractères"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $categorie = new Categories();
        $categorie->setNom($name);

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($categorie);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/categorie/suppr", name="supprCategorie")
     */
    function suppr(Request $request) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'ête pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else if ($this->getUser()->getPerm() != "admin") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous devez être  admin"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $id = $request->request->get("id");
        if ($id == null | $id == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Id non spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $idCategorieToReplace = $request->request->get("idCategorieToReplace");
        if ($idCategorieToReplace == null | $idCategorieToReplace == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Id de la categorie de remplacement non spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        $em = $this->getDoctrine()->getEntityManager();
        $categorieRepository = $em->getRepository(Categories::class);
        $categorieToDelete = $categorieRepository->findById($id);
        if (sizeof($categorieToDelete) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Categorie à supprimer non existente"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $categorieToDelete = $categorieToDelete[0];

        $categorieForReplace = $categorieRepository->findById($idCategorieToReplace);
        if (sizeof($categorieForReplace) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Categorie pour rempmacement non existente"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $categorieForReplace = $categorieForReplace[0];

        foreach ($categorieToDelete->getFilms() as $film) {
            $film->setCategorie($categorieForReplace);
            $em->persist($film);
        }

        foreach ($categorieToDelete->getSagas() as $saga) {
            $saga->setCategorie($categorieForReplace);
            $em->persist($saga);
        }

        foreach ($categorieToDelete->getSeries() as $serie) {
            $serie->setCategorie($categorieForReplace);
            $em->persist($serie);
        }

        $em->remove($categorieToDelete);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
