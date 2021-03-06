<?php

namespace App\Repository;

use App\Entity\Feed;
use App\Entity\FeedData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FeedRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feed::class);
    }

    /**
     * Create and persist Feed dependent FeedData according to it type.
     */
    public function createDependentFeedData(Feed $feed): void
    {
        $entityManager = $this->getEntityManager();
        $feedDataRepository = $entityManager->getRepository('App:FeedData');
        \assert($feedDataRepository instanceof FeedDataRepository);

        // We check, for this feed, if each dataFeeds are already created,
        // and create it if not.
        foreach (Feed::getDataTypeFor($feed->getFeedType()) as $label) {
            $feedData = $feedDataRepository->findOneBy([
                'feed' =>  $feed,
                'dataType' => $label
            ]);

            if (!$feedData) {
                $feedData = new FeedData();
                $feedData->setDataType($label);
                $feedData->setFeed($feed);
                $entityManager->persist($feedData);
            }
        }
    }

    /**
     * Remove ALL data (feedData and dataValue) for a feed and then feed itself
     */
    public function purge(Feed $feed)
    {
        $feedDataRepository = $this->getEntityManager()->getRepository('App:FeedData');
        \assert($feedDataRepository instanceof FeedDataRepository);

        foreach ($feedDataRepository->findByFeed($feed) as $feedData) {
            $feedDataRepository->purge($feedData);
        }

        $this
            ->createQueryBuilder('f')
            ->delete()
            ->where('f.id = :id')
            ->setParameter('id', $feed->getId())
            ->getQuery()
            ->execute()
        ;
    }

    public function findAllActive($feedDataProviderType = null)
    {
        $queryBuilder = $this
            ->createQueryBuilder('f')
            ->select()
            ->innerJoin('f.place', 'p')
            ->innerJoin('p.user', 'u')
            ->where('u.active = 1')
        ;

        if ($feedDataProviderType) {
            $queryBuilder
                ->where('f.feedDataProviderType = :type')
                ->setParameter('type', $feedDataProviderType)
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get Date of last up to date data.
     */
    public function getLastUpToDate(array $feeds): ?\DateTime
    {
        $feedDataRepository = $this->getEntityManager()->getRepository('App:FeedData');
        \assert($feedDataRepository instanceof FeedDataRepository);

        // Get all feedData.
        $feedDataList = $feedDataRepository->findByFeed($feeds);

        // Foreach feedData we get the last up to date value.
        /** @var \App\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            // A feed is up to date only if one feedData is up to date.
            // Well it could be that we will never have some feedData for a Feed. (in particular nebulosity from meteofrance)
            // In this case, if we choose that a feed is up to date only if all its feedData
            // are up to date, we will try to get this missing data over and over and flood the api
            // of the feed AND that's not cool :( and we try to be cool people :)

            $feedDataLastUpToDate = $feedDataRepository->getLastUpToDate($feedData);

            if (empty($lastUpToDate)) {
                $lastUpToDate = $feedDataLastUpToDate;
            }

            $lastUpToDate = max($lastUpToDate, $feedDataLastUpToDate);
        }

        // If we have no data, we start with yesterday
        if (empty($lastUpToDate)) {
            $lastUpToDate = new \DateTime("2 days ago");
        }

        return $lastUpToDate->add(new \DateInterval('P1D'));
    }

    /**
     * Check if there's data in DB for $date for all $feed's feedData and for all $frequencies.
     */
    public function isUpToDate(Feed $feed, \DateTimeImmutable $date, array $frequencies): bool
    {
        $feedDataRepository = $this->getEntityManager()->getRepository('App:FeedData');
        \assert($feedDataRepository instanceof FeedDataRepository);

        // Get all feedData.
        $feedDataList = $feedDataRepository->findByFeed($feed);

        $isUpToDate = true;

        // Foreach feedData we check if we have a value for yesterday.
        /** @var \App\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            // A feed is up to date only if all its feedData are up to date.
            $isUpToDate = $isUpToDate && $feedDataRepository->isUpToDate($feedData, $date, $frequencies);
        }

        return $isUpToDate;
    }
}
