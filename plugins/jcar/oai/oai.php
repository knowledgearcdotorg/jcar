<?php
/**
 * @package     JCar.Plugin
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Retrieves information from a REST API-enabled DSpace archive.
 */
class PlgJCarOai extends JPlugin
{
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

                $category->id = (string)$set->setSpec;
                $category->name = (string)$set->setName;

                $categories[] = $category;
            }
        } else {
            JLog::add($response->code, JLog::ERROR, 'jcardspace');
        }

        return $categories;
    }

    /**
     * Gets an item from a REST API-enabled DSpace archive.
     *
     * @param  int    $id  The id of an item to retrieve from the DSpace
     * archive.
     * @param  mixed  An item from the REST API-enabled DSpace archive, or
     * null if nothing could be found.
     */
    public function onJCarItemRetrieve($id)
    {
        return $this->getItem($id);
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
                            $key = $prefix.':'.(string)$tag->getName();

                            $values = JArrayHelper::getValue($metadata, $key);

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

            $default = 'http://www.openarchives.org/OAI/2.0/';
            $atom = 'http://www.w3.org/2005/Atom';
            $oreatom = 'http://www.openarchives.org/ore/atom/';
            $rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
            $dcterms = 'http://purl.org/dc/terms/';

            $xml->registerXPathNamespace('default', $default);
            $xml->registerXPathNamespace('atom', $atom);
            $xml->registerXPathNamespace('oreatom', $oreatom);
            $xml->registerXPathNamespace('rdf', $rdf);
            $xml->registerXPathNamespace('dcterms', $dcterms);

            $aggregates = '//atom:link'.
                '[@rel="http://www.openarchives.org/ore/terms/aggregates"]';

            $links = $xml->xpath($aggregates);

            foreach ($links as $link) {
                $attrs = array();

                foreach($link->attributes() as $key=>$value){
                    $attrs[$key] = trim($value);
                }

                $href = JArrayHelper::getValue($attrs, 'href', null, 'string');
                $name = JArrayHelper::getValue($attrs, 'title', null, 'string');
                $type = JArrayHelper::getValue($attrs, 'type', null, 'string');
                $size = JArrayHelper::getValue($attrs, 'length', null, 'int');

                $bitstream = new stdClass();
                $bitstream->url = urldecode($href);
                $bitstream->name = $name;
                $bitstream->mimeType = $type;
                $bitstream->size = $size;
                $bitstream->formatDescription = $type;

                $description =
                    '//oreatom:triples/rdf:Description[@rdf:about="'.
                    $bitstream->url.'"]/dcterms:description';

                $derivatives = $xml->xpath($description);
                $derivative = strtolower(
                    JArrayHelper::getValue(
                        $derivatives,
                        0,
                        'original',
                        'string'));

                if (!array_key_exists($derivative, $data)) {
                    $data[$derivative] = new stdClass();
                    $data[$derivative]->name = $derivative;
                    $data[$derivative]->bitstreams = array();
                }

                $data[$derivative]->bitstreams[] = $bitstream;
            }

            return $data;
        } else {
            throw new Exception("An error has occurred.", $response->code);
        }
    }
}