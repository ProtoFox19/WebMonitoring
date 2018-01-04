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
    private $prereserveLinks = [];

    public $testvariable = [];
    public $testvariablezwei = [];
    public $j = 0;
    public $k = 0;

    public function __construct()
    {
        $this->baseUrl = '';
        $this->links = [];
        $this->depth = 0;
    }

    public function crawl($url, $maxDepth = 10)
    {
        $this->baseUrl = $url;
        $this->depth = $maxDepth;

        $this->spider($this->baseUrl, $maxDepth);

        return $this;
    }

    public function links()
    {
        return $this->links;
    }

    public function setLinksBack(){
        $this->links = array();
    }

    private function spider($url, $maxDepth)
    {

        try {

            $baseUrl = str_replace(['http://', 'https://', '/'], '', $this->baseUrl); //OWN Begin: the following if´s consider all types of url´s
            if((strpos($url,"/") === 0 || strpos($url,"./") === 0 || strpos($url,"../") === 0)&& strpos($url, $baseUrl) === false){
                $url = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . $url : $this->baseUrl . $url;
                //$url = $baseUrl . $url;
            }
            if((strpos($url,"http://") !== 0 && strpos($url,"https://") !== 0 && strpos($url,"www") !== 0 && strpos($url,"/") !== 0 && strpos($url,"./") !== 0 && strpos($url,"../") !== 0) && strpos($url, $baseUrl) === false){
                $url = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . "/" . $url : $this->baseUrl . "/" . $url;
                //$url = $baseUrl . '/' . $url;
            }//OWN End
            //TODO rausnehmen
            $this->testvariablezwei[$this->k++] = $url . "  " . $maxDepth;
            //$this->testvariable[$this->j++] = $baseUrl;
            //$this->testvariable[$this->j++] = $url ."  ". microtime(true);

            $this->links[$url] = [
                'status_code' => 0,
                'url' => $url,
                'visited' => false,
                'is_external' => false,
            ];

            // Create a client and send out a request to a url
            $client = new Client();
            $crawler = $client->request('GET', $url);

            // get the content of the request result
            $html = $crawler->getBody()->getContents();

            //TODO wieder rausnehmen
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
                if (strpos($contentType[0], 'text/html') !== false) {

                    // collect the links within the page
                    $pageLinks = [];
                    if (@$this->links[$url]['is_external'] == false) {
                        $pageLinks = $this->extractLinks($html, $url);
                    }

                    //TODO rausnehmen
                    //$this->testvariable[$this->j++] = $pageLinks;
                    //$this->testvariablezwei[$this->k++] = $maxDepth;

                    // spawn spiders for the child links, marking the depth as decreasing, or send out the soldiers
                    $this->spawn($pageLinks, $maxDepth - 1);
                }
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
        //TODO wieder rausnehmen
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
                    //TODO wieder rausnehmen
                    //$this->testvariable[$this->j++] = $this->links[$url]['url'];
                    //$this->testvariablezwei[$this->k++] = $maxDepth;

                    $this->spider($this->links[$url]['url'], $maxDepth);
                }
            }
        }
    }



    private function checkIfExternal($url)
    {
        //TODO wieder rausnehmen
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

            $fullUrl ='';   //OWN Begin: because the $nodeUrl could have just a part url, the full url must be build
            $baseUrl = str_replace(['http://', 'https://', '/'], '', $this->baseUrl); //selbst hinzugefuegt
            if((strpos($nodeUrl,"/") === 0 || strpos($nodeUrl,"./") === 0 || strpos($nodeUrl,"../") === 0)&& strpos($nodeUrl, $baseUrl) === false){
                $fullUrl = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . $nodeUrl : $this->baseUrl . $nodeUrl;
            }
            if((strpos($nodeUrl,"http://") !== 0 && strpos($nodeUrl,"https://") !== 0 && strpos($nodeUrl,"www") !== 0 && strpos($nodeUrl,"/") !== 0 && strpos($nodeUrl,"./") !== 0 && strpos($nodeUrl,"../") !== 0) && strpos($nodeUrl, $baseUrl) === false){
                $fullUrl = substr($this->baseUrl, -1) == '/'? substr($this->baseUrl, 0, -1) . "/" . $nodeUrl : $this->baseUrl . "/" . $nodeUrl;
            }
            if($fullUrl ==='') $fullUrl = $nodeUrl; //OWN End
            //TODO wieder rausnehmen
            //$this->testvariable[$this->j++] = $fullUrl ."  ". microtime(true);
            /*if (isset($this->links[$fullUrl])) {
                $this->testvariable[$this->j++] = $this->links[$fullUrl];
            }*/

            // If we don't have it lets collect it
            if (! isset($this->links[$fullUrl]) && !isset($this->prereserveLinks[$nodeUrl])) {  //OWN: there must be a temporal reserving variable for the NodeURL, because of Asynchronous searching of the spiders
                $this->prereserveLinks[$nodeUrl] = $nodeUrl;    //OWN: set the temporal reserving
                // set the basics
                $currentLinks[$nodeUrl]['is_external'] = false;
                $currentLinks[$nodeUrl]['url'] = $nodeUrl;
                $currentLinks[$nodeUrl]['visited'] = false;

                //TODO wieder rausnehmen
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