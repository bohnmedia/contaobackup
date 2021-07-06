<?php

namespace BohnMedia\ContaoBackupBundle;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Ifsnop\Mysqldump\Mysqldump;
use Contao\Config;

class ContaoBackup {

    protected $rootDir;
    protected $zip;
    protected $zipFilePath;
    protected $dumpFilePath;
    protected $composerFilePath;
    protected $directories = ['app', 'files', 'templates', 'config', 'system/config', 'contao', 'src', '_external'];
    protected $files = ['composer.json'];

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
        $this->zipFilePath = $this->rootDir . '/var/cache/backup.zip';
        $this->dumpFilePath = $this->rootDir . '/var/cache/dump.sql';
        $this->composerFilePath = $this->rootDir . '/composer.json';
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

    private function addFilesFromDirectory($path, &$arrFiles = [])
    {

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
            
            // Check if file is a directory
            if (is_dir($absFile) && $recursive) {
                $this->getFilesByDirectory($relFile, $arrFiles);
            }
            
            // Add File to zip
            if (is_file($absFile)) {
                array_push($arrFiles, $relFile);
            }
    
        }

        return $arrFiles;

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

    public function list()
    {
        $arrFiles = [];

        foreach($this->directories as $directory) {
            $this->addFilesFromDirectory($directory, $arrFiles);
        }

        var_dump($arrFiles);
        exit();

    }

    public function download()
    {

        // Create and open zip file
        $this->openZip();

        // Backup folders
        $this->addDirToZip('app');
        $this->addDirToZip('files');
        $this->addDirToZip('templates');
        $this->addDirToZip('config');
        $this->addDirToZip('system/config');
        $this->addDirToZip('contao-manager', false);
        $this->addDirToZip('contao');
        $this->addDirToZip('src');
        $this->addDirToZip('_external');

        // Backup database
        $this->dumpDatabase();
        $this->zip->addFile($this->dumpFilePath, 'dump.sql');

        // Backup composer.json
        $this->zip->addFile($this->composerFilePath, 'composer.json');

        // Close zip file
        $this->closeZip();

        // Delete database dump
        unlink($this->dumpFilePath);

        // Send zip file to client
        $response = new BinaryFileResponse($this->zipFilePath);
        $response->deleteFileAfterSend(true);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'backup-' . date('Y-m-d_H-m-s') . '.zip'
        );
        return $response;

    }

    public function list()
    {

    }

    public function initializeSystem(): void
    {

        // Skip if backupKey exists
        $objConfig = Config::getInstance();
        if ($objConfig->has('backupKey')) return;

        // Generate key
        $backupKey = '';
        $allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $max = strlen($allowedChars) - 1;
        for ($i=0; $i<32; $i++) {
            $backupKey .= substr($allowedChars, rand(0, $max), 1);
        }
        
        // Update localconfig
        $objConfig->add("\$GLOBALS['TL_CONFIG']['backupKey']", $backupKey);
        $objConfig->save();

    }

}
