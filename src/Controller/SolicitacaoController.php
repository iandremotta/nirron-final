<?php

namespace App\Controller;

use App\Entity\Solicitacao;
use App\Form\SolicitacaoType;
use App\Entity\User;
use App\Repository\ConfiguracoesRepository;
use App\Repository\SolicitacaoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\FileUploader;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use setasign\Fpdi\Fpdi;
use App\Service\Watermark;

#[Route('/solicitacao')]
class SolicitacaoController extends AbstractController
{
    #[Route('/', name: 'app_solicitacao_index', methods: ['GET'])]
    public function index(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $solicitacoes = $solicitacaoRepository->findSolicitacaoByUser($this->getUser());
        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('solicitacao/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/new', name: 'app_solicitacao_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SolicitacaoRepository $solicitacaoRepository, FileUploader $fileUploader, Watermark $watermark): Response
    {
        $solicitacao = new Solicitacao();
        $form = $this->createForm(SolicitacaoType::class, $solicitacao);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $valor = str_replace('R$:', '', $solicitacao->getValor());
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
            $solicitacao->setValor($valor);
            $solicitacaoRepository->add($solicitacao, true, $this->getuser());
            if (in_array(User::ADMINISTRADOR_ASSESSORIA, $this->getUser()->getRoles()) || in_array(User::ADMINISTRADOR_LOGISTICA, $this->getUser()->getRoles()) ||    in_array(User::SUPER_USUARIO, $this->getUser()->getRoles())) {
                $watermark->addWatermark($solicitacao, $this->getParameter('kernel.project_dir'), $this->getUser());
            }

            $this->addFlash(
                'success',
                'Solicitação adicionada com sucesso.'
            );
            return $this->redirectToRoute('app_solicitacao_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('solicitacao/new.html.twig', [
            'solicitacao' => $solicitacao,
            'form' => $form,
            'nav_active' => 'Solicitação'
        ]);
    }

    #[Route('/{id}', name: 'app_solicitacao_show', methods: ['GET'])]
    public function show(Solicitacao $solicitacao): Response
    {
        return $this->render('solicitacao/show.html.twig', [
            'solicitacao' => $solicitacao,
            'nav_active' => 'Solicitação'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_solicitacao_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Solicitacao $solicitacao, SolicitacaoRepository $solicitacaoRepository): Response
    {
        $form = $this->createForm(SolicitacaoType::class, $solicitacao);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $solicitacaoRepository->add($solicitacao,  true, $this->getUser());

            return $this->redirectToRoute('app_solicitacao_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('solicitacao/edit.html.twig', [
            'solicitacao' => $solicitacao,
            'form' => $form,
            'nav_active' => 'Solicitação'
        ]);
    }

    #[Route('/aprovar/{id}', name: 'app_solicitacao_aprovar', methods: ['POST'])]
    public function aprovar(Request $request, Solicitacao $solicitacao, ConfiguracoesRepository $configuracoesRepository, SolicitacaoRepository $solicitacaoRepository, Watermark $watermark): Response
    {
        if ($this->isCsrfTokenValid('aprovar' . $solicitacao->getId(), $request->request->get('_token'))) {
            if (in_array('ROLE_APROVADOR_ADMINISTRATIVO', $this->getUser()->getRoles()) || in_array('ROLE_APROVADOR_OPERACIONAL', $this->getUser()->getRoles())) {
                $solicitacao->setStatus(Solicitacao::STATUS_APROVADOR_OK);
                $solicitacao->setUpdatedAt(new \DateTimeImmutable('now'));
                $solicitacao->setAprovador($this->getUser());
                $solicitacaoRepository->update($solicitacao, true);
                $this->addFlash(
                    'success',
                    'Solicitação aprovada com sucesso.'
                );
                if ($solicitacao->getEmpresa() == Solicitacao::EMPRESA_LOGISTICA && in_array('ROLE_APROVADOR_ADMINISTRATIVO', $this->getUser()->getRoles())) {
                    return $this->redirectToRoute('app_aprovador_administrativo_logistica_pendentes', [], Response::HTTP_SEE_OTHER);
                }
                return $this->redirectToRoute('app_login_success', [], Response::HTTP_SEE_OTHER);
            }

            if (in_array('ROLE_ADMINISTRADOR_ASSESSORIA', $this->getUser()->getRoles()) || in_array('ROLE_ADMINISTRADOR_LOGISTICA', $this->getUser()->getRoles()) || in_array('ROLE_SUPER', $this->getUser()->getRoles())) {

                $watermark->addWatermark($solicitacao, $this->getParameter('kernel.project_dir'), $this->getUser());
                $this->addFlash(
                    'success',
                    'Solicitação aprovada com sucesso.'
                );
                if ($solicitacao->getEmpresa() == Solicitacao::EMPRESA_ASSESSORIA) {
                    return $this->redirectToRoute('app_administrador_assessoria_pre_aprovados', [], Response::HTTP_SEE_OTHER);
                }
                return $this->redirectToRoute('app_administrador_logistica_pre_aprovados', [], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->redirectToRoute('app_solicitacao_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/recusar/{id}', name: 'app_solicitacao_recusar', methods: ['POST'])]
    public function recusar(Request $request, Solicitacao $solicitacao, SolicitacaoRepository $solicitacaoRepository): Response
    {

        if ($this->isCsrfTokenValid('recusar' . $solicitacao->getId(), $request->request->get('_token'))) {
            if (in_array('ROLE_APROVADOR_ADMINISTRATIVO', $this->getUser()->getRoles()) || in_array('ROLE_APROVADOR_OPERACIONAL', $this->getUser()->getRoles())) {
                $solicitacao->setUpdatedAt(new \DateTimeImmutable('now'));
                $solicitacao->setRecusador($this->getUser());
                $solicitacao->setStatus(Solicitacao::STATUS_APROVADOR_RECUSADO);
                $solicitacao->setRecusa($request->request->get('recusa'));
                $solicitacaoRepository->update($solicitacao, true);
                $this->addFlash(
                    'success',
                    'Solicitação recusada com sucesso.'
                );
                if ($solicitacao->getEmpresa() == Solicitacao::EMPRESA_LOGISTICA && in_array('ROLE_APROVADOR_ADMINISTRATIVO', $this->getUser()->getRoles())) {
                    return $this->redirectToRoute('app_aprovador_administrativo_logistica_pendentes', [], Response::HTTP_SEE_OTHER);
                }
                return $this->redirectToRoute('app_login_success', [], Response::HTTP_SEE_OTHER);
            }
            if (in_array('ROLE_ADMINISTRADOR_ASSESSORIA', $this->getUser()->getRoles()) || in_array('ROLE_ADMINISTRADOR_LOGISTICA', $this->getUser()->getRoles()) || in_array('ROLE_SUPER', $this->getUser()->getRoles())) {
                $solicitacao->setStatus(Solicitacao::STATUS_ADMINISTRADOR_RECUSADO);
                $solicitacao->setUpdatedAt(new \DateTimeImmutable('now'));
                $solicitacao->setRecusador($this->getUser());
                $solicitacao->setRecusa($request->request->get('recusa'));
                $solicitacaoRepository->update($solicitacao, true);
                $this->addFlash(
                    'success',
                    'Solicitação recusada com sucesso.'
                );
                if ($solicitacao->getEmpresa() == Solicitacao::EMPRESA_ASSESSORIA) {
                    return $this->redirectToRoute('app_administrador_assessoria_pre_aprovados', [], Response::HTTP_SEE_OTHER);
                }
                return $this->redirectToRoute('app_administrador_logistica_pre_aprovados', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->redirectToRoute('app_solicitacao_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_solicitacao_delete', methods: ['POST'])]
    public function delete(Request $request, Solicitacao $solicitacao, SolicitacaoRepository $solicitacaoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $solicitacao->getId(), $request->request->get('_token'))) {
            $solicitacaoRepository->remove($solicitacao, true);
        }

        return $this->redirectToRoute('app_solicitacao_index', [], Response::HTTP_SEE_OTHER);
    }
}
