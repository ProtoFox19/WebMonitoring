<?php
/**
 * Created by PhpStorm.
 * User: csuhr
 * Date: 05.12.17
 * Time: 16:40
 */

namespace AppBundle\Service;


use AppBundle\Entity\SitemapSettings;
use AppBundle\Entity\Web;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class WebService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * WebService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return Web[]
     */
    public function getWebs(){
        $webs = $this->em->getRepository('AppBundle:Web')
            ->findAllWebsInOrder();
        return $webs;
    }

    /**
     * @param $name_domain
     * @return Web
     */
    public function getWebByNameOrDomain($name_domain){
        $web = $this->em->getRepository('AppBundle:Web')->findOneByName($name_domain);
        if($web === NULL){
            $web = $this->em->getRepository('AppBundle:Web')->findOneByDomain($name_domain);
        }
        return $web;
    }

    /**
     * @return Web[]
     */
    public function getAllActiveWebs(){
        $webs = $this->em->getRepository('AppBundle:Web')
            ->findAllActiveWebs();
        return $webs;
    }
}