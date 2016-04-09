<?php
/**
 * @package     JCar.Component
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use \Joomla\Utilities\ArrayHelper;

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
        $size = (int)$size;

        if (!$size) {
            return 0;
        }

        $base = log($size, 1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    public static function cloak($value)
    {
        // remove email addresses from metadata.
        $pattern = "/[^@\s]*@[^@\s]*\.[^@\s]*/";
        $replacement =JText::_("COM_JCAR_CLOAK");

        return preg_replace($pattern, $replacement, $value);
    }

    /**
     * Parse the id.
     *
     * @param   string     $id  An id to parse.
     *
     * @return  int        A parsed id.
     *
     * @throws  Exception  Throws a 400 html error if the id does not have the
     * format dspace:{id}.
     */
    public static function parseId($id)
    {
        $parts = explode(":", $id, 2);

        if (count($parts) == 2) {
            return ArrayHelper::getValue($parts, 1);
        } else {
            JLog::add('Requested id='.$id, JLog::DEBUG, 'jcar');

            throw new Exception('Invalid id format', 400);
        }
    }
}
