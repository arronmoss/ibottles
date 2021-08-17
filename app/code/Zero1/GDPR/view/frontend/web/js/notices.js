/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'jquery/ui',
    'mage/cookies'
], function ($) {
    'use strict';

    $.widget('mage.cookieNotices', {
        /** @inheritdoc */
        _create: function () {
            if ($.mage.cookies.get(this.options.cookieName)) {
                this.element.hide();
            } else {
                this.element.show();
            }

            if ($.mage.cookies.get(this.options.cookieDenyName)) {
                this.element.hide();
            }

            $(this.options.cookieAllowButtonSelector).on('click', $.proxy(function () {
                var cookieExpires = new Date(new Date().getTime() + this.options.cookieLifetime * 1000);

                $.mage.cookies.set(this.options.cookieName, JSON.stringify(this.options.cookieValue), {
                    expires: cookieExpires
                });

                if ($.mage.cookies.get(this.options.cookieName)) {
                    window.location.reload();
                } else {
                    window.location.href = this.options.noCookiesUrl;
                }
            },this));

            // DECLINE button, set user_denied_save_cookie cookie and don't show alert again (line 25)
            $(this.options.cookieDenyButtonSelector).on('click', $.proxy(function () {
                var cookieDenyExpires = new Date(new Date().getTime() + this.options.cookieDenyLifetime * 1000);
                $.mage.cookies.set(this.options.cookieDenyName, JSON.stringify(this.options.cookieDenyValue), {
                    expires: cookieDenyExpires
                });
                if ($.mage.cookies.get(this.options.cookieDenyName)) {
                    window.location.reload();
                } else {
                    window.location.href = this.options.noCookiesUrl;
                }
            },this));
        }
    });

    return $.mage.cookieNotices;
});