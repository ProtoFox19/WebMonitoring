<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 23.12.2017
 * Time: 18:07
 *
 * Code Base: https://yabhq.com/build-web-crawler-10-minutes/
 */

namespace AppBundle\Service;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;


class Crawler
{
    protected $url;
    protected $links;
    protected $maxDepth;

    public $testvariable = [];
    public $j = 0;

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

    private function spider($url, $maxDepth)
    {

        try {

            $baseUrl = str_replace(['http://', 'https://'], '', $this->baseUrl); //selbst hinzugefuegt
            if(strpos($url,"/") === 0 && strpos($url, $baseUrl) === false){
                $url = $this->baseUrl . $url;
            }
            $this->testvariable[$this->j++] = $url;

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

                    // mark current url as visited
                    $this->links[$url]['visited'] = true;
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
                    $this->spider($this->links[$url]['url'], $maxDepth);
                }
            }
        }
    }



    private function checkIfExternal($url)
    {
        $baseUrl = str_replace(['http://', 'https://'], '', $this->baseUrl);
        // if the url fits then keep going!

        if(strpos($url,"/") === 0 || (strpos($url,"/") === 0 && strpos($url, $baseUrl) === true)){         //selbst hinzugefuegt
            return false;
        }
        if (preg_match("@http(s)?\://$baseUrl@", $url)) {
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

            // If we don't have it lets collect it
            if (! isset($this->links[$nodeUrl])) {
                // set the basics
                $currentLinks[$nodeUrl]['is_external'] = false;
                $currentLinks[$nodeUrl]['url'] = $nodeUrl;
                $currentLinks[$nodeUrl]['visited'] = false;

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