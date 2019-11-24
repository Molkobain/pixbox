<?php

namespace Molkobain\iTop\Extension\Pixbox\Portal\Controller;

use Combodo\iTop\Portal\Controller\AbstractController;
use Combodo\iTop\Portal\Helper\ApplicationHelper;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ReaderBrickController
 *
 * @package Molkobain\iTop\Extension\Pixbox\Portal\Controller
 */
class ReaderBrickController extends AbstractController
{
	/**
	 * Method for the brick's page
	 *
	 * @param Request     $oRequest
	 * @param Application $oApp
	 * @param             $sBrickId
	 *
	 * @return Response
	 * @throws \Exception
	 */
	public function DisplayAction(Request $oRequest, Application $oApp, $sBrickId)
	{
		// Retrieving brick instance
		$oBrick = ApplicationHelper::GetLoadedBrickFromId($oApp, $sBrickId);

		// Structure the data you will pass to the TWIG (HTML templating)
		$aData = array();

		// Finally, return a Response object
		return $oApp['twig']->render($oBrick->GetPageTemplatePath(), $aData);
	}
}