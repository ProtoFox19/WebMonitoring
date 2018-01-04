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
    public function __construct(EntityManager $em)
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

}