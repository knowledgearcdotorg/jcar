<?php
/**
 * @package     JCar.Plugin
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use \Joomla\Utilities\ArrayHelper;

\JLoader::import('joomla.filesystem.file');

JLoader::register('JCarHelper', JPATH_ROOT.'/administrator/components/com_jcar/helpers/jcar.php');

/**
 * Retrieves information from a REST API-enabled DSpace archive.
 */
class PlgJCarOai extends JPlugin
{
    public function __construct(&$subject, $config)
    {
        $this->autoloadLanguage = true;
        parent::__construct($subject, $config);

        JLog::addLogger(array());
    }

    /**
     * Gets a list of OAI sets as generic JCar categories.
     *
     * @return  A list of OAI sets as generic JCar categories.
     */
    public function onJCarCategoriesRetrieve()
    {
        $categories = array();

        $url = new JUri($this->params->get('oai_url'));

        $query = array("verb"=>"ListSets");

        $url->setQuery($query);

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ($response->code === 200) {
            $data = new SimpleXmlElement($response->body);

            $sets = iterator_to_array($data->ListSets->set, 0);

            foreach ($sets as $set) {
                $category = new stdClass();

                $category->id = $this->_name.":".(string)$set->setSpec;
                $category->name = (string)$set->setName;

                $categories[] = $category;
            }
        } else {
            JLog::add($response->code, JLog::DEBUG, 'jcaroai');

            throw new Exception('An error occurred.', $response->code);
        }

        return $categories;
    }

    /**
     * Gets an OAI set's information, the items within the category and paging
     * information to allow for browsing across the entire recordset.
     *
     * @return  An OAI set's information, the items within the
     * category and paging informatoin to allow for browsing across the entire
     * recordset.
     */
    public function onJCarCategoryRetrieve($id)
    {
        $category = new stdClass();

        $set = JCarHelper::parseId($id);

        $app = JFactory::getApplication();

        $this->set('set', $set);
        $this->set('token', $app->input->getString('token', null));

        $category->id = $id;
        $category->name = "";
        $category->description = "";
        $category->items = $this->getItems();
        $category->pagination = $this->getPagination();

        return $category;
    }

    /**
     * Gets an item from a REST API-enabled DSpace archive.
     *
     * @param   int       $id  The id of an item to retrieve from the DSpace
     * archive.
     * @param   stdClass  $params  Additional configuration details.
     *
     * @return  mixed  An item from the REST API-enabled DSpace archive, or
     * null if nothing could be found.
     */
    public function onJCarItemRetrieve($id, $params = null)
    {
        if ($params) {
            if ($url = $params->url) {
                $this->params->set('oai_url', $url);
            }
        }

        return $this->getItem($id);
    }

