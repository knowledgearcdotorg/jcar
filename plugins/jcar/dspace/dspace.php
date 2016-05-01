<?php
/**
 * @package     JCar.Plugin
 *
 * @copyright   Copyright (C) 2015-2016 KnowledgeArc Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use \Joomla\Utilities\ArrayHelper;

JLoader::register('JCarHelper', JPATH_ROOT.'/administrator/components/com_jcar/helpers/jcar.php');

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
     * Gets a list of DSpace collections as generic JCar categories.
     *
     * @return  A list of DSpace collections as generic JCar categories.
     */
    public function onJCarCategoriesRetrieve()
    {
        // A special case if the dspace layout is selected
        // (I.e. we're displaying communities).
        $layout = JFactory::getApplication()->input->getString('layout');
        $parts = explode(":", $layout);

        if (count($parts) == 2) {
            if (ArrayHelper::getValue($parts, 1) == 'dspace') {
                return $this->getCommunities();
            }
        }

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
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception('An error occurred.', $response->code);
        }

        return $categories;
    }

    /**
     * Gets a DSpace collection's information, the items within the
     * category and paging information to allow for browsing across the entire
     * recordset.
     *
     * @return  A DSpace collection's information, the items within the
     * category and paging information to allow for browsing across the entire
     * recordset.
     */
    public function onJCarCategoryRetrieve($id)
    {
        $category = null;

        $id = JCarHelper::parseId($id);

        $parts = explode(":", $id);

        if (count($parts) == 2 &&
            ArrayHelper::getValue($parts, 0) == 'community') {
            return $this->getCommunity($id);
        }

        $endpoint = '/collections/'.$id.'.json';
        $url = new JUri($this->params->get('rest_url').$endpoint);

        JLog::add((string)$url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ($response->code === 200) {
            $data = json_decode($response->body);

            $category = $this->parseCollection($data);
            $category->items = $this->getItems($id);
            $category->pagination = $this->getPagination();
        } else {
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception('An error occurred.', $response->code);
        }

        return $category;
    }

    /**
     * Gets an item from a REST API-enabled DSpace archive.
     *
     * @param   int       $id      The id of an item to retrieve from the
     * DSpace archive.
     * @param   stdClass  $params  Additional configuration details.
     *
     * @return  mixed     An item from the REST API-enabled DSpace archive, or
     * null if nothing could be found.
     */
    public function onJCarItemRetrieve($id, $params = null)
    {
        if ($params) {
            if ($url = $params->url) {
                $this->params->set('rest_url', $url);
            }

            if ($key = $params->key) {
                $this->params->set('rest_key', $key);
            }

            if ($secret = $params->secret) {
                $this->params->set('rest_secret', $secret);
            }
        }

        $id = JCarHelper::parseId($id);

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

                if (!ArrayHelper::getValue($array, $key)) {
                    $array[$key] = array();
                }

                $metadata->value = JCarHelper::cloak($metadata->value);

                $array[$key][] = $metadata->value;
            }

            $data->metadata = $array;

            $data->bundles = $this->getBundles($id);

            return $data;
        } else {
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception(
                JText::_('PLG_JCAR_DSPACE_ERROR_'.$response->code),
                $response->code);
        }
    }

    /**
     * Gets an item from a REST API-enabled DSpace archive.
     *
     * @param   int       $id      The id of an item to retrieve from the
     * DSpace archive.
     * @param   stdClass  $params  Additional configuration details.
     *
     * @return  mixed     An item from the REST API-enabled DSpace archive, or
     * null if nothing could be found.
     */
    public function onJCarAssetRetrieve($id)
    {
        $id = JCarHelper::parseId($id);

        $url = $this->params->get('rest_url').'/bitstreams/'.$id.'.json';

        JLog::add($url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get($url);

        if ($response->code === 200) {
            $data = json_decode($response->body);

            $data->id = $this->_name.":".$data->id;

            $downloadUrl =
                $this->params->get('rest_url').'/bitstreams/'.$id.'/download';

            $downloadUrl = new JUri($downloadUrl);

            $data->url = $downloadUrl;

            return $data;
        } else {
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception(
                JText::_('PLG_JCAR_DSPACE_ERROR_'.$response->code),
                $response->code);
        }
    }

    /**
     * Gets all communities, sub-communities and collections as a tree
     * structure.
     *
     * @return  array      A list of communities, sub-communities and
     * collections as a tree structure.
     *
     * @throws  Exception  Thrown if the API endpoint does not return the html
     * code 200.
     */
    private function getCommunities()
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
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception('An error occurred.', $response->code);
        }

        return $communities;
    }

    /**
     * Gets community and its sub-communities and collections as a tree
     * structure.
     *
     * @return  stdClass   A community and its sub-communities and collections
     * as a tree structure.
     *
     * @throws  Exception  Thrown if the API endpoint does not return the html
     * code 200.
     */
    public function getCommunity($id)
    {
        $community = null;

        $id = JCarHelper::parseId($id);

        $endpoint = '/communities/'.(int)$id.'.json?collections=true';
        $url = $this->params->get('rest_url').$endpoint;

        JLog::add($url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get($url);

        if ($response->code === 200) {
            $community = json_decode($response->body);
            $community = $this->parseCommunity($community);
        } else {
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception('An error occurred.', $response->code);
        }

        return $community;
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
        $community->id = $this->_name.":community:".$community->id;
        $community->description = $community->shortDescription;
        $community->introduction = $community->introductoryText;
        $community->copyright = $community->copyrightText;

        for ($i = 0; $i < count($community->subCommunities); $i++) {
            $subCommunity = ArrayHelper($community->subCommunities, $i);
            $subCommunity = $this->parseCommunity($subCommunity);

            $community->subCommunities[$i] = subCommunity;
        }

        for ($i = 0; $i < count($community->collections); $i++) {
            $id = ArrayHelper::getValue($community->collections, $i);
            $collection = $this->parseCollection($id);

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
     *
     * @throws  Exception  Thrown if the API endpoint does not return the html
     * code 200.
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
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception('An error occurred.', $response->code);
        }

        $collection->id = $this->_name.":".$collection->id;
        $collection->description = $collection->shortDescription;
        $collection->introduction = $collection->introductoryText;
        $collection->copyright = $collection->copyrightText;

        return $collection;
    }

    /**
     * Gets a list of items based on a collection from the REST API-enabled
     * DSpace archive.
     *
     * @param   int    $cid  The id of a collection.
     *
     * @return  mixed  An item from the REST API-enabled DSpace archive, or
     * null if nothing could be found.
     *
     * @throws  Exception  Thrown if the API endpoint does not return the html
     * code 200.
     */
    private function getItems($cid)
    {
        $pagination = $this->getPagination();

        $items = array();

        $url = $this->params->get('rest_url');
        $url .= '/collections/'.$cid.'/items.json';

        $url = new JUri($url);

        $url->setQuery(
            array(
                "start"=>$pagination->get('limitstart'),
                "limit"=>$pagination->get('limit')));

        JLog::add((string)$url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ($response->code === 200) {
            $items = json_decode($response->body);

            for ($i = 0; $i < count($items); $i++) {
                $items[$i]->id = $this->_name.":".$items[$i]->id;
            }
        } else {
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception(
                JText::_('PLG_JCAR_DSPACE_ERROR_'.$response->code),
                $response->code);
        }

        return $items;
    }

    private function getItemsCount($cid)
    {
        $url = $this->params->get('rest_url');
        $url .= '/collections/'.$cid.'/items/count.json';

        $url = new JUri($url);

        JLog::add((string)$url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();

        $response = $http->get((string)$url);

        if ($response->code === 200) {
            return (int)json_decode($response->body);
        } else {
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception(
                JText::_('PLG_JCAR_DSPACE_ERROR_'.$response->code),
                $response->code);
        }

        return 0;
    }

    private function getPagination()
    {
        $app = JFactory::getApplication();

        // @TODO these should be passed as method params.
        $total = $this->getItemsCount($app->input->getInt('id'));
        $start = $app->input->getInt('limitstart', 0);
        $limit = $app->input->getInt('limit', 20);

        $pagination = new JPagination($total, $start, $limit);

        return $pagination;
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
        $url = $this->params->get('rest_url');
        $url .= '/items/'.$item.'/bundles.json';

        $url = new JUri($url);

        if ($excludeBundles = $this->params->get('exclude_bundles', null)) {
            $url->setVar('type', $excludeBundles);
        }

        JLog::add($url, JLog::DEBUG, 'jcardspace');

        $http = JHttpFactory::getHttp();
        $response = $http->get($url);

        if ($response->code === 200) {
            $bundles = json_decode($response->body);

            for ($i = 0; $i < count($bundles); $i++) {
                $bundle = ArrayHelper::getValue($bundles, $i);
                $bitstreams = $bundle->bitstreams;

                for ($j = 0; $j < count($bitstreams); $j++) {
                    $bitstream = ArrayHelper::getValue($bitstreams, $j);

                    if ((bool)$this->params->get('stream')) {
                        $url = new JUri('index.php');

                        $url->setQuery(
                            array(
                                'option'=>'com_jcar',
                                'view'=>'asset',
                                'format'=>'raw',
                                'id'=>$this->_name.':'.$bitstream->id,
                                'name'=>$bitstream->name,
                                'Itemid'=>JFactory::getApplication()->input->getInt('Itemid')));
                    } else {
                        $url = new JUri(
                            $this->params->get('rest_url').
                            '/bitstreams/'.
                            $bitstream->id.
                            '/download');
                    }

                    $bundles[$i]->bitstreams[$j]->url = (string)$url;
                }
            }

            return $bundles;
        } else {
            JLog::add(print_r($response, true), JLog::DEBUG, 'jcardspace');

            throw new Exception(
                JText::_('PLG_JCAR_DSPACE_ERROR_'.$response->code),
                $response->code);
        }
    }
}
