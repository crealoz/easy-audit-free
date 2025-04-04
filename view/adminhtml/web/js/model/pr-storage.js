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
    'ko',
    'underscore'
], function (ko, _) {
    'use strict';
    return {
        storage: ko.observableArray([
            {key: 'relative_path', data: ''},
            {key: 'patch_type', data: ''},
        ]),
        add: function (key,data) {
            this.remove(key);
            this.storage.push({
                key: key,
                data: data
            });
        },
        remove: function (key) {
            this.storage(_.reject(this.storage(), function (item) {
                return item.key === key;
            }));
        },
        removeAll: function () {
            this.storage([]);
        },
        /**
         * Get data by key. Returns null if not found.
         * @param {string} key
         * @returns {Object}
         */
        get: function (key) {
            let item = _.find(this.storage(), function (item) {
                return item.key === key;
            });
            return item ? item.data : null;
        }
    };
});
