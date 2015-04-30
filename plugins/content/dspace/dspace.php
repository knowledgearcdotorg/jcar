<?php
/**
 * @package     Content.Plugin
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::import('joomla.filesystem.file');

/**
 * Embeds a record from a REST API-enabled DSpace archive into a Joomla! article.
 */
class PlgContentDSpace extends JPlugin
{
    private $item;

    public function __construct(&$subject, $config)
    {
        $this->autoloadLanguage = true;
        parent::__construct($subject, $config);

        JLog::addLogger(array());

        $item = null;
    }

    /**
     * Adds DSpace metadata to the content of an article.
     *
     * @param   string   $context  The context of the content being passed to the plugin.
     * @param   object   &$row     The article object. Note $article->text is also available
     * @param   mixed    &$params  The article params
     * @param   integer  $page     The 'page' number
     *
     * @return  mixed    void or true
     */
    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        // Expression to search for
        $regex = '/{jcar[^}]*}/';

        preg_match_all($regex, $row->text, $matches);

        foreach ($matches[0] as $match) {

            if(isset($match) && preg_match( '/ ([^}]+)}/', $match, $results)) {

                // separate parameters using delimiter - "|"
                $values = explode('|', JArrayHelper::getValue($results, 1));

                foreach($values as $param) {
                    list($key, $value) = explode('=', $param);

                    if(isset($key) && isset($value)) {
                        $this->params->set('jcar.'.$key, $value);
                    }
                }
            }

            $id = $this->params->get('jcar.id');

            $this->addMetaData($id);

            ob_start();

            $displayData = $this->getItem($id);
            include JPluginHelper::getLayoutPath('content', 'dspace');

            $html = ob_get_contents();
            ob_end_clean();

            $row->text = str_replace($match, $html, $row->text);
        }

        return true;
    }

    private function addMetaData($id)
    {
        $app = JFactory::getApplication('site');
        $doc = JFactory::getDocument();

        $item = $this->getItem($id);

        foreach ($item->metadata as $key=>$value) {
            $doc->setMetaData(str_replace(":", ".", $key), implode(', ', $value));
        }
    }

    private function getItem($id)
    {
        if (!$this->item) {
            JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_jcar/models');
            $model = JModelLegacy::getInstance('Item', 'JCarModel');

            try {
                $this->item = $model->getItem($id);
            } catch (Exception $e) {
                // fail silently.
            }
        }

        return $this->item;
    }
}