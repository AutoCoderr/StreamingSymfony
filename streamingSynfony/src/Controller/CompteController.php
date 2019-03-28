<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CompteController extends AbstractController
{
    /**
     * @Route("/compte/voir", name="voirCompte")
     */
    public function voir()
    {
        if ($this->getUser() == null) {
            return $this->render('error.html.twig', [
                'error'           => "Vous devez être connecter pour consulter votre compte",
                'me'              => $this->getUser(),
            ]);
        }
        return $this->render('compte/voir.html.twig', [
            'controller_name' => 'CompteController',
            'me'              => $this->getUser()
        ]);
    }

    /**
     * @Route("/compte/changePasswd", name="changePassword")
     */
    public function changePasswd(Request $request, UserPasswordEncoderInterface $encoder) {
        if ($this->getUser() == null) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Vous n'êtes pas connecté"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $password = $request->request->get("password");
        if ($password == null | $password == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Mot de passe non spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $em = $this->getDoctrine()->getEntityManager();
        $this->getUser()->setPassword($encoder->encodePassword($this->getUser(), $password));
        $em->persist($this->getUser());
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /*
     * @Route("/compte/changePasswdGratuit", name="changePasswdGratuit")
     */
    /*public function changePasswdGratuit(Request $request, UserPasswordEncoderInterface $encoder) {
        $password = $request->query->get("password");
        $id = $request->query->get("id");

        if ($password == null | $password == "") {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Mot de passe non spécifié"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $em = $this->getDoctrine()->getEntityManager();
        $userRepository = $em->getRepository(Users::class);
        $user = $userRepository->findById($id);
        if (sizeof($user) == 0) {
            $response = new Response(json_encode(["rep" => "failed", "errors" => ["Utilisateur non existent"]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        $user = $user[0];
        $user->setPassword($encoder->encodePassword($user, $password));
        $em->persist($user);
        $em->flush();

        $response = new Response(json_encode(["rep" => "success"]));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }*/
}
