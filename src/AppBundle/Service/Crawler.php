<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 23.12.2017
 * Time: 18:07
 *
 * Code Base: https://github.com/YABhq/Crawler-Tutorial/blob/master/src/crawler.php
 * modified by Christoph Suhr ("OWN" in Comments)
 */

namespace AppBundle\Service;

use GuzzleHttp\Client;
use http\Env\Url;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;


class Crawler
{
    protected $url;
    protected $links;
    protected $maxDepth;

    private $prereserveLinks = [];  //Own
    private $excludedLinks =[];     //Own
    private $webService;            //Own
    private $functionality;         //Own

    public $testvariable = [];      //Own Variables for testing
    public $testvariablezwei = [];
    public $j = 0;
    public $k = 0;

    public function __construct(WebService $webService)
    {
        $this->baseUrl = '';
        $this->links = [];
        $this->depth = 0;

        $this->webService = $webService;    //Own
    }

    public function crawl($url, $maxDepth = 10, $functionality = 'sitemap')
    {
        $this->baseUrl = $url;
        $this->depth = $maxDepth;
        $this->functionality = $functionality;

        /*$this->links = array();               see "setLinksBack"
        $this->prereserveLinks = array();
        $this->excludedLinks = array();*/

        if($this->functionality === 'sitemap'){     //Own: standard is sitemap, but for future functions the crawler is needed for other things with other needs
            $web = $this->webService->getWebByNameOrDomain($url);
            $this->excludedLinks = preg_split('/\r\n|\r|\n/', $web->getSitemapSettings()->getNotIncludedPath());
        }

        $this->spider($this->baseUrl, $maxDepth);

        return $this;
    }

    public function links()
    {
        return $this->links;
    }

    public function setLinksBack(){         //Own Begin: Because how Services works, only one instance is generated.
        $this->links = array();             //When this instance is used to crawl more than one website, the links of the previous websites would also be in the links array's, because the constructor isn't called twice
        $this->prereserveLinks = array();   //therefor this function is build
        $this->excludedLinks = array();     //an other Method would be to set the array's in the function "crawl" back. because "crawl" initiates the whole process, reason why i´ve done it separate: i want to reserve the option, that i can crawl two sites and bundle the links (example: sitename.de and shop.sitename.de)
    }                                       //Own End

