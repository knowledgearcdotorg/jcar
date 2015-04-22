<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Provides helper methods for the component.
 */
class JCarHelper
{
    public static $extension = 'com_jcar';

    /**
     * Configure the Linkbar.
     *
     * @param string $vName The name of the active view.
     *
     * @return void
     */
    public static function addSubmenu($vName)
    {
        JHtmlSidebar::addEntry(
            JText::_('COM_JSPACE_SUBMENU_CPANEL'),
            'index.php?option=com_jspace',
            $vName == 'cpanel'
        );
    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @param   int     The category ID.
     *
     * @return  JObject
     * @since   1.6
     */
    public static function getActions($categoryId = 0)
    {
        $user   = JFactory::getUser();
        $result = new JObject();

        if (empty($categoryId)) {
            $assetName = 'com_jcar';
        } else {
            $assetName = 'com_jcar.category.'.(int)$categoryId;
        }

        $actions = array(
            'core.admin',
            'core.manage',
            'core.create',
            'core.edit',
            'core.edit.own',
            'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
}