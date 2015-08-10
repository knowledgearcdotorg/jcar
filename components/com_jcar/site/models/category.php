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
 * Models an category from an archive.
 */
class JCarModelCategory extends JModelItem
{
    protected $item;

    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication();

        $pk = $app->input->getString('id');
        $this->setState('category.id', $pk);
    }

    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : $this->getState('category.id');

        if ($this->item === null) {
            $this->item = array();
        }

        if (!JArrayHelper::getValue($this->item, $pk)) {
            $parts = explode(":", $pk, 2);

            $plugin = null;

            if (count($parts) == 2) {
                $plugin = JArrayHelper::getValue($parts, 0);
            }

            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('jcar', $plugin);

            // Trigger the data preparation event.
            $responses = $dispatcher->trigger('onJCarCategoryRetrieve', array($pk));

            // loop through responses until we find a valid one.
            $valid = false;

            $this->item[$pk] = null;

            while (($response = current($responses)) && !$valid) {
                if ($response !== null) {
                    $valid = true;
                    $this->item[$pk] = $response;
                }

                next($responses);
            }
        }

        return JArrayHelper::getValue($this->item, $pk);
    }
}