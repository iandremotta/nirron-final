<?php
// src/Service/FileUploader.php
namespace App\Service;

use App\Entity\Solicitacao;
use App\Entity\Configuracoes;
use App\Repository\SolicitacaoRepository;
use App\Repository\ConfiguracoesRepository;
use App\Service\FileUploader;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use setasign\Fpdi\Fpdi;

class Watermark
{
    private $solicitacaoRepository;
    private $configuracoesRepository;
    private $params;

    public function __construct(SolicitacaoRepository $solicitacaoRepository, ConfiguracoesRepository $configuracoesRepository)
    {
        $this->solicitacaoRepository = $solicitacaoRepository;
        $this->configuracoesRepository = $configuracoesRepository;
    }

    public function addWatermark(Solicitacao $solicitacao, $route, $user)
    {
        $empresa = $solicitacao->getEmpresa();
        $configuracoes = $this->configuracoesRepository->findLastInserted();
        $path;
        if ($empresa == 'assessoria') {
            $path = $configuracoes->getPathAssessoria();
        } else if ($empresa == 'logistica') {
            $path = $configuracoes->getPathLogistica();
        } else {
            $this->addFlash(
                'error',
                'Caminho não encontrado, solicite ao SUPER a configuração.'
            );
            return $this->redirectToRoute('app_solicitacao_show', ['id' => $solicitacao->getId()], Response::HTTP_SEE_OTHER);
        }

        $pdf = new Fpdi();
        $file = $route . '\public\temp\products\\' . $solicitacao->getImageName();
        $solicitacao->setStatus(Solicitacao::STATUS_ADMINISTRADOR_OK);
        $solicitacao->setUpdatedAt(new \DateTimeImmutable('now'));
        $solicitacao->setAdministrador($user);
        $this->solicitacaoRepository->update($solicitacao, true);
        if (file_exists($file)) {
            $result = shell_exec('"C:\Program Files\gs\\gs10.00.0\bin\gswin64c" -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dBATCH -sOutputFile="C:\inetpub\wwwroot\nirron\public\converted\\' . $solicitacao->getImageName() . '" "' . $file . '" 2>&1');
            $file = $route . '\public\converted\\' . $solicitacao->getImageName();
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
            if (!$filesystem->exists($path)) {
                $filesystem->mkdir($path, 0700);
            }
            $data = $solicitacao->getVencimento();
            $result = $data->format('Y-m-d');
            $result = explode('-', $result);

            if (!$filesystem->exists($path . '\\' . $result[0])) {
                $filesystem->mkdir($path . '\\' . $result[0], 0700);
            }

            if (!$filesystem->exists($path . '\\' . $result[0] . '\\' . $result[1])) {
                $filesystem->mkdir($path . '\\' . $result[0] . '\\'  . $result[1], 0700);
            }

            if (!$filesystem->exists($path . '\\' . $result[0] . '\\'  . $result[1] . '\\'  . $result[2])) {
                $filesystem->mkdir($path . '\\' . $result[0] . '\\'  . $result[1] . '\\'  . $result[2], 0700);
            }

            $filesystem->chmod($route . '\public\temp\products\\', 0700, 0000, true);
            copy($route . '\public\converted\\' . $solicitacao->getImageName(), $path  . '\\' . $result[0] . '\\'  . $result[1] . '\\'  . $result[2] . '\\' . $solicitacao->getImageName());
            // dd('C:\workspace\nirron\nirron\public\images\products' . $solicitacao->getImageName());


        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at " . $exception->getPath();
        }
    }
}
