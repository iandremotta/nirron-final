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

#[Route('/administrador')]
class AdministradorController extends AbstractController
{

    #[Route('/assessoria/pre-aprovados', name: 'app_administrador_assessoria_pre_aprovados', methods: ['GET'])]
    public function assessoriaPendentes(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $solicitacoes = $solicitacaoRepository->findAllByEmpresaStatus(Solicitacao::EMPRESA_ASSESSORIA, Solicitacao::STATUS_APROVADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('administrador/index_assessoria.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/assessoria/todos', name: 'app_administrador_assessoria_todos', methods: ['GET'])]
    public function assessoriaTodos(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {

        $solicitacoes = $solicitacaoRepository->findAllByEmpresa(Solicitacao::EMPRESA_ASSESSORIA);
        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('administrador/index_assessoria.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/logistica/pre-aprovados', name: 'app_administrador_logistica_pre_aprovados', methods: ['GET'])]
    public function logisticaPreAprovados(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $solicitacoes = $solicitacaoRepository->findAllByEmpresaStatus(Solicitacao::EMPRESA_LOGISTICA, Solicitacao::STATUS_APROVADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('administrador/index_logistica.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/logistica/todos', name: 'app_administrador_logistica_todos', methods: ['GET'])]
    public function logisticaTodos(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $solicitacoes = $solicitacaoRepository->findAllByEmpresa(Solicitacao::EMPRESA_LOGISTICA);
        // dd($solicitacoes);
        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('administrador/index_logistica.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }
}
