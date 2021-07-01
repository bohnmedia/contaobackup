<?php

namespace BohnMedia\ContaoBackupBundle;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Ifsnop\Mysqldump\Mysqldump;

class ContaoBackup {

    protected $rootDir;
    protected $zip;
    protected $zipFilePath;
    protected $dumpFilePath;
    protected $composerFilePath;
    protected $stop;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
        $this->zipFilePath = $this->rootDir . '/var/cache/backup.zip';
        $this->dumpFilePath = $this->rootDir . '/var/cache/dump.sql';
        $this->composerFilePath = $this->rootDir . '/composer.json';

        $this->stop = isset($_GET["stop"]) ? (int)$_GET["stop"] : false;
    }

    private function openZip()
    {

        // Create new zip file
        $this->zip = new \ZipArchive();
        if ($this->zip->open($this->zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
	        exit("cannot open backup file");
        }

    }

    private function closeZip()
    {
        $this->zip->close();
    }

    private function addDirToZip($path, $recursive = true) {
	
        // Absolute path
        $absPath = $this->rootDir . '/' . $path;
        
        // Check if directory exists
        if (!is_dir($absPath)) {
            return;
        }
        
        // Scan directory
        $files = scandir($absPath);
        foreach($files as $file) {
                    
            // Skip parent folder and this folder
            if ($file === "." || $file === "..") continue;
            
            // Filename with path
            $absFile = $absPath . "/" . $file;
            $relFile = $path . "/" . $file;
            
            // Check if $file is a directory
            if (is_dir($absFile) && $recursive) {
                $this->addDirToZip($relFile);
            }
            
            // Add File to zip
            if (is_file($absFile)) {
                var_dump('add file');
                $this->zip->addFile($absFile, $relFile);
            }
    
        }
    }

    private function dumpDatabase() {

        // Generate pdo path
        $dsn = 'mysql:dbname=' . $GLOBALS['TL_CONFIG']['dbDatabase'] . ';host=' . $GLOBALS['TL_CONFIG']['dbHost'];
        if ($GLOBALS['TL_CONFIG']['dbPort']) $dsn .= ';port=' . $GLOBALS['TL_CONFIG']['dbPort'];

        // Dump sql file
        $dump = new Mysqldump($dsn, $GLOBALS['TL_CONFIG']['dbUser'], $GLOBALS['TL_CONFIG']['dbPass']);
        $dump->start($this->dumpFilePath);

    }

    public function binaryFileResponse()
    {

        // Create and open zip file
        $this->openZip();

        // Backup folders
        var_dump("add directories");
        $this->addDirToZip('files');
        $this->addDirToZip('templates');
        $this->addDirToZip('config');
        $this->addDirToZip('system/config');
        $this->addDirToZip('contao-manager', false);
        var_dump(is_file($this->zipFilePath));
        if ($this->stop === 1) exit();

        // Backup database
        $this->dumpDatabase();
        if ($this->stop === 2) exit();
        $this->zip->addFile($this->dumpFilePath, 'dump.sql');
        if ($this->stop === 3) exit();
        unlink($this->dumpFilePath);
        if ($this->stop === 4) exit();

        // Backup composer.json
        $this->zip->addFile($this->composerFilePath, 'composer.json');
        if ($this->stop === 5) exit();

        // Close zip file
        $this->closeZip();
        if ($this->stop === 6) exit();

        // Send zip file to client
        $response = new BinaryFileResponse($this->zipFilePath);
        $response->deleteFileAfterSend();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'backup-' . date('Y-m-d_H-m-s') . '.zip'
        );
        return $response;

    }

}