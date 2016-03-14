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
 * Models an item from an archive.
 */
class JCarModelItem extends JModelItem
{
    protected $item;

    public function __construct($config = array())
    {
        parent::__construct($config);

        JLog::addLogger(array());
    }

    protected function populateState()
    {
        parent::populateState();

        $app = JFactory::getApplication();

        $pk = $app->input->getString('id');
        $this->setState('item.id', $pk);
    }

    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : $this->getState('item.id');

        if ($this->item === null) {
            $this->item = array();
        }

        if (!JArrayHelper::getValue($this->item, $pk)) {
            $parts = explode(":", $pk, 2);

            $plugin = null;

            if (count($parts) == 2) {
                $plugin = JArrayHelper::getValue($parts, 0);
            } else {
                JLog::add('Invalid id format', JLog::CRITICAL, 'jcar');

                // if there are not two parts, we can assume that the id is
                // missing the plugin identifier prefix.
                throw new Exception('Invalid id format', 400);
            }

            $dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('jcar', $plugin);

            try {
                $vars = array($pk, $this->get('plugin', null));

                // Trigger the data preparation event.
                $responses = $dispatcher->trigger('onJCarItemRetrieve', $vars);
            } catch (Exception $e) {
                JLog::add($e->getMessage(), JLog::CRITICAL, 'jcar');

                // error we can't recover from? throw to user.
                throw $e;
            }

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

            // if not found, fail loudly.
            if (!$valid) {
                throw new Exception("The item does not exist.", 404);
            }
        }

        return JArrayHelper::getValue($this->item, $pk);
    }
}