<?php

namespace App\Controller;

use App\Entity\Solicitacao;
use App\Form\SolicitacaoType;
use App\Repository\SolicitacaoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/aprovador')]
class AprovadorController extends AbstractController
{

    #[Route('/administrativo/pendentes', name: 'app_aprovador_administrativo_pendentes', methods: ['GET'])]
    public function administrativoPendentes(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array('ROLE_APROVADOR_ADMINISTRATIVO', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_solicitacao_index');
        }

        $solicitacoes = $solicitacaoRepository->findAllByTipoStatus(Solicitacao::TIPO_ADMINISTRATIVO, Solicitacao::STATUS_PENDENTE);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('aprovador/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/administrativo/pre-aprovados', name: 'app_aprovador_administrativo_pre_aprovados', methods: ['GET'])]
    public function administrativoPreAprovados(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array('ROLE_APROVADOR_ADMINISTRATIVO', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_solicitacao_index');
        }

        $solicitacoes = $solicitacaoRepository->findAllByTipoStatus(Solicitacao::TIPO_ADMINISTRATIVO, Solicitacao::STATUS_APROVADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('aprovador/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/administrativo/todos', name: 'app_aprovador_administrativo_todos', methods: ['GET'])]
    public function administrativoTodos(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array('ROLE_APROVADOR_ADMINISTRATIVO', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_solicitacao_index');
        }

        $solicitacoes = $solicitacaoRepository->findAllByTipo(Solicitacao::TIPO_ADMINISTRATIVO);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('aprovador/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/operacional/pendentes', name: 'app_aprovador_operacional_pendentes', methods: ['GET'])]
    public function operacionalPendentes(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array('ROLE_APROVADOR_OPERACIONAL', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_solicitacao_index');
        }

        $solicitacoes = $solicitacaoRepository->findAllByTipoStatus(Solicitacao::TIPO_OPERACIONAL, Solicitacao::STATUS_PENDENTE);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('aprovador/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/operacional/pre-aprovados', name: 'app_aprovador_operacional_pre_aprovados', methods: ['GET'])]
    public function operacionalPreAprovados(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array('ROLE_APROVADOR_OPERACIONAL', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_solicitacao_index');
        }

        $solicitacoes = $solicitacaoRepository->findAllByTipoStatus(Solicitacao::TIPO_OPERACIONAL, Solicitacao::STATUS_APROVADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('aprovador/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/operacional/todos', name: 'app_aprovador_operacional_todos', methods: ['GET'])]
    public function aprovadorTodos(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        if (!in_array('ROLE_APROVADOR_OPERACIONAL', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_solicitacao_index');
        }

        $solicitacoes = $solicitacaoRepository->findAllByTipo(Solicitacao::TIPO_OPERACIONAL);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('aprovador/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }
}
