define("jquery underscore ko mage/storage Eloom_Core/js/model/url-builder Eloom_Payment/js/cash".split(" "),function(d,e,f,g,h,k){return k.extend({defaults:{template:"Eloom_PayUCo/payment/pse-form",code:"eloom_payments_payu_pse"},banks:f.observableArray(),initialize:function(){this._super();return this},isActive:function(){return!0},isInSandboxMode:function(){return window.checkoutConfig.payment.eloom_payments_payu.isInSandboxMode},isTransactionInTestMode:function(){return window.checkoutConfig.payment.eloom_payments_payu.isTransactionInTestMode},
getLogoUrl:function(){return window.checkoutConfig.payment.eloom_payments_payu.url.logo},isShowPseBanks:function(){var a=this;g.post(h.createUrl("/eloom/payuco/psebanks",{}),null,!1).done(function(b){b=JSON.parse(b);a.banks.removeAll();b.data&&e.each(b.data,function(c,l){c&&a.banks.push({v:c.value,t:c.label})})});return!0},userTypes:function(){return window.checkoutConfig.payment[this.getCode()].userType},getData:function(){var a=d("#".concat(this.getCode()).concat("_bank")).val(),b=d("#".concat(this.getCode()).concat("_user_type")).val();
a={method:this.item.method,additional_data:{pse_bank:a?a:"null",pse_user_type:b?b:"null"}};a.additional_data=e.extend(a.additional_data,this.additionalData);return a}})});
