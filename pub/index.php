<?php
/**
 * Public alias for the application entry point
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

if(php_sapi_name() != 'cli' && is_file(__DIR__.'/../var/.maintenance.flag')){
       $fileToUse = __DIR__.'/../var/maintenance/default_maintenance.html';
       if(isset($_SERVER['MAGE_RUN_CODE'])){
           if(is_file(__DIR__.'/../var/maintenance/'.$_SERVER['MAGE_RUN_CODE'].'.html')){
                   $fileToUse = __DIR__.'/../var/maintenance/'.$_SERVER['MAGE_RUN_CODE'].'.html';
           }
       }
       http_response_code(503);
    include ($fileToUse);
    exit();
}

use Magento\Framework\App\Bootstrap;

try {
    require __DIR__ . '/../app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}

$bootstrap = Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
$bootstrap->run($app);
