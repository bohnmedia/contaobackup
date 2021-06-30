<?php

namespace BohnMedia\ContaoBackupBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Config;

class BackupController
{

  private $framework;
  private $config;

  public function __construct(ContaoFramework $framework)
  {
      $this->framework = $framework;
      $this->config = $this->framework->getAdapter(Config::class);
  }
  
  public function loadAction(Request $request): Response
  {

    $serverKey = $this->config->get('backupKey');
    $clientKey = $request->query->get('key');

		return new Response("Hello World! / " . $clientKey . " / " . $serverKey);

  }

}
		