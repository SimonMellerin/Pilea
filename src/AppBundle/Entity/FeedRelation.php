<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedRelation
 *
 * @ORM\Table(name="feed_relation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FeedRelationRepository")
 */
class FeedRelation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /*
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Feed")
     * @ORM\JoinColumn(nullable=false)
     */
    private $primaryFeed;

    /*
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Feed")
     * @ORM\JoinColumn(nullable=false)
     */
    private $secondaryFeed;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
