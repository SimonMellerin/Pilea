<?php

namespace App\Repository;

use App\Entity\DataValue;
use App\Entity\FeedData;
use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * FeedDataRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FeedDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedData::class);
    }

    /**
     * Remove ALL data (feedData and dataValue) for a feed and then feed itself
     */
    public function purge(FeedData $feedData)
    {
        $dataValueRepository = $this->getEntityManager()->getRepository('App:DataValue');
        \assert($dataValueRepository instanceof DataValueRepository);

        $dataValueRepository
            ->createQueryBuilder('v')
            ->delete()
            ->where('v.feedData = :id')
            ->setParameter('id', $feedData->getId())
            ->getQuery()
            ->execute()
        ;

        $this
            ->createQueryBuilder('f')
            ->delete()
            ->where('f.id = :id')
            ->setParameter('id', $feedData->getId())
            ->getQuery()
            ->execute()
        ;
    }

    public function findOneByPlaceAndDataType(Place $place, string $dataType)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('fd');

        $queryBuilder->select()
            ->innerJoin('fd.feed', 'f')
            ->where('f.place = :place')
            ->setParameter('place', $place)
            ->andWhere('fd.dataType = :dataType')
            ->setParameter('dataType', $dataType)
        ;

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Update or Create a new DataValue and persist it.
     *
     * @param \DateInterval $date
     * @param int $frequency
     * @param string $value
     * @param EntityManager $entityManager
     */
    public function updateOrCreateValue(FeedData $feedData, \DateTimeImmutable $date, $frequency, $value)
    {
        $dataValueRepository = $this->getEntityManager()->getRepository('App:DataValue');
        \assert($dataValueRepository instanceof DataValueRepository);

        // Update date according to frequnecy
        $date = DataValue::adaptToFrequency($date, $frequency);

        $criteria = [
            'feedData' => $feedData,
            'date' => $date,
            'frequency' => $frequency,
        ];

        // Try to get the corresponding DataValue.
        $dataValue = $dataValueRepository->findOneBy($criteria);

        // Create it if it doesn't exist.
        if (!isset($dataValue)) {
            $dataValue = new DataValue();
            $dataValue->setFrequency($frequency);
            $dataValue->setFeedData($feedData);
            $dataValue->setDate($date);
        }

        if ($frequency <= DataValue::FREQUENCY['HOUR']) $dataValue->setHour($date->format('H'));
        $weekDay = $date->format('w') == 0 ? 6 : $date->format('w') - 1;
        if ($frequency <= DataValue::FREQUENCY['DAY']) $dataValue->setWeekDay($weekDay);
        if ($frequency <= DataValue::FREQUENCY['WEEK']) $dataValue->setWeek($date->format('W'));
        if ($frequency <= DataValue::FREQUENCY['MONTH']) $dataValue->setMonth($date->format('m'));
        if ($frequency <= DataValue::FREQUENCY['YEAR']) $dataValue->setYear($date->format('Y'));

        $dataValue->setValue($value);

        // Persit the dataValue.
        $this->getEntityManager()->persist($dataValue);
    }

    /**
     * Get Date of last up to date data.
     * @param EntityManager $entityManager
     * @param $frequencies array of int from DataValue frequencies
     *
     * @return \Datetime
     */
    public function getLastUpToDate(FeedData $feedData)
    {
        $dataValueRepository = $this->getEntityManager()->getRepository('App:DataValue');
        \assert($dataValueRepository instanceof DataValueRepository);

        // Try to get the corresponding DataValue.

        $result = $dataValueRepository->getLastValue($feedData, DataValue::FREQUENCY['DAY']);

        if (!empty($result[0]['date'])) {
            return new \DateTime($result[0]['date']);
        }

        return null;
    }

    /**
     * Check if there's data in DB for $date for all $frequencies.
     * @param EntityManager $entityManager
     * @param \DateTime $date
     * @param $frequencies array of int from DataValue frequencies
     */
    public function isUpToDate(FeedData $feedData, \DateTimeImmutable $date, array $frequencies)
    {
        $isUpToDate = true;

        // Foreach frequency we check if we have a value for date.
        foreach ($frequencies as $frequency) {
            $criteria = [
                'feedData' => $feedData,
                'date' => DataValue::adaptToFrequency($date, $frequency),
                'frequency' => $frequency,
            ];

            // Try to get the corresponding DataValue.
            $dataValue = $this->getEntityManager()->getRepository('App:DataValue')->findBy($criteria);

            // A feed is up to date only if all its feedData are up to date.
            $isUpToDate = $isUpToDate && !empty($dataValue);
        }

        return $isUpToDate;
    }
}
