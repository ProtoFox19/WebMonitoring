<?php
/**
 * Created by PhpStorm.
 * User: Christoph
 * Date: 04.12.2017
 * Time: 22:45
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WebRepository")
 * @ORM\Table(name="webs")
 * @UniqueEntity(fields={"name"}, message="The Name is already registered")
 * @UniqueEntity(fields={"domain"}, message="The Domain is already registered")
 */
class Web
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", unique=true)
     */
    private $name;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", unique=true)
     */
    private $domain;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    /**
     * The relation is not displayed in the Database, just for Doctrine (for mapping, so an extra query isn't used)
     * @ORM\OneToOne(targetEntity="SitemapSettings", mappedBy="web", orphanRemoval=true)
     */
    private $sitemapSettings;

  /*  public function __construct()
    {
        $this->sitemapSettings = new ArrayCollection();
    }*/

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getSitemapSettings()
    {
        return $this->sitemapSettings;
    }




}