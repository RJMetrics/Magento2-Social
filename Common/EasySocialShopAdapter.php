<?php
/**
 * Copyright 2017 Shopial. All rights reserved.
 */

namespace Magento\Social\Common;

use Magento\Social\Api\SocialNetworkInterface;

use Magento\Social\Api\Data\SetupRequestInterface;
use Magento\Social\Api\Data\SyncRequestInterface;
use Magento\Social\Api\Data\ResultInterfaceFactory;
use Magento\Social\Api\Data\ResultInterface;

/**
 * Class EasySocialShopAdapter
 */
class EasySocialShopAdapter
{
    const METHOD_POST = 'post';

    const METHOD_GET = 'get';

    /**
     * @param string $action
     * @param array $data
     *
     * @return array
     */
    public function call($action, array $data)
    {
        try {
            list ($method, $url) = $this->matchCall($action);

            if ($method === self::METHOD_GET) {
                $url .= '?' . http_build_query($data);
            }

            $ch = curl_init($url);

            if ($method === self::METHOD_POST) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $result = trim(curl_exec($ch), " \t\n\"'");
            $result = json_decode($result, true);
            $result = $result ?: [];
        } catch (\Exception $exception) {
            $result = [
                'error' => true,
                'message' => $exception->getMessage()
            ];
        }

       return $result;
    }

    /**
     * @param string $action
     *
     * @return array
     */
    private function matchCall($action)
    {
        switch ($action) {
            case 'status':
                $url = 'https://fbapp.ezsocialshop.com/facebook/index.php/magento2/is_update';
                $result = [self::METHOD_GET, $url];
                break;
            case 'setup':
                $url = 'https://fbapp.ezsocialshop.com/facebook/index.php/magento2/save_facebook_token';
                $result = [self::METHOD_POST, $url];
                break;
            default:
                $url = 'https://fbapp.ezsocialshop.com/facebook/index.php/magento2/catalog_callback';
                $result = [self::METHOD_POST, $url];
                break;
        }
        return $result;
    }
}
