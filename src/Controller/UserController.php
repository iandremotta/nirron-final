<?php

namespace App\Controller;

use App\Entity\Configuracoes;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\ConfiguracoesRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
// Include paginator interface
use Knp\Component\Pager\PaginatorInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request, PaginatorInterface $paginator, ConfiguracoesRepository $configuracoesRepository): Response
    {
        $users = $userRepository->createQueryBuilder('u')->orderBy('u.createdAt', 'desc');
        $pagination = $paginator->paginate($users, $request->query->getInt('page', 1), 10);
        $configuracoes = $configuracoesRepository->findLastInserted();
        // $configuracoes = $configuracoesRepository->findAll();
        // dd($configuracoes[0]['path']);
        return $this->render('user/index.html.twig', [
            'users' => $pagination,
            'configuracoes' => $configuracoes,
            'nav_active' => "Usuários"
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                'Usuário adicionado com sucesso.'
            );
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $userRepository->add($user, true);
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
            'nav_active' => 'Usuários'
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'nav_active' => 'Usuários'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                'Usuário atualizado com sucesso.'
            );
            if ($form->get('password')->getData() != null) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
            }
            $userRepository->add($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'nav_active' => 'Usuários'
        ]);
    }

    #[Route('/ativar/{id}', name: 'app_user_active', methods: ['POST'])]
    public function active(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash(
                'success',
                'Usuário ativado com sucesso.'
            );
            $user->setIsActive(true);
            $userRepository->add($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/inativar/{id}', name: 'app_user_inactive', methods: ['POST'])]
    public function inactive(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash(
                'success',
                'Usuário inativado com sucesso.'
            );
            $user->setIsActive(false);
            $userRepository->add($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/configuracoes', name: 'app_configuracoes', methods: ['POST'])]
    public function configuracoes(Request $request, ConfiguracoesRepository $configuracoesRepository): Response
    {
        // $configuracoes = $configuracoesRepository->findBy(array(), array('id' => 'DESC'), 1, 0);
        if ($request->get('assessoria') != null) {
            $configuracoes = $configuracoesRepository->findLastInserted();
            if ($configuracoes != null) {
                $configuracoes->setPathAssessoria($request->get('assessoria'));
                $configuracoesRepository->add($configuracoes, true);
                $this->addFlash(
                    'success',
                    'Caminho adicionado com sucesso.'
                );
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
            $configuracoes = new Configuracoes();
            $configuracoes->setPathAssessoria($request->get('assessoria'));
            $configuracoesRepository->add($configuracoes, true);
            $this->addFlash(
                'success',
                'Caminho adicionado com sucesso.'
            );
        }

        if ($request->get('logistica') != null) {
            $configuracoes = $configuracoesRepository->findLastInserted();
            if ($configuracoes != null) {
                $configuracoes->setPathLogistica($request->get('logistica'));
                $configuracoesRepository->add($configuracoes, true);
                $this->addFlash(
                    'success',
                    'Caminho adicionado com sucesso.'
                );
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
            $configuracoes = new Configuracoes();
            $configuracoes->setPathLogistica($request->get('logistica'));
            $configuracoesRepository->add($configuracoes, true);
            $this->addFlash(
                'success',
                'Caminho adicionado com sucesso.'
            );
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
