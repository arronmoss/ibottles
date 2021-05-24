## moved all this to Migrate, maybe people might want to install modules without DM
cd ~/htdocs;
mv pub/errors/local.xml.sample pub/errors/local.xml

#paypal test
bin/magento config:set paypal/wpp/api_username arron.moss+sandboxbusiness_api1.zero1.co.uk
bin/magento config:set paypal/wpp/api_password JDDJ8PDDH6XATT2U
bin/magento config:set paypal/wpp/api_signature AN7RYZQ.31bZqTgC8c9AIBbiy4tkA3-oEyYqgcMiCqDxuTpGl8M-7Q0Q
bin/magento config:set paypal/general/business_account arron.moss@zero1.co.uk
bin/magento config:set paypal/wpp/sandbox_flag 1 

bin/magento config:set --lock-config payment/stripe_payments_basic/stripe_mode test

# Elastic
#m2db_host=$(php -r '$env = include "./app/etc/env.php"; echo $env["db"]["connection"]["default"]["host"].PHP_EOL;');
#INSTANCEID="$(echo $m2db_host | cut -d'-' -f1)"
#echo ${INSTANCEID}
#bin/magento config:set smile_elasticsuite_core_base_settings/es_client/servers ${INSTANCEID}-elastic-search
#bin/magento config:set catalog/search/engine elasticsuite

# UPDATE admin_user SET interface_locale='en_GB' WHERE 1;
