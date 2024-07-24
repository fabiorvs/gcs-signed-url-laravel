<?php

namespace App\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;

class GoogleCloudStorageService
{
    protected $storageClient;
    protected $bucket;

    public function __construct()
    {
        $this->storageClient = new StorageClient([
            'projectId' => config('googlecloud.project_id'),
            'keyFilePath' => config('googlecloud.key_file'),
        ]);

        $this->bucket = $this->storageClient->bucket(config('googlecloud.storage_bucket'));
    }

    public function generateSignedUrl($filename)
    {
        Log::info("Generating signed URL for file: " . $filename);
        try {
            $object = $this->bucket->object($filename);
            Log::info("Object retrieved from bucket: " . $filename);
            $url = $object->signedUrl(new \DateTime('1 hour'), [
                'version' => 'v4',
            ]);
            Log::info("Signed URL generated: " . $url);
            return $url;
        } catch (\Exception $e) {
            Log::error('Error generating signed URL for file ' . $filename . ': ' . $e->getMessage());
            throw $e;
        }
    }

    public function getHtmlContentFromBucket($filename)
    {
        Log::info("Getting HTML content from bucket for file: " . $filename);
        try {
            $object = $this->bucket->object($filename);
            $contents = $object->downloadAsString();
            Log::info("HTML content retrieved from bucket for file: " . $filename);
            return $contents;
        } catch (\Exception $e) {
            Log::error('Error getting HTML content from bucket for file ' . $filename . ': ' . $e->getMessage());
            throw $e;
        }
    }

    public function processHtmlContent($htmlContent, $baseDir)
    {
        Log::info("Processing HTML content");
        $urlRegex = '/(src|href)="([^"]+)"/i';
        $matches = [];
        preg_match_all($urlRegex, $htmlContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $attribute = $match[1];
            $url = $match[2];
            if ($url) {
                $absolutePath = $baseDir . '/' . $url;
                Log::info("Processing URL: " . $absolutePath);
                try {
                    $signedUrl = $this->generateSignedUrl($absolutePath);
                    $htmlContent = str_replace($url, $signedUrl, $htmlContent);
                    Log::info("Replaced URL with signed URL: " . $signedUrl);
                } catch (\Exception $e) {
                    Log::error('Error generating signed URL for ' . $url . ': ' . $e->getMessage());
                }
            }
        }

        return $htmlContent;
    }
}
