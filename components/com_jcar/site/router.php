<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
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
        $segments = [];

        $view = JArrayHelper::getValue($query, 'view');
        $layout = JArrayHelper::getValue($query, 'layout');
        $id = JArrayHelper::getValue($query, 'id');

        // Get a menu item based on Itemid or currently active
        $app = JFactory::getApplication();

        if ($itemId = JArrayHelper::getValue($query, 'Itemid')) {
            $menuItem = $this->menu->getItem($itemId);
        } else {
            $menuItem = $this->menu->getActive();
        }

        $mView = JArrayHelper::getValue($menuItem->query, 'view', null);
        $mLayout = JArrayHelper::getValue($menuItem->query, 'layout', null);
        $mId = JArrayHelper::getValue($menuItem->query, 'id', null);

        if ($view) {
            if (!$itemId ||
                empty($menuItem) ||
                empty($menuItem->component) ||
                $menuItem->component != 'com_jcar' ||
                ($view != 'category' && $mView != $view)) {
                $segments[] = $view;
            }

            unset($query['view']);
        }

        if ($view && ($mView == $view) && ($id) && ($mId == $id)) {
            unset($query['view']);
            unset($query['id']);

            return $segments;
        }

        if ($mView == 'item') {
            $id = str_replace('/', urlencode('%2F'), $id);

            JTable::addIncludePath(JPATH_ROOT."/administrator/components/com_jcar/tables");
            $table = JTable::getInstance('Route', 'JCarTable');

            if ($table->load(['item_id'=>$id])) {
                $segments[] = $table->alias;
            } else {
                $segments[] = $id;
            }

            unset($query['id']);
        }

        if ($layout) {
            if ($itemId && $mLayout) {
                if ($layout == $mLayout) {
                    unset($query['layout']);
                }
            } else {
                if ($mLayout == 'default') {
                    unset($query['layout']);
                }
            }
        }

        if ($view == 'asset') {
            $segments[] = $query['name'];

            unset($query['format']);
            unset($query['name']);
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
            $mView = JArrayHelper::getValue($item->query, 'view');
            $mLayout = JArrayHelper::getValue($item->query, 'layout');

            if (($mView == "categories" || $mView == "item") && count($segments) == 1) {
                $vars['view'] = ($mView == "categories") ? "category" : $mView;
                $vars['layout'] = $mLayout;
            } else {
                $vars['view'] = array_shift($segments);
            }

            $vars['id'] = array_shift($segments);
        } else {
            $vars['view'] = array_shift($segments);
            $vars['id'] = array_shift($segments);
        }

        if ($vars['view'] == 'asset') {
            $vars['format'] = 'raw';

            if ($name = array_shift($segments)) {
                $vars['name'] = $name;
            }
        }

        // if item, assume the id is the item's alias. Convert to id.
        if ($vars['view'] == 'item') {
            if ($alias = \Joomla\Utilities\ArrayHelper::getValue($vars, 'id')) {
                JTable::addIncludePath(JPATH_ROOT."/administrator/components/com_jcar/tables");
                $table = JTable::getInstance('Route', 'JCarTable');

                if ($table->load(['alias'=>$alias])) {
                    $vars['id'] = $table->item_id;
                }
            }
        }

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
