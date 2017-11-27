<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 27.11.2017
 * Time: 18:37
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected  $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


}