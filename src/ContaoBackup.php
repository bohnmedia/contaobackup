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

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
        $this->zipFilePath = $this->rootDir . '/var/cache/backup.zip';
        $this->dumpFilePath = $this->rootDir . '/var/cache/dump.sql';
        $this->composerFilePath = $this->rootDir . '/composer.json';
    }

    private function openZip()
    {
        // Delete zip if it exists
        if (is_file($this->zipFilePath)) {
            unlink($this->zipFilePath);
        }

        // Test
        $myfile = fopen($this->zipFilePath, "w") or die("Unable to open file!");
        fclose($myfile);

        var_dump("open");
        var_dump($this->zipFilePath);
        var_dump(is_file($this->zipFilePath));
        exit();

        // Create new zip file
        $this->zip = new \ZipArchive();
        if ($this->zip->open($this->zipFilePath, \ZipArchive::CREATE) !== TRUE) {
	        exit("cannot open backup file");
        }

        var_dump("open");
        var_dump($this->zipFilePath);
        var_dump(is_file($this->zipFilePath));

    }

    private function closeZip()
    {
        var_dump("close");
        var_dump(is_file($this->zipFilePath));
        exit();
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
        $this->addDirToZip('files');
        $this->addDirToZip('templates');
        $this->addDirToZip('config');
        $this->addDirToZip('system/config');
        $this->addDirToZip('contao-manager', false);

        // Backup database
        $this->dumpDatabase();
        $this->zip->addFile($this->dumpFilePath, 'dump.sql');
        unlink($this->dumpFilePath);

        // Backup composer.json
        $this->zip->addFile($this->composerFilePath, 'composer.json');

        // Close zip file
        $this->closeZip();

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