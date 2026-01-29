<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;

class GoogleDriveService
{
    public function drive(): Drive
    {
        $client = new Client();
        $client->setAuthConfig(config('services.google.drive_sa_json'));
        $client->setScopes([Drive::DRIVE_READONLY]);
        return new Drive($client);
    }

    public function listPdfsInFolder(string $folderId, int $pageSize = 100, ?string $pageToken = null)
    {
        $drive = $this->drive();

        $q = sprintf(
            "'%s' in parents and mimeType='application/pdf' and trashed=false",
            $folderId
        );

        return $drive->files->listFiles([
            'q' => $q,
            'fields' => 'nextPageToken, files(id,name,mimeType,size,modifiedTime)',
            'pageSize' => $pageSize,
            'pageToken' => $pageToken,
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);
    }

    public function downloadFile(string $fileId): string
    {
        $drive = $this->drive();
        $resp = $drive->files->get($fileId, ['alt' => 'media']);
        return $resp->getBody()->getContents();
    }
}
