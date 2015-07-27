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
 * Models an item from an archive.
 */
class JCarModelCategories extends JModelList
{
    protected $items;

    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $app = JFactory::getApplication('site');

        $pk = $app->input->getString('id');
        $this->setState('item.id', $pk);
    }

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