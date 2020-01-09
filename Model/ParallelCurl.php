<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_CacheWarmer extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_CacheWarmer
 */

namespace Magenest\CacheWarmer\Model;

use Magenest\CacheWarmer\Api\ParallelCurlInterface;
use Magenest\CacheWarmer\Helper\ParallelCurl as ParallelCurlHelper;

class ParallelCurl implements ParallelCurlInterface
{

    protected $parallelCurlHelper;
    protected $results;
    protected $config;
    const OK = 200;
    const MAX_REQUESTS = 16;

    public function __construct(
        ParallelCurlHelper $parallelCurlHelper
    )
    {
        $this->results = array();
        $this->parallelCurlHelper = $parallelCurlHelper;
    }

    public function sendSingleCurl($url, $in_options = null, $post_fields = null, $callback = null, $user_data = null)
    {
        $this->setOptions($in_options);
        $this->startRequest($url, $callback, $user_data, $post_fields);
        $this->finishAllRequests();
        return $this->results;
    }

    public function sendMultipleCurl($urls = array(), $max_requests = null, $in_options = null, $post_fields = null, $callback = null, $user_data = null)
    {
        $this->results = array();
        $this->setMaxRequests($max_requests);
        $this->setOptions($in_options);
        for ($i = 0; $i < count($urls); $i++) {
            if (isset($urls[$i]['url'])) {
                $this->startRequest($urls[$i]['url'], $callback, $user_data, $post_fields);
            }
        }
        $this->finishAllRequests();
        return $this->results;
    }

    public function setMaxRequests($max_requests = null)
    {
        if (!empty($max_requests)) {
            $max_requests = self::MAX_REQUESTS;
        }
        $this->parallelCurlHelper->max_requests = $max_requests;
    }

    public function setOptions($in_options = null)
    {
        if (!isset($in_options)) {
            $in_options = array(
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_USERAGENT, 'Magenest Cache Warmer'
            );
        }
        $this->parallelCurlHelper->options = $in_options;
    }

    public function startRequest($url, $callback = null, $user_data = array(), $post_fields = null)
    {
        if (!isset($callback)) {
            $callback = [$this, 'onRequestDone'];
        }
        $this->parallelCurlHelper->startRequest($url, $callback, $user_data = array(), $post_fields = null);
    }

    function onRequestDone($content, $url, $ch, $user_data)
    {
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->results[] = $httpcode;
        return $this->results;
    }

    public function finishAllRequests()
    {
        $this->parallelCurlHelper->finishAllRequests();
    }
}