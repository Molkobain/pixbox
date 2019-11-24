<?php

namespace Molkobain\iTop\Extension\Pixbox\Portal\Brick;


use Combodo\iTop\DesignElement;
use Combodo\iTop\Portal\Brick\PortalBrick;

class ReaderBrick extends PortalBrick
{
    const DEFAULT_PAGE_TEMPLATE_PATH = 'molkobain-pixbox/templates/layout.html.twig';

    static $sRouteName = 'p_pixbox_reader';
}