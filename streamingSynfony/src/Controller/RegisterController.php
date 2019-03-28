<?php

namespace App\Controller;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new Users();

        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);

        $formBuilder
            ->add('email',      EmailType::class, ['label' => "Votre email"])
            ->add('Prenom',      TextType::class, ['label' => "Votre prénom"])
            ->add('Nom',      TextType::class, ['label' => "Votre nom"])
            ->add('Password', PasswordType::class, ['label' => "Mot de passe"])
            ->add('Password2', PasswordType::class, ['label' => "Le re-rentrer"])
            ->add('dateN', DateType::class, [
                'label' => "Votre date de naissance",
                'days' => range(1,31),
                'months' => range(1,12),
                'years' => range(1950,2015)
            ])
            ->add('save', SubmitType::class, array('label' => "S'inscrire")
            );

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setPerm("visioner");
            $user->setBanned(false);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("acceuil");
        }

        return $this->render('register/register.html.twig', [
            'controller_name' => 'RegisterController',
            'form'            => $form->createView()
        ]);
    }
}
