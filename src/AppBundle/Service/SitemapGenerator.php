<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 29.12.2017
 * Time: 18:24
 */

namespace AppBundle\Service;


class SitemapGenerator
{
    public function __construct()
    {
        $this->generateArchive();
    }

    public function generateArchive(){
        mkdir('./test1/test2', 0777, true);
    }
}

//if (php_sapi_name() === 'cli') {
//if(empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0 ){
 //   $obj = new SitemapGenerator();
//}
//}