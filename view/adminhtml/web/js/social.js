/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global alert:true*/

define([
    'underscore',
    'jquery',
    'mage/translate'
], function (_, $, $t) {
    'use strict';

    return function (config) {
        var isIE = navigator.appVersion.match(/MSIE/) == "MSIE";
        var fbConfig = config.fbConfig;
        var localConfig = config.localConfig;

        var fbConfigState = {
            setting_id: fbConfig.diaSettingId,
            pixel_id: fbConfig.pixel.pixelId,
            catalog_id: fbConfig.catalogId,
            page_id: fbConfig.pageId,
            page_access_token: fbConfig.pageAccessToken,
            not_yet_installed: (
                !fbConfig.diaSettingId
                || !fbConfig.pixelId
                || !fbConfig.catalogId
                || !fbConfig.pageId
                || !fbConfig.pageAccessToken
            )
        };

        function updateState(data) {
            _.extend(fbConfigState, data);

            if (
                fbConfigState.not_yet_installed
                && fbConfigState.setting_id
                && fbConfigState.pixel_id
                && fbConfigState.catalog_id
                && fbConfigState.page_id
                && fbConfigState.page_access_token
            ) {
                window.refresh();
            }
        }

        function resetState() {
            fbConfigState = {};
        }

        function togglePopupOriginWeb(dia_origin) {
            var current_origin = fbConfig.popupOrigin;
            if (dia_origin.includes('web.') && !current_origin.includes('web.')) {
                fbConfig.popupOrigin = current_origin.replace('www.', 'web.');
            } else if (!dia_origin.includes('web.') && current_origin.includes('web.')) {
                fbConfig.popupOrigin = current_origin.replace('web.', 'www.');
            }
        }

        function parseURL(url) {
            var parser = document.createElement('a');
            parser.href = url;
            return parser;
        }

        function urlFromSameDomain(url1, url2) {
            var u1 = parseURL(url1);
            var u2 = parseURL(url2);
            var u1host = u1.host.replace('web.', 'www.');
            var u2host = u2.host.replace('web.', 'www.');
            return u1.protocol === u2.protocol && u1host === u2host;
        }

        function bindMessageEvents() {
            if (isIE && window.MessageChannel) {
                // do nothing, wait for our messaging utils ready
            } else {
                window.addEventListener('message', function (event) {
                    var origin = event.origin || event.originalEvent.origin;
                    if (urlFromSameDomain(origin, fbConfig.popupOrigin)) {
                        togglePopupOriginWeb(origin);

                        callToAction(event.data);
                    }
                }, false);
            }
        }
        bindMessageEvents();

        var fbPopupWindow;
        function callToPopup(type, params, prependType) {
            if (!fbPopupWindow) {
                console.log('Facebook Popup window has been closed.');
            } else {
                type = prependType ? (prependType + ' ' + type) : type;
                fbPopupWindow.postMessage({
                    type: type,
                    params: params
                }, fbConfig.popupOrigin);
            }
        }

        function callToAction(data) {
            if (typeof data !== 'object' || !data.type) {
                console.error('Facebook Ads Extension Error: get unsupport msg:', evdata);
            } else {
                switch (data.type) {
                    case 'get dia settings':
                        callToPopup('dia settings', {clientSetup: fbConfig});
                        break;
                    case 'gen feed':
                        // not implemented
                        break;

                    case 'set merchant settings':
                        if (!data.setting_id) {
                            console.error('Facebook Ads Extension Error: Missed Setting ID', data);
                            return;
                        }
                        saveSettings(
                            'set merchant settings',
                            {
                                setting_id: data.setting_id
                            },
                            function () {
                                updateState(data);
                            }
                        );
                        break;
                    case 'set pixel':
                        if (!data.pixel_id) {
                            console.error('Facebook Ads Extension Error: Missed Pixel ID', data);
                            return;
                        }
                        saveSettings(
                            'set pixel',
                            data,
                            function () {
                                updateState(data);
                            }
                        );
                        break;
                    case 'set catalog':
                        if (!data.catalog_id) {
                            console.error('Facebook Ads Extension Error: Missed Catalog ID', data);
                            return;
                        }
                        saveSettings(
                            'set catalog',
                            data,
                            function () {
                                updateState(data);
                            }
                        );
                        break;
                    case 'set page access token':
                        if (!data.page_access_token) {
                            console.error('Facebook Ads Extension Error: Missed Page Access Token', data);
                            return;
                        }
                        saveSettings(
                            'set page access token',
                            data,
                            function () {
                                updateState(data);
                            }
                        );
                        break;
                    case 'reset':
                        resetSettings(
                            'reset',
                            data,
                            function () {
                                resetState();
                            }
                        );
                        break
                }
            }
        }

        function saveSettings(type, params, callback) {
            $.ajax({
                url: localConfig.saveSettingsUrl,
                data: params,
                type: 'POST',
                success: function (response) {
                    if (response.success) {
                        if (callback) {
                            callback(response);
                        }
                        callToPopup(type, params, 'ack');
                    } else {
                        callToPopup(type, params, 'fail');
                    }
                },
                error: function () {
                    callToPopup(type, params, 'fail');
                }
            });
        }

        function resetSettings(type, params, callback) {
            $.ajax({
                url: localConfig.resetUrl,
                data: params,
                type: 'POST',
                success: function (response) {
                    if (response.success) {
                        if (callback) {
                            callback(response);
                        }
                        callToPopup(type, params, 'ack');
                    } else {
                        callToPopup(type, params, 'fail');
                    }
                },
                error: function () {
                    callToPopup(type, params, 'fail');
                }
            });
        }

        var popups = {};
        function openPopup(popupUrl, popupName) {
            var width = 1153;
            var height = 808;
            var topPos = screen.height / 2 - height / 2;
            var leftPos = screen.width / 2 - width / 2;

            if (popups[popupUrl]) {
                popups[popupUrl].close();
            }

            popups[popupUrl] = window.open(
                popupUrl,
                popupName,
                [
                    'toolbar=no',
                    'location=no',
                    'directories=no',
                    'status=no',
                    'menubar=no',
                    'scrollbars=no',
                    'resizable=no',
                    'copyhistory=no',
                    'width=' + width,
                    'height=' + height,
                    'top=' + topPos,
                    'left=' + leftPos
                ].join(',')
            );

            return popups[popupUrl];
        }

        function openSocialIntPopup(popupUrl) {
            openPopup(
                popupUrl,
                $t('Integration Setup Wizard')
            );
            ping(localConfig.integrationStatusUrl, function (result) {
                if (result.status > 0) {
                    window.refresh();
                    return true;
                } else {
                    return false;
                }
            });
        }

        function openSocialShopPopup(popupUrl) {
            openPopup(
                popupUrl,
                $t('Facebook Shop')
            );
        }

        function openSocialUpgradePopup(popupUrl) {
            openPopup(
                popupUrl,
                $t('Upgrade Easy Social Shop Account')
            );
            ping(localConfig.subscriptionStatusUrl, function (result) {
                if (result.status === 'upgraded') {
                    window.refresh();
                    return true;
                } else {
                    return false;
                }
            });
        }

        function openSocialAdPopup(popupUrl) {
            openPopup(
                popupUrl,
                $t('Create Ad in Facebook')
            );
        }

        function openSocialFbPopup() {
            var originUrl = window.location.protocol + '//' + window.location.host;
            var popupUrl = fbConfig.popupOrigin;

            fbPopupWindow = openPopup(
                popupUrl
                    + '?origin=' + encodeURIComponent(originUrl)
                    + (fbConfigState.setting_id ? '&merchant_settings_id=' + fbConfigState.setting_id : ''),
                $t('Facebook Dia Wizard')
            );
        }

        var pings = {};
        var counts = {};
        function ping(pingUrl, callback) {
            if (!pings[pingUrl]) {
                counts[pingUrl] = 0;
                pings[pingUrl] = setInterval(
                    function () {
                        $.ajax(pingUrl, {
                            data: {},
                            type: 'GET',
                            success: function (response) {
                                if (callback(response) === true || counts[pingUrl] > 100) {
                                    clearInterval(pings[pingUrl]);
                                    pings[pingUrl] = false;
                                    counts[pingUrl] = 0;
                                } else {
                                    ++counts[pingUrl];
                                }
                            },
                            error: function () {
                                ++counts[pingUrl];
                            }
                        });
                    },
                    5000
                );
            }
        }

        window.openSocialIntPopup = openSocialIntPopup;
        window.openSocialShopPopup = openSocialShopPopup;
        window.openSocialUpgradePopup = openSocialUpgradePopup;
        window.openSocialAdPopup = openSocialAdPopup;
        window.openSocialFbPopup = openSocialFbPopup;
    };
});
