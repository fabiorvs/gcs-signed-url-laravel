<?php

namespace App\Http\Controllers;

use App\Services\GoogleCloudStorageService;

class HtmlController extends Controller
{
    protected $gcsService;

    public function __construct(GoogleCloudStorageService $gcsService)
    {
        $this->gcsService = $gcsService;
    }

    public function serveFile($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if ($extension === 'html') {
            return $this->serveHtml($filename);
        } else {
            return $this->redirectFile($filename);
        }
    }

    public function serveHtml($filename)
    {
        try {
            $htmlContent = $this->gcsService->getHtmlContentFromBucket($filename);
            $baseDir = dirname($filename);
            $processedHtml = $this->gcsService->processHtmlContent($htmlContent, $baseDir);
            return response($processedHtml, 200)->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            \Log::error('Erro ao processar o arquivo HTML: ' . $e->getMessage());
            return response('Erro ao processar o arquivo HTML', 500);
        }
    }

    public function redirectFile($filename)
    {
        try {
            $signedUrl = $this->gcsService->generateSignedUrl($filename);
            return redirect($signedUrl);
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar a URL assinada: ' . $e->getMessage());
            return response('Erro ao gerar a URL assinada', 500);
        }
    }
}
