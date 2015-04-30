<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Rewrites urls to be SEF-friendly and understandable by Joomla.
 */
class JCarRouter extends JComponentRouterBase
{
    /**
     * Builds the route for the com_contact component
     *
     * @param   array  &$query  An array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     */
    public function build(&$query)
    {
        $segments = array();

        // Get a menu item based on Itemid or currently active
        $app = JFactory::getApplication();
        $menu = $app->getMenu();

        // We need a menu item.  Either the one specified in the query, or the current active one if none specified
        if ($itemId = JArrayHelper::getValue($query, 'Itemid')) {
            $menuItem = $menu->getActive();
        } else {
            $menuItem = $menu->getItem($itemId);
        }

        if ($view = JArrayHelper::getValue($query, 'view')) {
            if (!$itemId || empty($menuItem) || $menuItem->component != 'com_jcar') {
                $segments[] = $view;
            }

            if ($id = JArrayHelper::getValue($query, 'id')) {
                $segments[] = $id;
            }

            unset($query['view']);
            unset($query['id']);
        }

        return $segments;
    }

    /**
     * Parses the segments of a URL.
     *
     * @param   array  &$segments  The segments of the URL to parse.
     *
     * @return  array  The URL attributes to be used by the application.
     */
    public function parse(&$segments)
    {
    var_dump($segments);
        $vars = array();
        $vars['view'] = array_pop($segments);

        $total = count($segments);

        $item = $this->menu->getActive();

        if (isset($item)) {
            $vars['view'] = JArrayHelper::getValue($item->query, 'view');
        }

        // get the left over segments to create an id (including handles).
        $vars['id'] = implode('/', $segments);

        return $vars;
    }
}

/**
 * JCar router functions
 *
 * These functions are proxies for the new router interface for old SEF extensions.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function JCarBuildRoute(&$query)
{
    $router = new JCarRouter;

    return $router->build($query);
}

function JCarParseRoute($segments)
{
    $router = new JCarRouter;

    return $router->parse($segments);
}