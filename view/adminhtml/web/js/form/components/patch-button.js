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
    'Magento_Ui/js/form/components/button',
    'mage/translate',
    'underscore'
], function (Button, $t, _) {
    'use strict';

    return Button.extend({
        defaults: {
            resultId: null,
            diff: false,
            title: $t('Generate Patch'),
        },

        initialize: function () {
            this._super();
            let title = this.title();
            if (this.source.data.general.diff != null && this.source.data.general.diff != '') {
                title = $t('Update Patch');
            }
            title += ' ' + $t('(a credit per file will be consumed)');
            this.title(title);
        },

        /**
         * Apply action on target component,
         * but previously create this component from template if it does not exist
         *
         * @param {Object} action - action configuration
         */
        applyAction: function (action) {
            if (action.params && action.params[0]) {
                action.params[0]['result_id'] = this.resultId;
            } else {
                action.params = [{
                    'result_id': this.resultId
                }];
            }

            this._super();
        },
    });
});
