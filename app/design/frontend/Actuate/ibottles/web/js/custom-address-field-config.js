define(['jquery', 'underscore'], function($, _) {
    "use strict";
 
    var vatNumber = ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE'];
    var eoriNumber = ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE'];

    return {
        /* 
		Personal ID Number: CANARY ISLANDS NIF/NIE/DNI (ES?), SPAIN NIF/NIE/DNI, ITALY CF, PORTUGAL NIF/NIE/DNI, BRAZIL CPF, ARGENTINA, CHILE, COLOMBIA
		Personal Tax ID Number:BOLIVA,COSTA RICA,ECUADOR,PARAGUARY,PERU,URUGUARY,VENEZUELA
		Business EORI Number: Austria, Belgium, Bulgaria, Croatia, Cyprus, Czech Republic, Denmark, Estonia, Finland, France, Germany, Greece, Hungary, Ireland, Italy, Latvia, Lithuania, Luxembourg, Malta, Netherlands, Poland, Portugal, Romania, Slovakia, Slovenia, Spain and Sweden.
		*/
        config: {
            /* checkout */
            'div[name="shippingAddress.custom_attributes.vat_number"]': vatNumber,
            'div[name="shippingAddress.custom_attributes.business_eori_number"]': eoriNumber,
            
            /* customer address edit */
            'div[name="custom_attributes.vat_number"]': vatNumber,
            'div[name="custom_attributes.business_eori_number"]': eoriNumber,
        },

        updateFields: function(countryValue){
            var fieldConfig = this.config;
            console.log('update fields', countryValue);
            _.each(_.keys(fieldConfig), function(key){
                console.log('field: ', key, $(key).length);
                if($(key).length > 0){
                    if(_.indexOf(fieldConfig[key], countryValue) === -1){
                        // hide the field
                        $(key).removeClass('_required _error');
                        let input = $(key).find('input');
                        input.attr('aria-required','false').removeClass('required-entry')
                        // we only want to add a value if it's empty
                        if(input.val() == ''){
                            input.val('N/A');
                        }
                        input.change();
                        $(key).hide();
                    }else{
                        // show the field
                        // $(key).addClass('_required');
                        let input = $(key).find('input');
                        $(key).find('input')
                        
                        // input.attr('aria-required','true').addClass('required-entry')
                        // only want to empty if the value is 'N/A'
                        if(input.val() == 'N/A'){
                            input.val('');
                        }
                        input.change();
                        $(key).show();
                    }
                }
            });
        }
    };
});
