<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 11.12.2017
 * Time: 02:59
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Web;
use Doctrine\ORM\EntityRepository;

class WebRepository extends EntityRepository
{
    /**
     * @return Web[]
     */
    public function findAllWebsInOrder()
    {
        return $this->createQueryBuilder('webs')
            ->orderBy('webs.name', 'ASC')
            ->getQuery()
            ->execute();
    }

    /**
     * @return Web[]
     */
    public function findAllActiveWebs()
    {
        return $this->createQueryBuilder('webs')
            ->andWhere('webs.active = :isActive')
            ->setParameter('isActive', true)
            ->getQuery()
            ->execute();
    }
}
