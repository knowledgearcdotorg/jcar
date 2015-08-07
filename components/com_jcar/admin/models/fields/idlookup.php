<?php
/**
 * @package     JCar.Component
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  1.6
 */
class JCarFormFieldIdLookup extends JFormField
{
    protected $type = 'JCar.IdLookup';

    protected function getInput()
    {
        JHtml::_('jquery.framework');

        $typeahead = JUri::root().'/media/com_jcar/js/typeahead.js/typeahead.bundle.min.js';

        $url = new JUri('index.php');
        $url->setQuery(array(
            "option"=>"com_jcar",
            "view"=>"item",
            "format"=>"json",
            "query"=>"%QUERY"));

        JFactory::getDocument()->addScript($typeahead);
        JFactory::getDocument()->addScriptDeclaration =
<<<JS
(function($) {
    $(document).ready(function() {
        var ids = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            //prefetch: '../data/films/post_1960.json',
            remote: {
                url: '{(string)$url}',
                wildcard: '%QUERY'
            }
        });

        $('#idLookup').typeahead(null, {
            name: 'text',
            display: 'value',
            source: ids
        });
    })
})(jQuery);
JS;
        //$html = JLayoutHelper::render("jspace.form.fields.asset", $this);

        $html =
<<<HTML
<input type="text" id="idLookup" name="id"/>
HTML;
        return $html;
    }
}