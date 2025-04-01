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
    'Magento_Ui/js/form/element/ui-select',
    'Crealoz_EasyAudit/js/model/pr-storage'
], function (Select, prStorage) {
    'use strict';

    return Select.extend({
        defaults: {
            initialized: false,
            listens: {
                'value': 'updatePrStorage'
            }
        },

        initialize: function () {
            this._super();
            this.value(prStorage.get('patch_type'));
            this.initialized = true;
            return this;
        },
        updatePrStorage: function (value) {
            if (this.initialized) {
                prStorage.add(this.index, value);
            }
        },
    });
});
