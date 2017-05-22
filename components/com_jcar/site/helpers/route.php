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
 * Provides route building for displaying items using a clean url.
 */
abstract class JCarHelperRoute
{
    protected static $lookup = array();

    public static function getCategoryRoute($id, $language = 0)
    {
        $needles = array('category'=>array((string)$id));

        // Create the link
        $url = new JUri('index.php');
        $url->setVar('option', 'com_jcar');
        $url->setVar('view', 'category');
        $url->setVar('id', $id);

        if ($language && $language != "*" && JLanguageMultilang::isEnabled()) {
            $url->setVar('lang', $language);
            $needles['language'] = $language;
        }

        if ($item = self::findItem($needles)) {
            $url->setVar('Itemid', $item);
        }

        return (string)$url;
    }

    public static function getItemRoute($id, $language = 0)
    {
        $needles = ['item'=>[(string)$id]];

        // Create the link
        $url = new JUri('index.php');
        $url->setVar('option', 'com_jcar');
        $url->setVar('view', 'item');
        $url->setVar('id', $id);

        if ($language && $language != "*" && JLanguageMultilang::isEnabled()) {
            $url->setVar('lang', $language);
            $needles['language'] = $language;
        }

        $app = JFactory::getApplication();
        $menus = $app->getMenu('site');
        $component = JComponentHelper::getComponent('com_jcar');
        $items = $menus->getItems(
            ['component_id', 'link'],
            [$component->id, 'index.php?option=com_jcar&view=item']);

        if ($item = array_pop($items)) {
            JTable::addIncludePath(JPATH_ROOT."/administrator/components/com_jcar/tables");
            $table = JTable::getInstance('Route', 'JCarTable');

            if ($table->load(['item_id'=>$id])) {
                if ($table->id) {
                    $url->setVar('Itemid', $item->id);
                }
            }
        }

        return (string)$url;
    }

    protected static function findItem($needles = null)
    {
        $app = JFactory::getApplication();
        $menus = $app->getMenu('site');
        $language = isset($needles['language']) ? $needles['language'] : '*';

        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$language])) {
            self::$lookup[$language] = array();

            $component = JComponentHelper::getComponent('com_jcar');

            $attributes = ['component_id'];
            $values = [$component->id];

            if ($language != '*') {
                $attributes[] = 'language';
                $values[] = array($needles['language'], '*');
            }

            $items = $menus->getItems($attributes, $values);

            foreach ($items as $item) {
                if (isset($item->query) && isset($item->query['view'])) {
                    $view = $item->query['view'];

                    if (!isset(self::$lookup[$language][$view])) {
                        self::$lookup[$language][$view] = array();
                    }

                    if (isset($item->query['id'])) {
                        /**
                         * Here it will become a bit tricky
                         * language != * can override existing entries
                         * language == * cannot override existing entries
                         */
                        if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*') {
                            self::$lookup[$language][$view][$item->query['id']] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles) {
            foreach ($needles as $view=>$ids) {
                if (isset(self::$lookup[$language][$view])) {
                    foreach ($ids as $id) {
                        if (isset(self::$lookup[$language][$view][$id])) {
                            return self::$lookup[$language][$view][$id];
                        }
                    }
                }
            }
        }

        // Check if the active menuitem matches the requested language
        $active = $menus->getActive();

        if ($active &&
            $active->component == 'com_jcar' &&
                ($language == '*' ||
                in_array($active->language, array('*', $language)) ||
                !JLanguageMultilang::isEnabled())) {
            return $active->id;
        }

        // If not found, return language specific home link
        $default = $menus->getDefault($language);

        return !empty($default->id) ? $default->id : null;
    }
}
