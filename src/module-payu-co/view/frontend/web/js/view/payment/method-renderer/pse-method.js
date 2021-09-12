define([
		'jquery',
		'underscore',
		'ko',
		'mage/storage',
		'Eloom_Core/js/model/url-builder',
		'Eloom_Payment/js/cash'
	],
	function ($,
	          _,
	          ko,
	          storage,
	          urlBuilder,
	          CashPayment) {
		'use strict';

		return CashPayment.extend({
			defaults: {
				template: 'Eloom_PayUCo/payment/pse-form',
				code: 'eloom_payments_payu_pse',
			},
			banks: ko.observableArray(),

			initialize: function () {
				let self = this;
				this._super();

				return self;
			},

			isActive: function () {
				return true;
			},

			getLogoUrl: function () {
				return window.checkoutConfig.payment['eloom_payments_payu'].url.logo;
			},

			isShowPseBanks: function () {
				let self = this;

				storage.post(
					urlBuilder.createUrl('/eloom/payuco/psebanks', {}),
					null,
					false
				).done(function (response) {
					let json = JSON.parse(response);
					self.banks.removeAll();

					if (json.data) {
						_.each(json.data, function (bank, index) {
							if (bank) {
								self.banks.push({'v': bank.value, 't': bank.label});
							}
						});
					}
				});

				return true;
			},

			userTypes: function () {
				return window.checkoutConfig.payment[this.getCode()].userType;
			},

			getData: function () {
				let bank = $('#'.concat(this.getCode()).concat('_bank')).val();
				let userType = $('#'.concat(this.getCode()).concat('_user_type')).val();

				let data = {
					'method': this.item.method,
					'additional_data': {
						'pse_bank': (bank ? bank : 'null'),
						'pse_user_type': (userType ? userType : 'null')
					}
				};
				data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

				return data;
			},
		});
	}
);