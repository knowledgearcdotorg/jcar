<?php
defined('JPATH_BASE') or die;

JHtml::_('jquery.framework');

$typeahead = JUri::root().'/media/com_jcar/js/typeahead.js/typeahead.bundle.min.js';

$url = new JUri('index.php');
$url->setQuery(array(
    "option"=>"com_jcar",
    "view"=>"item",
    "format"=>"json",
    "query"=>"%QUERY"));

//JFactory::getDocument()->addScript($typeahead);
JFactory::getDocument()->addScriptDeclaration(
<<<JS
(function($) {
    $(document).ready(function() {
        /*var ids = new Bloodhound({
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
        });*/

        // not a clean event capture but just try to fire as much as possible.
        $("#idLookup").on("keyup change blur", function() {
            var identifier = $(this).val();
            var plugin = $("#idLookupPlugin").val();

            var idTag = "#{$displayData->id}";

            if (identifier.indexOf(plugin+":") == 0) {
                $(idTag).val(identifier);
            } else {
                $(idTag).val(plugin+":"+identifier);
            }
        });
    })
})(jQuery);
JS
);
?>

<?php
echo JHtml::_(
    'select.genericlist',
    $displayData->options,
    "idLookupPlugin",
    "",
    'value',
    'text',
    $displayData->plugin); ?>

<input
    type="text"
    id="idLookup"
    name="idLookup"
    placeholder="<?php echo JText::_($displayData->getAttribute('placeholder')); ?>"
    value="<?php echo $displayData->lookup; ?>"/>

<input
    type="hidden"
    id="<?php echo $displayData->id; ?>"
    name="<?php echo $displayData->name; ?>"
    value="<?php echo $displayData->value; ?>"/>
