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
    'underscore',
    'Magento_Ui/js/form/element/textarea'
], function (_, Textarea) {
    'use strict';

    return Textarea.extend({
        initialize: function () {
            this._super();
            if (this.value() != '') {
                this.setVisible(true);
            }
            return this;
        },
    });
});
