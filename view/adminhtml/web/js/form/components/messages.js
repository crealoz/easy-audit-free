/*!
 * EasyAudit Premium - Magento 2 Audit Extension
 *
 * Copyright (c) 2025 Crealoz. All rights reserved.
 * Licensed under the EasyAudit Premium EULA.
 *
 * This software is provided under a paid license and may not be redistributed,
 * modified, or reverse-engineered without explicit permission.
 * See EULA for details: https://crealoz.fr/easyaudit-eula
 */
define([
    'Magento_Ui/js/form/components/html',
    'ko',
    'Crealoz_EasyAudit/js/model/response-storage'
], function (Component, ko, responseStorage) {
    'use strict';

    return Component.extend({

        responseData: ko.observableArray([]),

        initialize: function () {
            this._super();
            this.responseData = responseStorage.response;
            return this;
        },

        initObservable: function () {
            this._super();

            responseStorage.response.subscribe(function (newValue) {
                this.visible(newValue.visible);
                this.content(newValue.text);
            }, this);


            return this;
        }
    });
});
