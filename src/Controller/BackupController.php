<?php

namespace BohnMedia\ContaoBackupBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Contao\CoreBundle\Framework\ContaoFramework;

class BackupController
{

  private $framework;

  public function __construct(ContaoFramework $framework)
  {
      $this->framework = $framework;
  }
  
  public function loadAction(Request $request): Response
  {
    
    $key = $request->query->get('key');

		return new Response("Hello World! " . $key);

  }

}
		