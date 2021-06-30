<?php

namespace BohnMedia\ContaoBackupBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class BackupController
{
    public function loadAction($alias): Response
    {
		// Response
		$response = new Response("Controller works!");
		return $response;
    }
}
		