<?php

namespace App\Controller;

use App\Entity\Solicitacao;
use App\Form\SolicitacaoType;
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
    public function new(Request $request, SolicitacaoRepository $solicitacaoRepository, FileUploader $fileUploader): Response
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
    public function aprovar(Request $request, Solicitacao $solicitacao, ConfiguracoesRepository $configuracoesRepository, SolicitacaoRepository $solicitacaoRepository): Response
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
                return $this->redirectToRoute('app_login_success', [], Response::HTTP_SEE_OTHER);
            }

            if (in_array('ROLE_ADMINISTRADOR_ASSESSORIA', $this->getUser()->getRoles()) || in_array('ROLE_ADMINISTRADOR_LOGISTICA', $this->getUser()->getRoles()) || in_array('ROLE_SUPER', $this->getUser()->getRoles())) {
                $configuracoes = $configuracoesRepository->findLastInserted();
                if ($configuracoes == null || $configuracoes->getPath() == "") {
                    $this->addFlash(
                        'error',
                        'Caminho não encontrado, solicite ao SUPER a configuração.'
                    );
                    return $this->redirectToRoute('app_solicitacao_show', ['id' => $solicitacao->getId()], Response::HTTP_SEE_OTHER);
                }

                $pdf = new Fpdi();
                $file = $this->getParameter('kernel.project_dir') . '\public\temp\products\\' . $solicitacao->getImageName();
                $solicitacao->setStatus(Solicitacao::STATUS_ADMINISTRADOR_OK);
                $solicitacao->setUpdatedAt(new \DateTimeImmutable('now'));
                $solicitacao->setAdministrador($this->getUser());
                $solicitacaoRepository->update($solicitacao, true);
                if (file_exists($file)) {
                    $pagecount = $pdf->setSourceFile($file);
                } else {
                    die('Source PDF not found!');
                }

                // Add watermark image to PDF pages 
                for ($i = 1; $i <= $pagecount; $i++) {
                    $tpl = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tpl);
                    $pdf->addPage();
                    $pdf->useTemplate($tpl, 1, 1, $size['width'], $size['height'], TRUE);
                    $str = iconv('UTF-8', 'windows-1252', 'Aprovador: ' . $solicitacao->getAdministrador()->getNome());
                    //Put the watermark 
                    $pdf->SetFont('Helvetica', 'B', '16');
                    $pdf->SetTextColor(255, 0, 0);
                    $pdf->SetXY(($size['width'] - 100), ($size['height'] - 25));
                    $pdf->Write(0, $str);
                }

                $pdf->Output('F', $file);
                $filesystem = new Filesystem();
                try {
                    if (!$filesystem->exists($configuracoes->getPath())) {
                        $filesystem->mkdir($configuracoes->getPath(), 0700);
                    }
                    $data = $solicitacao->getVencimento();
                    $result = $data->format('Y-m-d');
                    $result = explode('-', $result);

                    if (!$filesystem->exists($configuracoes->getPath() . '\\' . $result[0])) {
                        $filesystem->mkdir($configuracoes->getPath() . '\\' . $result[0], 0700);
                    }

                    if (!$filesystem->exists($configuracoes->getPath() . '\\' . $result[0] . '\\' . $result[1])) {
                        $filesystem->mkdir($configuracoes->getPath() . '\\' . $result[0] . '\\'  . $result[1], 0700);
                    }

                    if (!$filesystem->exists($configuracoes->getPath() . '\\' . $result[0] . '\\'  . $result[1] . '\\'  . $result[2])) {
                        $filesystem->mkdir($configuracoes->getPath() . '\\' . $result[0] . '\\'  . $result[1] . '\\'  . $result[2], 0700);
                    }

                    $filesystem->chmod($this->getParameter('kernel.project_dir') . '\public\temp\products\\', 0700, 0000, true);

                    copy($this->getParameter('kernel.project_dir') . '\public\temp\products\\' . $solicitacao->getImageName(), $configuracoes->getPath() . '\\' . $result[0] . '\\'  . $result[1] . '\\'  . $result[2] . '\\' . $solicitacao->getImageName());
                    // dd('C:\workspace\nirron\nirron\public\images\products' . $solicitacao->getImageName());


                } catch (IOExceptionInterface $exception) {
                    echo "An error occurred while creating your directory at " . $exception->getPath();
                }

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
                if (in_array('ROLE_APROVADOR_ADMINISTRATIVO', $this->getUser()->getRoles())) {
                    $this->addFlash(
                        'success',
                        'Solicitação recusada com sucesso.'
                    );
                    return $this->redirectToRoute('app_aprovador_administrativo_pendentes', [], Response::HTTP_SEE_OTHER);
                }
                if (in_array('ROLE_APROVADOR_OPERACIONAL', $this->getUser()->getRoles())) {
                    $this->addFlash(
                        'success',
                        'Solicitação recusada com sucesso.'
                    );
                    return $this->redirectToRoute('app_aprovador_operacional_pendentes', [], Response::HTTP_SEE_OTHER);
                }
            }
            if (in_array('ROLE_ADMINISTRADOR_ASSESSORIA', $this->getUser()->getRoles()) || in_array('ROLE_ADMINISTRADOR_LOGISTICA', $this->getUser()->getRoles()) || in_array('ROLE_SUPER', $this->getUser()->getRoles())) {
                $solicitacao->setStatus(Solicitacao::STATUS_ADMINISTRADOR_RECUSADO);
                $solicitacao->setUpdatedAt(new \DateTimeImmutable('now'));
                $solicitacao->setRecusador($this->getUser());
                $solicitacao->setRecusa($request->request->get('recusa'));
                $solicitacaoRepository->update($solicitacao, true);
                if ($solicitacao->getEmpresa() == 'assessoria') {
                    $this->addFlash(
                        'success',
                        'Solicitação recusada com sucesso.'
                    );
                    return $this->redirectToRoute('app_administrador_assessoria_pre_aprovados', [], Response::HTTP_SEE_OTHER);
                }
                if ($solicitacao->getEmpresa() == 'logistica') {
                    $this->addFlash(
                        'success',
                        'Solicitação recusada com sucesso.'
                    );
                    return $this->redirectToRoute('app_administrador_logistica_pre_aprovados', [], Response::HTTP_SEE_OTHER);
                }
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
