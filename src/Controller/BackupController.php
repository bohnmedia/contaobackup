<?php

namespace BohnMedia\ContaoBackupBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class BackupController
{

  public function loadAction($alias): Response
  {
		return new Response("Hello World!");
  }

}
		