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
        
        $view = JArrayHelper::getValue($query, 'view');
        $id = JArrayHelper::getValue($query, 'id');

        // Get a menu item based on Itemid or currently active
        $app = JFactory::getApplication();
        $menu = $app->getMenu();

        if ($itemId = JArrayHelper::getValue($query, 'Itemid')) {
            $menuItem = $menu->getActive();
        } else {
            $menuItem = $menu->getItem($itemId);
        }

        $mView = JArrayHelper::getValue($menuItem->query, 'view', null);
        $mId = JArrayHelper::getValue($menuItem->query, 'id', null);

        if ($view) {
            if (!$itemId || empty($menuItem) || $menuItem->component != 'com_jcar') {
                $segments[] = $view;
            }
            
            unset($query['view']);
        }

        if ($view && ($mView == $view) && ($id) && ($mId == $id)) {
            unset($query['view']);
            unset($query['catid']);
            unset($query['id']);

            return $segments;
        }
        
        if ($mId != $id || $mView != $view) {
            if ($view == 'item') {
                $segments[] = $id;
            }

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
        $vars = array();

        $item = $this->menu->getActive();

        if (isset($item)) {
            $vars['view'] = JArrayHelper::getValue($item->query, 'view');
        } else {
            $vars['view'] = array_shift($segments);
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