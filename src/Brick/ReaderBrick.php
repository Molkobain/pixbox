<?php

namespace Molkobain\iTop\Extension\Pixbox\Portal\Brick;

use Combodo\iTop\Portal\Brick\PortalBrick;

/**
 * Class ReaderBrick
 *
 * @package Molkobain\iTop\Extension\Pixbox\Portal\Brick
 */
class ReaderBrick extends PortalBrick
{
	const ENUM_SUPPORTED_MEDIA_TYPE_IMAGE = 'image';
	const ENUM_SUPPORTED_MEDAI_TYPE_VIDEO = 'video';

    const DEFAULT_MESSAGES_PAGINATION = 10;
    const DEFAULT_PAGE_TEMPLATE_PATH = 'molkobain-pixbox/templates/layout.html.twig';

    static $sRouteName = 'p_pixbox_reader';

	/**
	 * @return array
	 */
    public static function GetSupportedMediaTypes()
    {
    	return array(
    		static::ENUM_SUPPORTED_MEDIA_TYPE_IMAGE,
		    static::ENUM_SUPPORTED_MEDAI_TYPE_VIDEO,
	    );
    }
}