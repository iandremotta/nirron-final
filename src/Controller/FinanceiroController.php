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
use App\Service\FileUploader;
use DateInterval;
use DateTime;
use Vich\UploaderBundle\Handler\DownloadHandler;
use ZipArchive;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/financeiro')]
class FinanceiroController extends AbstractController
{
    #[Route('/assessoria/hoje', name: 'app_financeiro_assessoria_hoje', methods: ['GET'])]
    public function assessoriaHoje(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $solicitacoes = $solicitacaoRepository->findAllByEmpresaStatusHoje(Solicitacao::EMPRESA_ASSESSORIA, Solicitacao::STATUS_ADMINISTRADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('financeiro/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/assessoria/semana', name: 'app_financeiro_assessoria_semana', methods: ['GET'])]
    public function assessoriaSemana(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $solicitacoes = $solicitacaoRepository->findAllByEmpresaStatusSemana(Solicitacao::EMPRESA_ASSESSORIA, Solicitacao::STATUS_ADMINISTRADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('financeiro/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/assessoria/todos', name: 'app_financeiro_assessoria_todos', methods: ['GET'])]
    public function assessoriaTodos(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $solicitacoes = $solicitacaoRepository->findAllByEmpresaStatus(Solicitacao::EMPRESA_ASSESSORIA, Solicitacao::STATUS_ADMINISTRADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('financeiro/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/assessoria/download', name: 'app_assessoria_download', methods: ['GET', 'POST'])]
    public function download(SolicitacaoRepository $solicitacaoRepository, Request $request, DownloadHandler $downloadHandler): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->get("data");
            $datas = array();
            if ($data == "") {
                $data = date("d/m/Y");
                array_push($datas, $data);
            }
            $datas = explode("-", $data);

            $solicitacoes = $solicitacaoRepository->findByBoleto(Solicitacao::EMPRESA_ASSESSORIA, Solicitacao::STATUS_ADMINISTRADOR_OK, $datas)->getResult();
            if ($solicitacoes == []) {
                $this->addFlash(
                    'error',
                    'Nenhum boleto encontrado para o período!'
                );
                return $this->redirect($request->headers->get('referer'));
            }
            $files = [];
            foreach ($solicitacoes as $solicitacao) {
                array_push($files, 'images/products/' . $solicitacao->getImageName());
            }

            $zip = new \ZipArchive();
            $name =  $request->request->get("data");
            $name = implode(",", $datas);
            $name = str_replace("/", "-", $name);
            $name = str_replace(",", "_", $name);
            $zipName = $name . ".zip";
            $zip->open($zipName,  \ZipArchive::CREATE);
            foreach ($files as $file) {
                $zip->addFromString(basename($file),  file_get_contents($file));
            }
            $zip->close();

            $response = new Response(file_get_contents($zipName));
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
            $response->headers->set('Content-length', filesize($zipName));

            @unlink($zipName);

            return $response;
        }
    }

    #[Route('/logistica/hoje', name: 'app_financeiro_logistica_hoje', methods: ['GET'])]
    public function index(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $solicitacoes = $solicitacaoRepository->findAllByEmpresaStatusHoje(Solicitacao::EMPRESA_LOGISTICA, Solicitacao::STATUS_ADMINISTRADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('financeiro/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/logistica/semana', name: 'app_financeiro_logistica_semana', methods: ['GET'])]
    public function logisticaSemana(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $solicitacoes = $solicitacaoRepository->findAllByEmpresaStatusSemana(Solicitacao::EMPRESA_LOGISTICA, Solicitacao::STATUS_ADMINISTRADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('financeiro/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/logistica/todos', name: 'app_financeiro_logistica_todos', methods: ['GET'])]
    public function logisticaTodos(SolicitacaoRepository $solicitacaoRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $solicitacoes = $solicitacaoRepository->findAllByEmpresaStatus(Solicitacao::EMPRESA_LOGISTICA, Solicitacao::STATUS_ADMINISTRADOR_OK);

        $pagination = $paginator->paginate($solicitacoes, $request->query->getInt('page', 1), 10);
        return $this->render('financeiro/index.html.twig', [
            'solicitacoes' => $pagination,
            'nav_active' => "Solicitação"
        ]);
    }

    #[Route('/logistica/download', name: 'app_logistica_download', methods: ['GET', 'POST'])]
    public function logisticaDownload(SolicitacaoRepository $solicitacaoRepository, Request $request, DownloadHandler $downloadHandler): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->get("data");
            $datas = array();
            if ($data == "") {
                $data = date("d/m/Y");
                array_push($datas, $data);
            }
            $datas = explode("-", $data);

            $solicitacoes = $solicitacaoRepository->findByBoleto(Solicitacao::EMPRESA_LOGISTICA, Solicitacao::STATUS_ADMINISTRADOR_OK, $datas)->getResult();
            if ($solicitacoes == []) {
                $this->addFlash(
                    'error',
                    'Nenhum boleto encontrado para o período!'
                );
                return $this->redirect($request->headers->get('referer'));
            }
            $files = [];
            foreach ($solicitacoes as $solicitacao) {
                array_push($files, 'images/products/' . $solicitacao->getImageName());
            }

            $zip = new \ZipArchive();
            $name =  $request->request->get("data");
            $name = implode(",", $datas);
            $name = str_replace("/", "-", $name);
            $name = str_replace(",", "_", $name);
            $zipName = $name . ".zip";
            $zip->open($zipName,  \ZipArchive::CREATE);
            foreach ($files as $file) {
                $zip->addFromString(basename($file),  file_get_contents($file));
            }
            $zip->close();

            $response = new Response(file_get_contents($zipName));
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $zipName . '"');
            $response->headers->set('Content-length', filesize($zipName));

            @unlink($zipName);

            return $response;
        }
    }
}
