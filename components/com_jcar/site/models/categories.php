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
 * Models all categories from an archive.
 */
class JCarModelCategories extends JModelList
{
    protected $items;

    public function getItems()
    {
        if ($this->items === null) {
            $this->items = array();
        }

        $plugin = null;

        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('jcar', $plugin);

        // Trigger the data preparation event.
        $responses = $dispatcher->trigger('onJCarCategoriesRetrieve');

        // loop through responses until we find a valid one.
        $valid = false;

        $this->items = null;

        while (($response = current($responses)) && !$valid) {
            if ($response !== null) {
                $valid = true;
                $this->items = $response;
            }

            next($responses);
        }

        return $this->items;
    }
}