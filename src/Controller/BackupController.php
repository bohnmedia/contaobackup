<?php

namespace BohnMedia\ContaoBackupBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class BackupController
{
  protected $rootDir;
  protected $session;
  protected $framework;

  public function __construct(string $rootDir, Session $session, ContaoFramework $framework)
  {
      $this->rootDir      = $rootDir;
      $this->session      = $session;
      $this->framework    = $framework;
  }

  public function loadAction($alias): Response
  {
    // Initialize framework
    $this->framework->initialize();

		// Response
		$response = new Response("Controller works!");

    // Save session
    $this->session->save();

    // Return
		return $response;

  }
}
		