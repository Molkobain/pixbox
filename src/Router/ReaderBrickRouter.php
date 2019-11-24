<?php

namespace Molkobain\iTop\Extension\Pixbox\Portal\Router;

use Combodo\iTop\Portal\Router\AbstractRouter;
use Silex\Application;

/**
 * Class ReaderBrickRouter
 *
 * @package Molkobain\iTop\Extension\Pixbox\Portal\Router
 */
class ReaderBrickRouter extends AbstractRouter
{
	static $aRoutes = array(
		array('pattern' => '/pixbox/reader/{sBrickId}',
			'callback' => 'Molkobain\\iTop\\Extension\\Pixbox\\Portal\\Controller\\ReaderBrickController::DisplayAction',
			'bind' => 'p_pixbox_reader',
			'values' => array()
		),
	);

}
