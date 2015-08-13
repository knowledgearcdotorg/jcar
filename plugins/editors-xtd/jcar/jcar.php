<?php
/**
 * @package     JCar.Plugin
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Provides a button for inserting and configuring a JCAR record in an article.
 */
class PlgButtonJCar extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * Display the button
     *
     * @param   string  $name  The name of the button to add
     *
     * @return array A two element array of (imageName, textToInsert)
     */
    public function onDisplay($name)
    {
        JHtml::_('behavior.modal');

        $link = new JUri('index.php');

        $array = array(
            "option"=>"com_jcar",
            "view"=>"item",
            "layout"=>"configuration",
            "tmpl"=>"component",
            "e_name"=>$name);

        $link->setQuery($array);

        $button = new JObject;
        $button->modal = true;
        $button->class = 'btn';
        $button->link  = (string)$link;
        $button->text  = JText::_('PLG_EDITORS-XTD_JCAR_BUTTON_JCAR');
        $button->name  = 'link';
        $button->options = "{handler: 'iframe', size: {x: 500, y: 300}}";

        return $button;
    }
}