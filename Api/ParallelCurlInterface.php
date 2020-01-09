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

namespace Magenest\CacheWarmer\Api;

interface ParallelCurlInterface
{
    /**
     * @param $url
     * @param null $in_options
     * @param null $post_fields
     * @return mixed
     */
    public function sendSingleCurl($url, $in_options = null, $post_fields = null, $callback = null, $user_data = null);

    /**
     * @param null $in_options
     * @return mixed
     */
    public function sendMultipleCurl($urls = array(), $max_requests = null, $in_options = null, $post_fields = null, $callback = null, $user_data = null);

}