    private function getItems()
    {
        $items = array();

        $xml = $this->getRecordList();

        $records = array();

        if (count($xml->ListRecords->record)) {
            $records = iterator_to_array($xml->ListRecords->record, 0);
        }

        foreach ($records as $record) {
            $item = new stdClass();

            $item->id = (string)$record->header->identifier;

            $metadata = iterator_to_array($record->metadata, 0);

            foreach ($metadata as $field) {
                $namespaces = $field->getDocNamespaces(true);

                foreach ($namespaces as $prefix=>$namespace) {
                    if ($prefix) {
                        $field->registerXPathNamespace($prefix, $namespace);

                        $tags = $field->xpath($prefix.':*/*');

                        foreach ($tags as $tag) {
                            if (JString::trim((string)$tag)) {
                                if ((string)$tag->getName() == "title") {
                                    $item->name = (string)$tag;
                                }
                            }
                        }
                    }
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    private function getPagination()
    {
        $app = JFactory::getApplication();

        $count = $this->getItemsCount();
        $start = $app->input->getInt('limitstart');

        if ($this->getCursor() == 0) {
            $app->setUserState('jcar.oai.limit', count($this->getItems()));
        }

        $limit = $app->getUserState('jcar.oai.limit', $count);

        $pagination = new JPagination($count, $start, $limit);

        $token = $this->getResumptionToken();

        if ($token) {
            $pagination->setAdditionalUrlParam("token", $token);
        }

        return $pagination;
    }

    private function getItemsCount()
    {
        $count = 0;

        $xml = $this->getRecordList();

        if ($xml) {
            if (isset($xml->ListRecords->resumptionToken)) {
                $resumptionToken = $xml->ListRecords->resumptionToken;

                $count = (int)$resumptionToken['completeListSize'];
            } else {
                $count = (int)count($this->getItems());
            }
        }

        return $count;
    }

    private function getCursor()
    {
        $cursor = 0;

        $xml = $this->getRecordList();

        if ($xml) {
            if (isset($xml->ListRecords->resumptionToken)) {
                $resumptionToken = $xml->ListRecords->resumptionToken;

                $cursor = (int)$resumptionToken['cursor'];
            }
        }

        return $cursor;
    }

    private function getResumptionToken()
    {
        $xml = $this->getRecordList();

        if ($xml) {
            if (isset($xml->ListRecords->resumptionToken)) {
                return (string)$xml->ListRecords->resumptionToken;
            }
        }

        return null;
    }

    private function getRecordList()
    {
        if (!$xml = $this->get('oai', array())) {
            $set = $this->get('set');

            $url = new JUri($this->params->get('oai_url'));

            $url->setVar('verb', 'ListRecords');

            $app = JFactory::getApplication();

            $token = $this->get('token');

            if ($token) {
                $url->setVar('resumptionToken', $token);
            } else {
                $url->setVar('metadataPrefix', 'oai_dc');
                $url->setVar('set', $set);
            }

            JLog::add((string)$url, JLog::DEBUG, 'jcaroai');

            $http = JHttpFactory::getHttp();

            $response = $http->get((string)$url);

            if ($response->code === 200) {
                $xml = new SimpleXMLElement($response->body);

                $this->set('oai', $xml);
            } else {
                JLog::add(print_r($response, true), JLog::DEBUG, 'jcaroai');

                throw new Exception(
                    JText::_('PLG_JCAR_OAI_ERROR_'.$response->code),
                    $response->code);
            }
        }

        return $xml;
    }

    /**
     * Gets an item from the REST API-enabled DSpace archive.
     *
     * @param  int    $id  The id of an item to retrieve from the DSpace
     * archive.
     * @param  mixed  An item from the REST API-enabled DSpace archive, or
     * null if nothing could be found.
     */
    private function getItem($id)
    {
        $url = new JUri($this->params->get('oai_url'));

        $query = array(
            "verb"=>"GetRecord",
            "metadataPrefix"=>"oai_dc",
            "identifier"=>$id);

        $url->setQuery($query);

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        $data = null;

        if ($response->code === 200) {
            $xml = new SimpleXMLElement($response->body);

            $metadata = array();

            $namespaces = $xml->getDocNamespaces(true);

            foreach ($namespaces as $prefix=>$namespace) {
                if ($prefix) {
                    $xml->registerXPathNamespace($prefix, $namespace);

                    $tags = $xml->xpath('//'.$prefix.':*');

                    foreach ($tags as $tag) {
                        if (JString::trim((string)$tag)) {
                            $key = $prefix.'.'.(string)$tag->getName();

                            $values = ArrayHelper::getValue($metadata, $key);

                            if (!is_array($values)) {
                                $values = array();
                            }

                            $values[] = (string)$tag;

                            $metadata[$key] = $values;
                        }
                    }
                }
            }

            $data = new stdClass();

            $data->metadata = $metadata;

            $data->bundles = array();

            if ($this->params->get('ore_enabled', 1)) {
                $data->bundles = $this->getBundles($id);
            }

            return $data;
        } else {
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcaroai');

            throw new Exception("An error has occurred.", $response->code);
        }
    }

    /**
     * Gets bundle information for the specified item from the REST
     * API-enabled DSpace archive.
     *
     * @param  int    $item  The item id of the bundles to retrieve from the
     * DSpace archive.
     *
     * @param  array  Bundle information for the specified item from the REST
     * API-enabled DSpace archive.
     */
    private function getBundles($item)
    {
        $excludeBundles = array_filter(explode(",", $this->params->get('exclude_bundles')));

        $url = new JUri($this->params->get('oai_url'));

        $query = array(
            "verb"=>"GetRecord",
            "metadataPrefix"=>"ore",
            "identifier"=>$item);
        $url->setQuery($query);

        $http = JHttpFactory::getHttp();
        $response = $http->get((string)$url);

        $data = array();

        if ($response->code === 200) {
            $xml = new SimpleXMLElement($response->body);

            foreach ($xml->getDocNamespaces(true) as $prefix=>$namespace) {
                if (strlen($prefix) == 0) {
                    $prefix = "default";
                }

                $xml->registerXPathNamespace($prefix, $namespace);
            }

            $description = '//oreatom:triples/rdf:Description';

            $links = $xml->xpath($description);

            foreach ($links as $link) {
                $href = $link->attributes("rdf", true)->about;

                if (count($link->xpath("dcterms:description"))) {
                    $derivative = $link->xpath("dcterms:description");
                    $derivative = (string)array_shift($derivative);

                    if (count($excludeBundles) == 0 ||
                        array_search($derivative, $excludeBundles) !== false) {
                        $derivative = JString::strtolower($derivative);

                        $bitstream = new stdClass();
                        $bitstream->url = urldecode($href);

                        $aggregates = '//atom:link[@href="'.$href.'"]';

                        $node = $xml->xpath($aggregates);

                        $attrs = array();

                        if (count($node)) {
                            $attrs = iterator_to_array($node[0]->attributes());
                        }

                        $name = ArrayHelper::getValue($attrs, 'title', JFile::getName($href), 'string');
                        $type = ArrayHelper::getValue($attrs, 'type', null, 'string');
                        $size = ArrayHelper::getValue($attrs, 'length', null, 'int');

                        $bitstream->name = $name;
                        $bitstream->mimeType = $type;
                        $bitstream->size = $size;
                        $bitstream->formatDescription = $type;

                        if (!array_key_exists($derivative, $data)) {
                            $data[$derivative] = new stdClass();
                            $data[$derivative]->name = $derivative;
                            $data[$derivative]->bitstreams = array();
                        }

                        $data[$derivative]->bitstreams[] = $bitstream;
                    }
                }
            }

            return $data;
        } else {
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcaroai');

            throw new Exception("An error has occurred.", $response->code);
        }
    }
}
