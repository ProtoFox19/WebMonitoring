<?php
/**
 * Created by PhpStorm.
 * User: Christoph Suhr
 * Date: 04.01.2018
 * Time: 09:48
 *
 * @author Christoph Suhr
 */

namespace AppBundle\Service;

class GenerateSitemap
{
    private $links;
    private $name;
    private $webService;
    private $notablyPath = [];

    public function __construct(WebService $webService)
    {
        $this->links = $links = [];
        $this->name = $name ='';
        $this->webService = $webService;
    }

    public function generateSitemap($links, $domain){
        sort($links);
        $this->links = $links;
        $this->name = str_replace(['http://', 'https://', 'www.'], '', $domain);
        $this->getNotablyPath($domain);

        if(count($this->links) > 50000){
            $this->divideSitemap();
        } else{
           $xml = $this->getSitemap($this->links);
           $this->safeSitemap($xml);
        }
    }

    private function getNotablyPath($domain){
        $web = $this->webService->getWebByNameOrDomain($domain);
        $this->notablyPath = preg_split('/\r\n|\r|\n/', $web->getSitemapSettings()->getNotablyPath());
        $i=0;
        foreach ($this->notablyPath as $path){
            $this->notablyPath[$i] = explode('|',$path);
            $i++;
        }
    }

    private function safeSitemap($xml, $number = NULL){
        $counter ='';
        if(isset($number)) $counter = $number;
        $file = fopen('./sitemaps/'.$this->name.'/sitemap'.$counter.'.xml','w+');
        fwrite($file,$xml);
        fclose($file);
    }

    private function divideSitemap(){
        $numberOfSitemaps = ((int) count($this->links)/50000);      //in reality is $numberOfSitemaps the number of Sitemaps needed - 1
        $tempLinks = $this->links;

        for($i=0; $i < $numberOfSitemaps; $i++){                    //handle the first sitemaps, with safe 50000 entry's
            $partLinks = array_splice($tempLinks, 0, 50000);        //$partLinks get the first 50000 entry's of the array $tempLinks and the first 50000 entry's were deleted in $templinks
            $xml = $this->getSitemap($partLinks);
            $this->safeSitemap($xml, $i+1);
        }
        if(count($tempLinks) > 0){
            $xml = $this->getSitemap($tempLinks);      //$tempLinks are now the remaining Links
            $this->safeSitemap($xml, $numberOfSitemaps+1);
        }

        $xml = $this->createMasterSitemap($numberOfSitemaps);
        $this->safeSitemap($xml);
    }

    private function createMasterSitemap($numberOfSitemaps){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml.= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        for($i=0; $i < $numberOfSitemaps; $i++){
            $xml.= "\t" .'<sitemap>'."\n";
            $xml.= "\t" ."\t" .'<loc>'. 'sitemap'. ($i+1) .'.xml'. '</loc>'."\n";
            $xml.= "\t" .'</sitemap>'."\n";
        }
        $xml.= '</sitemapindex>';

        return $xml;
    }

    private function getSitemap($links){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml.= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($links as $link) {
            $changefreq = 'monthly';
            $priority = '0.5';
            foreach ($this->notablyPath as $notably){
                if(!empty($notably)){
                    $search = '/'. preg_quote($notably[0],'/') .'.+/';      //escaped all possible regular expressions in the search Url
                    if(!preg_match($search, $link['url']) && strpos($link['url'],$notably[0]) !== false){    //strpos: if path was found, preg_match: but not the children pages
                        $changefreq = $notably[1];
                        $priority = $notably[2];
                    }
                }
            }
            $xml.= "\t" .'<url>'."\n";
            $xml.= "\t" ."\t" .'<loc>'. $link['url'] .'</loc>'. "\n";
            $xml.= "\t" ."\t" .'<changefreq>'. $changefreq .'</changefreq>'. "\n";
            $xml.= "\t" ."\t" .'<priority>'. $priority .'</priority>'. "\n";
            $xml.= "\t" .'</url>'."\n";
        }

        $xml.= '</urlset>';

        return $xml;
    }
}