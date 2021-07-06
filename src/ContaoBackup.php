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

    private function addFilesFromDirectory($path, &$arrFiles)
    {

        // Absolute path
        $absPath = realpath($this->rootDir . '/' . $path);
        
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
            if (is_dir($absFile)) {
                $this->addFilesFromDirectory($relFile, $arrFiles);
            }
            
            // Add File to list
            if (is_file($absFile)) {
                $this->addFile($relFile, $arrFiles);
            }
    
        }

    }

    private function addFile($path, &$arrFiles)
    {

        // Absolute path
        $absFile = realpath($this->rootDir . '/' . $path);

        // Add File to zip
        if (is_file($absFile)) {
            array_push($arrFiles, [
                $path,
                md5_file($absFile)
            ]);
        }

    }

    public function dumpdb()
    {

        // Generate pdo path
        $dsn = 'mysql:dbname=' . $GLOBALS['TL_CONFIG']['dbDatabase'] . ';host=' . $GLOBALS['TL_CONFIG']['dbHost'];
        if ($GLOBALS['TL_CONFIG']['dbPort']) $dsn .= ';port=' . $GLOBALS['TL_CONFIG']['dbPort'];

        // Dump sql file
        $dump = new Mysqldump($dsn, $GLOBALS['TL_CONFIG']['dbUser'], $GLOBALS['TL_CONFIG']['dbPass']);
        $dump->start($this->dumpFilePath);

        // Send file
        return new BinaryFileResponse($this->dumpFilePath);

    }

    public function list(): Response
    {
        $arrFiles = [];

        foreach($this->directories as $directory) {
            $this->addFilesFromDirectory($directory, $arrFiles);
        }

        foreach($this->files as $file) {
            $this->addFile($file, $arrFiles);
        }

        return new Response(json_encode($arrFiles), Response::HTTP_OK, ['content-type' => 'application/json']);

    }

    public function file()
    {

        $name = isset($_GET["name"]) ? $_GET["name"] : "";

        // No name set
        if (!$name) {
            return new Response('Missing name parameter', Response::HTTP_BAD_REQUEST);
        }

        // Absolute path
        $absPath = realpath($this->rootDir . '/' . $name);
        $file = is_file($absPath) ? $absPath : "";

        // File not found
        if (!$file) {
            return new Response('File not found', Response::HTTP_NOT_FOUND);
        }

        // Block requests, if someone tries to break out the root directory
        if (strpos($file, $this->rootDir) !== 0) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        // Send file
        return new BinaryFileResponse($file);
   
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