    private function spider($url, $maxDepth)
    {

        try {
            //$this->testvariable[$this->j++] = memory_get_usage();
            /*$memory = $this->j++ . "\t" . memory_get_usage()/1024/1024 . ' mib ' . "\t\t\t" . $url . "\n";
            print_r($memory);*/
            $baseUrl = str_replace(['http://', 'https://', '/'], '', $this->baseUrl); //Own Begin: the following if´s consider all types of url´s
            if(strpos($url,"/") === 0 && strpos($url, $baseUrl) === false){
                $url = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . $url : $this->baseUrl . $url;
                //$url = $baseUrl . $url;
            }
            if(strpos($url,"./") === 0 && strpos($url, $baseUrl) === false){
                $url = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . substr($url, 1) : $this->baseUrl . substr($url,1);
            }
            if(strpos($url,"../") === 0 && strpos($url, $baseUrl) === false){
                $url = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . substr($url, 2) : $this->baseUrl . substr($url,2);
            }
            if((strpos($url,"http://") !== 0 && strpos($url,"https://") !== 0 && strpos($url,"www") !== 0 && strpos($url,"/") !== 0 && strpos($url,"./") !== 0 && strpos($url,"../") !== 0) && strpos($url, $baseUrl) === false){
                $url = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . "/" . $url : $this->baseUrl . "/" . $url;
                //$url = $baseUrl . '/' . $url;
            }//Own End
            //TODO testing
            //$this->testvariablezwei[$this->k++] = $url . "  " . $maxDepth;
            //$this->testvariable[$this->j++] = $baseUrl;
            //$this->testvariable[$this->j++] = $url ."  ". microtime(true);
            $this->links[$url] = [
                'status_code' => 0,
                'url' => $url,
                'visited' => false,
                'is_external' => false,
                'lastMod' => '',            //Own
            ];

            $fileextension = "/\.(zip|exe|pdf|png|jpg|jpeg|mpg|doc|xls|ppt|pps|ppsx|psd|rar|txt|mp3|mov|mp4|bmp|gif|ico)/i";    //Own
            if(!preg_match($fileextension, $url)) {
                // Create a client and send out a request to a url
                $client = new Client();
                $crawler = $client->request('GET', $url,['allow_redirects' => false]);      //Own: don't allow redirects

                // get the content of the request result
                $html = $crawler->getBody()->getContents();

                //TODO testing
                //$this->testvariable[$this->j++] = $url;

                // lets also get the status code
                $statusCode = $crawler->getStatusCode();

                // mark current url as visited
                $this->links[$url]['visited'] = true;
                // Set the status code
                $this->links[$url]['status_code'] = $statusCode;
                if ($statusCode == 200) {

                    // Make sure the page is html
                    $contentType = $crawler->getHeader('Content-Type');
                    $rawLastMod = $crawler->getHeader('Last-Modified'); //Own begin
                    if(!is_null($rawLastMod) && !empty($rawLastMod)){
                        $lastMod = date("Y-m-d",strtotime($rawLastMod[0]));
                        $this->links[$url]['lastMod'] = $lastMod;
                        //print_r($this->links[$url]['lastMod'] . "\n");
                    }   //Own end
                    if (strpos($contentType[0], 'text/html') !== false) {

                        // collect the links within the page
                        $pageLinks = [];
                        if (@$this->links[$url]['is_external'] == false) {
                            $pageLinks = $this->extractLinks($html, $url);
                        }

                        //TODO testing
                        //$this->testvariable[$this->j++] = $pageLinks;
                        //$this->testvariablezwei[$this->k++] = $maxDepth;

                        // spawn spiders for the child links, marking the depth as decreasing, or send out the soldiers
                        $this->spawn($pageLinks, $maxDepth - 1);
                    }
                }
            } else{
                $validfileextension  = "/\.(zip|pdf)/i";        //Own: allow zip and pdf in the lists of Links, but don't "GET" the content
                if (preg_match($validfileextension, $url)) $this->links[$url]['visited'] = true;
            }
        } catch(\GuzzleHttp\Exception\RequestException $ex)  {
            // do nothing or something
        } catch (Exception $ex) {
            // call it a 404?
            $this->links[$url]['status_code'] = '404';
        }
    }

    private function spawn($links, $maxDepth)
    {
        //TODO testing
        //$this->testvariable[$this->j++] = $links;

        // if we hit the max - then its the end of the rope
        if ($maxDepth == 0) {
            return;
        }

        foreach ($links as $url => $info) {
            // only pay attention to those we do not know
            if (! isset($this->links[$url])) {
                $this->links[$url] = $info;

                // we really only care about links which belong to this domain
                if (! empty($url) && ! $this->links[$url]['visited'] && ! $this->links[$url]['is_external']) {
                    // restart the process by sending out more soldiers!
                    //TODO testing
                    //$this->testvariable[$this->j++] = $this->links[$url]['url'];
                    //$this->testvariablezwei[$this->k++] = $maxDepth;

                    $this->spider($this->links[$url]['url'], $maxDepth);
                }
            }
        }
    }



    private function checkIfExternal($url)
    {
        //TODO testing
        //$this->testvariable[$this->j++] = $url;

        $baseUrl = str_replace(['http://', 'https://'], '', $this->baseUrl);
        // if the url fits then keep going!

        /*if(strpos($url,"/") === 0 || (strpos($url,"/") === 0 && strpos($url, $baseUrl) === true)){         //selbst hinzugefuegt
            return false;
        }*/
        if(strpos($url,"/") === 0 || strpos($url,"./") === 0 || strpos($url,"../") === 0){         //OWN
            return false;
        }
        if (preg_match("@http(s)?\://$baseUrl@", $url)) {       //https or http + baseUrl
            return false;
        }
        if(strpos($url,"http://") !== 0 && strpos($url,"https://") !== 0 && strpos($url,"www") !== 0 && strpos($url,"/") !== 0 && strpos($url,"./") !== 0 && strpos($url,"../") !== 0){     //OWN
            return false;
        }

        return true;
    }

