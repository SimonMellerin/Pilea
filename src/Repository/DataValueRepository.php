<?php

namespace App\Repository;

use App\Controller\DataController;
use App\Entity\DataValue;
use App\Entity\FeedData;
use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * DataValueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DataValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataValue::class);
    }

    /**
     * Get an average value
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param FeedData $feedData
     * @param int $frequency
     */
    public function getAverageValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('AVG(d.value) AS value');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.feedData');
        return $queryBuilder
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Get an minimum value
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param FeedData $feedData
     * @param int $frequency
     */
    public function getMinValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('MIN(d.value) AS value, d.date');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.feedData');
        return $queryBuilder
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Get an maximum value
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param FeedData $feedData
     * @param int $frequency
     */
    public function getMaxValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('MAX(d.value) AS value, d.date');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.feedData');
        return $queryBuilder
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Get sum of value
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param FeedData $feedData
     * @param String $frequency
     * @return array|mixed|\Doctrine\DBAL\Driver\Statement|NULL
     */
    public function getSumValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('SUM(d.value) AS value');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.feedData');

        return $queryBuilder
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Get XY
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param FeedData $feedDataX
     * @param FeedData $feedDataY
     * @param String $frequency
     * @return array|mixed|\Doctrine\DBAL\Driver\Statement|NULL
     */
    public function getXY(\DateTime $startDate, \DateTime $endDate, FeedData $feedDataX, FeedData $feedDataY, $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('dx');

        $queryBuilder
            ->select('dx.value AS xValue, dy.value AS yValue, dx.date AS date')
            ->join(DataValue::class, 'dy', Join::WITH, 'dx.date = dy.date')
            // Add condition on dates
            ->andWhere('dx.date BETWEEN :start AND :end')
            ->andWhere('dy.date BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end',   $endDate)
            // Add condition on feedData
            ->andWhere('dx.feedData = :feedDataX')
            ->setParameter('feedDataX', $feedDataX->getId())
            ->andWhere('dy.feedData = :feedDataY')
            ->setParameter('feedDataY', $feedDataY->getId())
            // Add condition on frequency
            ->andWhere('dx.frequency = :frequency')
            ->andWhere('dy.frequency = :frequency')
            ->setParameter('frequency', $frequency)
            ->addGroupBy('dx.value')
            ->addGroupBy('dy.value')
            ->addGroupBy('dx.date')
            ->orderBy('dx.date', 'asc');

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
    * Get number of item inferior than value
    *
    * @param \DateTime $startDate
    * @param \DateTime $endDate
    * @param string $frequency
    */
    public function getNumberInfValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency, $value)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('COUNT(d.date) AS value');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->andWhere('d.value <= :value');
        $queryBuilder->setParameter('value', $value);
        $queryBuilder->addGroupBy('d.feedData');

        return $queryBuilder
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Get last date value
     *
     * @param FeedData $feedData
     * @param string $frequency
     * @return array|mixed|\Doctrine\DBAL\Driver\Statement|NULL
     */
    public function getLastValue(FeedData $feedData, $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('MAX(d.date) AS date')
        ->andWhere('d.feedData = :feedData')
        ->setParameter('feedData', $feedData->getId())
        ->andWhere('d.frequency = :frequency')
        ->setParameter('frequency', $frequency);

        return $queryBuilder
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * Get value
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param FeedData $feedData
     * @param string $frequency
     */
    public function getValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);

        return $queryBuilder
            ->addGroupBy('d.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get repartition
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param FeedData $feedData
     * @param string $frequency
     */
    public function getRepartitionValue(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $axeX, $axeY, $frequency, $repartitionType)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('AVG(d.value) AS value, d.' . $axeX . ' AS axeX, d.' . $axeY . ' AS axeY');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.' . $axeX);
        $queryBuilder->addGroupBy('d.' . $axeY);

        // If this is a year repartition, we also group by year.
        if (in_array($repartitionType, [DataController::YEAR_HORIZONTAL_REPARTITION, DataController::YEAR_VERTICAL_REPARTITION])) {
          $queryBuilder->addSelect('d.year AS year');
          $queryBuilder->addGroupBy('d.year');
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * Get sum of value group by frequency (day, weekDay, week, month, year)
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param FeedData $feedData
     * @param string $frequency
     * @param string $groupBy
     */
    public function getSumValueGroupBy(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency, $groupBy)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('SUM(d.value) AS value, d.' . $groupBy . ' AS groupBy');
        $this->betweenDateWithFeedDataAndFrequency($startDate, $endDate, $feedData, $frequency, $queryBuilder);
        $queryBuilder->addGroupBy('d.' . $groupBy);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * Add condition on querybuild on:
     *    - dates
     *    - feedData
     *    - frequency
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param FeedData $feedData
     * @param string $frequency
     * @param QueryBuilder $queryBuilder
    */
    public function betweenDateWithFeedDataAndFrequency(\DateTime $startDate, \DateTime $endDate, FeedData $feedData, $frequency, QueryBuilder &$queryBuilder)
    {
        $startDate = DataValue::adaptToFrequency($startDate, $frequency);

        $queryBuilder
            ->andWhere('d.date BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end',   $endDate)
            // Add condition on feedData
            ->andWhere('d.feedData = :feedData')
            ->setParameter('feedData', $feedData->getId())
            // Add condition on frequency
            ->andWhere('d.frequency = :frequency')
            ->setParameter('frequency', $frequency)
            ->addGroupBy('d.date')
            ->orderBy('d.date', 'asc');
    }


    /**
     * Get date interval of data.
     *
     * @return array|mixed|\Doctrine\DBAL\Driver\Statement|NULL
     */
    public function getPeriodDataAmplitude(Place $place)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->select('MIN(d.date), MAX(d.date)')
            ->innerJoin('d.feedData', 'fd')
            ->innerJoin('fd.feed', 'f')
            ->where('f.place = :place')
            ->setParameter('place', $place)
            ->andWhere('d.frequency = :frequency')
            ->setParameter('frequency', 2)
        ;

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
