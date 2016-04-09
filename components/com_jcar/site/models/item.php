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

        if ($this->item !== null) {
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

    /**
     * Generates a menu item based on the JCar item's title.
     *
     * By referencing the JCar item using a menu item, Joomla will be able to
     * generate a search engine-friendly url.
     *
     * @return  mixed  The menu item id if a menu item is generated, or false
     * if the menu item already exists.
     */
    public function generateSefMenuItem()
    {
        $url = new JUri('index.php');
        $url->setVar("option", "com_jcar");
        $url->setVar("view", "item");
        $url->setVar("id", str_replace('/', '%2F', $this->getState('item.id')));

        $item = $this->getItem();

        $menu = JMenu::getInstance('site');

        if ($menu->getItems(array("link"), array((string)$url), true)) {
            return false;
        }

        $component = JComponentHelper::getComponent('com_jcar');
        $parentId = $component->params->get('parent_id');
        $parentMenuItem = $menu->getItem($parentId);

        $count = 0;
        $suffix = "";
        $title = reset(JArrayHelper::getValue($item->metadata, "dc.title"));

        // check to see whether any other menu items share the same name.
        do {
            $alias = JApplicationHelper::stringURLSafe($title.$suffix);
            $menuItems = $menu->getItems(
                array("alias", "parent_id"),
                array($alias, $parentMenuItem->id));

            if (count($menuItems)) {
                $count++;
                $suffix = " ".$count;
            } else {
                $exists = false;
            }
        } while ($exists);

        $title = $title.$suffix;

        $menuItem = array(
            'menutype'=>$parentMenuItem->menutype,
            'title'=>$title,
            'type'=>'component',
            'component_id'=>10089,
            'link'=>'index.php?option=com_jcar&view=item&id='.$this->getState('item.id'),
            'language'=>'*',
            'published'=>1,
            'parent_id'=>$parentMenuItem->id
        );

        $menuTable = JTable::getInstance('Menu', 'JTable', array());

        $menuTable->setLocation($parentMenuItem->id, 'last-child');

        if (!$menuTable->save($menuItem)) {
            throw new Exception($menuTable->getError());
            return false;
        }

        return $menuTable->id;
    }
}
