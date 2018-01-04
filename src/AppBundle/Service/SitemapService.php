<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 04.01.2018
 * Time: 06:35
 */

namespace AppBundle\Service;


use AppBundle\Entity\SitemapSettings;
use AppBundle\Entity\Web;

class SitemapService
{

    /**
     * @param Web $web
     * @return SitemapSettings
     */
    public function createDefaultSitemapSetting(Web $web){
        $newSitemapSetting = new SitemapSettings();
        $newSitemapSetting->setNotIncludedPath('');
        $newSitemapSetting->setNotablyPath('');
        $newSitemapSetting->setActive(true);
        $newSitemapSetting->setWeb($web);
        return $newSitemapSetting;
    }
}