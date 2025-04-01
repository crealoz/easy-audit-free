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
    'Magento_Ui/js/grid/columns/select'
], function (ko, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Crealoz_EasyAudit/grid/cells/custom-html',
        },

        getHtml: function (row) {
            return row[this.index + '_html'];
        },
    });
});
