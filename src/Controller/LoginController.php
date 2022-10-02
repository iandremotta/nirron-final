<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/', name: 'app_login')]

    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'controller_name' => "Hi"
        ]);
    }

    #[Route('/login_success', name: 'app_login_success')]
    public function loginRedictAction()
    {
        if ($this->isGranted('ROLE_ADMINISTRADOR_ASSESSORIA')) {
            return $this->redirectToRoute("app_administrador_assessoria_pre_aprovados");
        }

        if ($this->isGranted('ROLE_ADMINISTRADOR_LOGISTICA')) {
            return $this->redirectToRoute("app_administrador_logistica_pre_aprovados");
        }

        if ($this->isGranted('ROLE_APROVADOR_ADMINISTRATIVO')) {
            return $this->redirectToRoute("app_aprovador_administrativo_assessoria_pendentes");
        }

        if ($this->isGranted('ROLE_APROVADOR_OPERACIONAL')) {
            return $this->redirectToRoute("app_aprovador_operacional_pendentes");
        }

        if ($this->isGranted('ROLE_SOLICITANTE')) {
            return $this->redirectToRoute("app_solicitacao_index");
        }

        if ($this->isGranted('ROLE_FINANCEIRO_ASSESSORIA')) {
            return $this->redirectToRoute("app_financeiro_assessoria_semana");
        }

        if ($this->isGranted('ROLE_FINANCEIRO_LOGISTICA')) {
            return $this->redirectToRoute("app_financeiro_logistica_semana");
        }

        if ($this->isGranted('ROLE_SUPER')) {
            return $this->redirectToRoute("app_administrador_assessoria_pre_aprovados");
        }

        return $this->redirectToRoute("app_login");
    }
}
