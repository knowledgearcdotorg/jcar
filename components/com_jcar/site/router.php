<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class JCarRouter extends JComponentRouterBase
{
    public function build(&$query)
    {
        return array();
    }

    public function parse(&$segments)
    {
        return array();
    }
}

/**
 * JCar router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function JCarBuildRoute(&$query)
{
    $router = new JSpaceRouter;

    return $router->build($query);
}

function JCarParseRoute($segments)
{
    $router = new JSpaceRouter;

    return $router->parse($segments);
}