<?php
/**
 * @package     JCar.Plugin
 *
 * @copyright   Copyright (C) 2015 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Retrieves information from a REST API-enabled DSpace archive.
 */
class PlgJCarDSpace extends JPlugin
{
    public function __construct(&$subject, $config)
    {
        $this->autoloadLanguage = true;
        parent::__construct($subject, $config);

        JLog::addLogger(array());
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
        $parts = explode(":", $id, 2);

        if (count($parts) == 2) {
            $id = JArrayHelper::getValue($parts, 1);
        }

        return $this->getItem($id);
    }

    /**
     * A DSpace-specific trigger for returning communities, sub-communities
     * and collections as a tree structure.
     *
     * @return  A list of communities, sub-communities and collections as a
     * tree structure.
     */
    public function onJCarCommunitiesRetrieve()
    {
        $communities = array();

        $endpoint = '/communities.json?collections=true';
        $url = new JUri($this->params->get('rest_url').$endpoint);

        JLog::add($url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ($response->code === 200) {
            $data = json_decode($response->body);
            $communities = $data->communities;

            for ($i = 0; $i < count($communities); $i++) {
                $communities[$i] = $this->parseCommunity($communities[$i]);
            }
        } else {
            JLog::add($response->code, JLog::ERROR, 'jcardspace');
        }

        return $communities;
    }

    /**
     * A DSpace-specific trigger for returning a community and its
     * sub-communities and collections as a tree structure.
     *
     * @return  A community and its sub-communities and collections as a tree
     * structure.
     */
    public function onJCarCommunityRetrieve($id)
    {
        $community = null;

        $endpoint = '/communities/'.(int)$id.'.json?collections=true';
        $url = $this->params->get('rest_url').$endpoint;

        JLog::add($url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get($url);

        if ($response->code === 200) {
            $community = json_decode($response->body);
            $community = $this->parseCommunity($community);
        } else {
            JLog::add($response->code, JLog::ERROR, 'jcardspace');
        }

        return $community;
    }

    /**
     * Gets a list of DSpace collections as generic JCar categories.
     *
     * @return  A list of DSpace collections as generic JCar categories.
     */
    public function onJCarCategoriesRetrieve()
    {
        $categories = array();

        $endpoint = '/collections.json';
        $url = new JUri($this->params->get('rest_url').$endpoint);

        JLog::add((string)$url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ($response->code === 200) {
            $data = json_decode($response->body);
            $collections = $data->collections;

            foreach ($collections as $collection) {
                $categories[] = $this->parseCollection($collection);
            }
        } else {
            JLog::add($response->code, JLog::ERROR, 'jcardspace');
        }

        return $categories;
    }

    public function onJCarCategoryRetrieve($id)
    {
        $category = null;

        $endpoint = '/collections/'.$id.'.json';
        $url = new JUri($this->params->get('rest_url').$endpoint);

        JLog::add((string)$url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ($response->code === 200) {
            $data = json_decode($response->body);

            $category = $this->parseCollection($data);
        } else {
            JLog::add($response->code, JLog::ERROR, 'jcardspace');
        }

        return $category;
    }

    /**
     * Parses a DSpace community, adding additional content to the community
     * object.
     *
     * @param   stdClass  $community  The community to parse.
     *
     * @return  stdClass  A DSpace community with additional content.
     */
    private function parseCommunity($community)
    {
        $community->description = $community->shortDescription;
        $community->introduction = $community->introductoryText;
        $community->copyright = $community->copyrightText;

        for ($i = 0; $i < count($community->subCommunities); $i++) {
            $subCommunity = JArrayHelper($community->subCommunities, $i);
            $subCommunity = $this->parseCommunity($subCommunity);

            $community->subCommunities[$i] = subCommunity;
        }

        for ($i = 0; $i < count($community->collections); $i++) {
            $id = JArrayHelper::getValue($community->collections, $i);
            $collection = $this->parseCollection($id);

            $collection->description = $collection->shortDescription;
            $collection->introduction = $collection->introductoryText;
            $collection->copyright = $collection->copyrightText;

            $community->collections[$i] = $collection;
        }

        return $community;
    }

    /**
     * Parses a DSpace collection, adding additional content to the collection
     * object.
     *
     * @param   stdClass  $collection  The collection to parse.
     *
     * @return  stdClass  A DSpace collection with additional content.
     */
    private function parseCollection($collection)
    {
        $endpoint = '/collections/'.$collection->id.'/items/count.json';
        $url = new JUri($this->params->get('rest_url').$endpoint);

        JLog::add((string)$url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ($response->code === 200) {
            $data = json_decode($response->body);
            $collection->count = (int)$data;
        } else {
            JLog::add($response->code, JLog::ERROR, 'jcardspace');
        }

        $collection->description = $collection->shortDescription;
        $collection->introduction = $collection->introductoryText;
        $collection->copyright = $collection->copyrightText;

        return $collection;
    }

    /**
     * Gets an item from the REST API-enabled DSpace archive.
     *
     * @param  int    $id  The id of an item to retrieve from the DSpace archive.
     *
     * @param  mixed  An item from the REST API-enabled DSpace archive, or null if nothing could be found.
     */
    private function getItem($id)
    {
        $url = $this->params->get('rest_url').'/items/'.$id.'.json';

        JLog::add($url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get($url);

        if ($response->code === 200) {
            $data = json_decode($response->body);

            $array = array();
            foreach ($data->metadata as $metadata) {
                $key = $metadata->schema.'.'.$metadata->element;

                if (isset($metadata->qualifier)) {
                    $key .= '.'.$metadata->qualifier;
                }

                if (!JArrayHelper::getValue($array, $key)) {
                    $array[$key] = array();
                }

                $array[$key][] = $metadata->value;
            }

            $data->metadata = $array;

            $data->bundles = $this->getBundles($id);

            return $data;
        } else {
            JLog::add($response->body, JLog::ERROR, 'jcardspace');
            throw new Exception(JText::_('PLG_JCAR_DSPACE_ERROR_'.$response->code), $response->code);
        }
    }

    /**
     * Gets bundle information for the specified item from the REST API-enabled DSpace archive.
     *
     * @param  int    $item  The item id of the bundles to retrieve from the DSpace archive.
     *
     * @param  array  Bundle information for the specified item from the REST API-enabled DSpace archive.
     */
    private function getBundles($item)
    {
        $url = $this->params->get('rest_url').'/items/'.$item.'/bundles.json';
        JLog::add($url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();
        $response = $http->get($url);

        if ($response->code === 200) {
            $data = json_decode($response->body);

            return $data;
        } else {
            JLog::add($response->body, \JLog::ERROR, 'jcardspace');
            throw new Exception(JText::_('PLG_JCAR_DSPACE_ERROR_'.$response->code), $response->code);
        }
    }
}