<?php
/**
 * @package     JCar.Component
 * @subpackage  Site
 *
 * @copyright   Copyright (C) 2015-2017 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Models all categories from an archive.
 */
class JCarModelCategories extends JModelList
{
    protected $items = null;

    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $app = JFactory::getApplication();

        $plugin = $app->input->getCmd('plugin', null);

        $this->setState('plugin', $plugin);

        $params = $app->getParams();
        $this->setState('params', $params);
    }

    public function getItems()
    {
        if ($this->items === null) {
            $this->items = array();
        }

        $plugin = $this->getState('plugin');

        $dispatcher = JEventDispatcher::getInstance();
        JPluginHelper::importPlugin('jcar', $plugin);

        // Trigger the data preparation event.
        $responses = $dispatcher->trigger('onJCarCategoriesRetrieve');

        // loop through responses until we find a valid one.
        $valid = false;

        while (($response = current($responses))) {
            if ($response !== null) {
                $this->items = array_merge($this->items, $response);
            }

            next($responses);
        }

        return $this->items;
    }
}