    private function extractLinks($html, $url)
    {
        $dom = new DomCrawler($html);
        $currentLinks = [];

        // get the links
        $dom->filter('a')->each(function(DomCrawler $node, $i) use (&$currentLinks) {
            // get the href
            $nodeUrl = $node->attr('href');
            $fullUrl ='';   //Own Begin: because the $nodeUrl could have just a part url, the full url must be build
            $baseUrl = str_replace(['http://', 'https://', '/'], '', $this->baseUrl);
            if(strpos($nodeUrl,"/") === 0 && strpos($nodeUrl, $baseUrl) === false){
                $fullUrl = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . $nodeUrl : $this->baseUrl . $nodeUrl;
            }
            if(strpos($nodeUrl,"./") === 0 && strpos($nodeUrl, $baseUrl) === false){
                $fullUrl = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . substr($nodeUrl,1) : $this->baseUrl . substr($nodeUrl,1);
            }
            if(strpos($nodeUrl,"../") === 0 && strpos($nodeUrl, $baseUrl) === false){
                $fullUrl = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . substr($nodeUrl,2) : $this->baseUrl . substr($nodeUrl,2);
            }
            if((strpos($nodeUrl,"http://") !== 0 && strpos($nodeUrl,"https://") !== 0 && strpos($nodeUrl,"www") !== 0 && strpos($nodeUrl,"/") !== 0 && strpos($nodeUrl,"./") !== 0 && strpos($nodeUrl,"../") !== 0) && strpos($nodeUrl, $baseUrl) === false){
                $fullUrl = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . "/" . $nodeUrl : $this->baseUrl . "/" . $nodeUrl;
            }
            if($fullUrl ==='') $fullUrl = $nodeUrl; //Own End
            //TODO testing
            //$this->testvariable[$this->j++] = $fullUrl ."  ". microtime(true);
            /*if (isset($this->links[$fullUrl])) {
                $this->testvariable[$this->j++] = $this->links[$fullUrl];
            }*/

            $exclude = false;                                       //Own Begin
            if($this->functionality === 'sitemap'){                 //if the crawler is crawling for "CreateSitemapCommand", note excluded Links
                foreach ($this->excludedLinks as $excludedLink){
                    if(!empty($excludedLink)){
                        if (substr($excludedLink, -1) == '*') {
                            $excludedLink = substr($excludedLink, 0, -1);
                            $search = '/'. preg_quote($excludedLink,'/') .'.+/';      //escaped all possible regular expressions in the search Url
                            if(preg_match($search, $nodeUrl)){
                                $exclude = true;
                            }
                        } else{
                            if (substr($excludedLink, -1) == '/') {
                                $excludedLink = substr($excludedLink, 0, -1);
                            }
                            if(strpos($nodeUrl,$excludedLink) !== false){           //if the excludedLink is in the nodeUrl, set exclude true, so the site with the excluded link don't get crawled. NOTE: excludedLink must be a Path link (example: /somthing/test/), otherwise the "if command" can be faulty, when the nodeUrl have the baseUrl included
                                $exclude = true;
                            }
                        }
                    }
                }
            }
            $regex = "/^[a-z]+:(?!\/\/)/i";     // Mailto: or javascript: and so on
            if(preg_match($regex, $nodeUrl)) $exclude = true;
            $regexanchor = "/#(?!\!)/";         //For anchors, #! <= are okay
            if(preg_match($regexanchor, $nodeUrl)) $exclude = true; //Own End

            // If we don't have it lets collect it
            if (! isset($this->links[$fullUrl]) && !isset($this->prereserveLinks[$nodeUrl]) && !$exclude) {  //Own: there must be a temporal reserving variable for the NodeURL,
                $this->prereserveLinks[$nodeUrl] = $nodeUrl;    //Own: set the temporal reserving
                // set the basics
                $currentLinks[$nodeUrl]['is_external'] = false;
                $currentLinks[$nodeUrl]['url'] = $nodeUrl;
                $currentLinks[$nodeUrl]['visited'] = false;

                //TODO testing
                //$this->testvariablezwei[$this->k++] = $nodeUrl ."  ". microtime(true);


                // check if the link is external
                if ($this->checkIfExternal($currentLinks[$nodeUrl]['url'])) {
                    $currentLinks[$nodeUrl]['is_external'] = true;
                }
            }
        });

        // if page is linked to itself, ex. homepage
        if (isset($currentLinks[$url])) {
            // let's avoid endless cycles
            $currentLinks[$url]['visited'] = true;
        }


        // Send back the reports
        return $currentLinks;
    }
}