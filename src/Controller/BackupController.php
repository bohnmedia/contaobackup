<?php

namespace BohnMedia\ContaoBackupBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class BackupController
{

  public function loadAction(): Response
  {
		return new Response("Hello World!");
  }

}
		