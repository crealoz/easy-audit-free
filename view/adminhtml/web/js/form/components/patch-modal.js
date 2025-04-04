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
    'jquery',
    'ko',
    'Magento_Ui/js/modal/modal-component',
    'Crealoz_EasyAudit/js/model/pr-storage',
    'Crealoz_EasyAudit/js/model/response-storage',
    'Magento_Ui/js/modal/alert'
], function (_, $, ko, Modal, PrStorage, responseStorage, modalAlert) {
    'use strict';

    return Modal.extend({

        initialize: function () {
            this._super();
            PrStorage.add('relative_path', this.source.data.general.relative_path);
            PrStorage.add('patch_type', this.source.data.general.patch_type);
            this.responseData = PrStorage.response;
            return this;
        },

        saveData: function () {
            this.applyData();

            let self = this;

            let ajaxUrl = this.source.submit_url,
                data = {
                    'form_key': window.FORM_KEY,
                    'data': {
                        'relative_path': PrStorage.get('relative_path'),
                        'patch_type': PrStorage.get('patch_type'),
                        'result_id': this.source.data.general.result_id,
                    }
                };

            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: data,
                showLoader: true
            }).done(function (xhr) {
                if (xhr.status === 'success') {
                    responseStorage.response( { text: xhr.message, type: "info", visible: true });
                } else if (xhr.error === true) {
                    responseStorage.response( { text: xhr.message, type: "error", visible: true });
                }
                // close modal after 5 seconds
                setTimeout(function () {
                    self.closeModal();
                }, 500);
            });
        },
    });
});
