<?php

namespace BohnMedia\ContaoBackupBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Config;
use BohnMedia\ContaoBackupBundle\ContaoBackup;

class BackupController
{

  private $framework;
  private $config;
  private $contaobackup;

  public function __construct(ContaoFramework $framework, ContaoBackup $contaobackup)
  {
      $this->framework = $framework;
      $this->config = $this->framework->getAdapter(Config::class);
      $this->contaobackup = $contaobackup;
  }
  
  public function loadAction(Request $request): Response
  {

    $serverKey = $this->config->get('backupKey');
    $clientKey = $request->query->get('key');
    $response = new Response();

    // No key
    if (!$clientKey) {
      $response->setContent('Please use a key');
      $response->headers->set('Content-Type', 'text/plain');
      $response->setStatusCode(400);
      return $response;
    }

    // Wrong key
    if ($clientKey !== $serverKey) {
      $response->setContent('Wrong key');
      $response->headers->set('Content-Type', 'text/plain');
      $response->setStatusCode(401);
      return $response;
    }

    return $this->contaobackup->binaryFileResponse();

  }

  public function generateDefaultPassword(Request $request): Response
  {
    $this->contaobackup->generateDefaultPassword();
    return new Response();
  }

}
		