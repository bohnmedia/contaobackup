<?php

namespace BohnMedia\ContaoBackupBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BackupController
{

  public function loadAction(Request $request): Response
  {
    
    $key = $request->query->get('key');

		return new Response("Hello World! " . $key);

  }

}
		