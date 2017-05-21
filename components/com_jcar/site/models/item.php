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
    const TITLE_MAX_LENGTH = 254;

    const ALIAS_MAX_LENGTH = 190;

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

        $params = $app->getParams();
        $this->setState('params', $params);
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

    public function doesRouteExist()
    {

    }

    /**
     * Generates a menu item based on the JCar item's title.
     *
     * By referencing the JCar item using a menu item, Joomla will be able to
     * generate a search engine-friendly url.
     *
     * @return  mixed      A url based on the alias or false if no url is generated.
     *
     * @throw   Exception  If the new route cannot be saved.
     */
    public function generateSefRoute()
    {
        JModelLegacy::addIncludePath(JPATH_ROOT.'/administrator/components/com_jcar/models');
        $model = JModelLegacy::getInstance("Route", "JCarModel", ['ignore_request'=>true]);

        $component = JComponentHelper::getComponent('com_jcar');

        if (!(int)$component->params->get('sef_generate', 0)) {
            return false;
        }

        $item = $this->getItem();

        $metadata = $item->metadata;

        $titles = JArrayHelper::getValue($metadata, "dc.title", []);
        $title = reset($titles);

        $dates = JArrayHelper::getValue($metadata, "dc.date.issued", []);

        if ($dates) {
            $issued = reset($dates);
        }
        
        $isNewRoute = false;
        $count = 0;
        $suffix = "";

        // keep producing aliases until we have a unique one.
        do {
            $unique = true;

            $urlSafeSuffix = JApplicationHelper::stringURLSafe($suffix);

            // increment by one to represent the "-" in the suffix when there is a suffix.
            if (($urlSafeSuffixLength = strlen($urlSafeSuffix)) > 0) {
                $urlSafeSuffixLength++;
            }

            $alias = substr(JApplicationHelper::stringURLSafe($title), 0, self::ALIAS_MAX_LENGTH - $urlSafeSuffixLength);

            if ($urlSafeSuffix) {
                $alias .= "-".$urlSafeSuffix;
            }

            $table = $model->getTable('Route');

            if ($table->load(["alias"=>$alias])) {
                if ($table->item_id !== $this->getState('item.id')) {
                    // try to make unique with date otherwise just start incrementing the suffix.
                    if ($issued) {
                        if ($count) {
                            $suffix .= " ".$count;
                        } else {
                            $suffix = $issued;
                        }
                    } else {
                        $suffix = $count;
                    }

                    $count++;

                    $unique = false;
                }
            } else {
                $isNewRoute = true;
            }
        } while (!$unique);

        if ($isNewRoute) {
            // increment by one to represent the " " in the suffix when there is a suffix.
            if (($suffixLength = strlen($suffix)) > 0) {
                $suffixLength++;
            }

            $title = substr($title, 0, self::TITLE_MAX_LENGTH - $suffixLength);

            if ($suffix) {
                $title .= " ".$suffix;
            }

            $data = [
                "title"=>$title,
                "alias"=>$alias,
                "item_id"=>$this->getState('item.id'),
                "state"=>1,
                "language"=>'*'
            ];

            if (!$model->save($data)) {
                throw new Exception($model->getError());
            }

            return JCarHelperRoute::getItemRoute($this->getState('item.id'));
        }

        return false;
    }
}
