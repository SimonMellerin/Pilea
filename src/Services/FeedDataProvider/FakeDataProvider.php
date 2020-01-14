<?php
namespace App\Services\FeedDataProvider;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;

/**
 * Fake data provider
 *
 * @warning Only use for development purpose
 * @see App\Command\Dev\GenerateFakeDataCommand
 */
class FakeDataProvider extends AbstractFeedDataProvider {

    public function fetchData(\Datetime $date, array $feeds, bool $force = false)
    {
        foreach ($feeds as $feed) {
            if ($force || !$this->feedRepository->isUpToDate($feed, $date, $feed->getFrequencies())) {
                switch ($feed->getFeedType()) {
                    case Feed::FEED_TYPE_METEO :
                        $this->generateMeteoData($date, $feed);
                        break;
                    case Feed::FEED_TYPE_ELECTRICITY:
                        $this->generateElectricityData($date, $feed);
                        break;
                }
            }
        }
    }

    /**
     * Generate Fake data for a meteo typed feed for date for all frenquencies.
     */
    private function generateMeteoData(\DateTime $date, Feed $feed)
    {
        $DataTypes = [
            FeedData::FEED_DATA_TEMPERATURE => [
                'min' => -10,
                'max' => 40,
                'operator' => 'AVG',
            ],
            FeedData::FEED_DATA_TEMPERATURE_MIN => [
                'min' => -10,
                'max' => 40,
                'operator' => 'AVG',
            ],
            FeedData::FEED_DATA_TEMPERATURE_MAX => [
                'min' => -10,
                'max' => 40,
                'operator' => 'AVG',
            ],
            FeedData::FEED_DATA_DJU => [
                'min' => 0,
                'max' => 4,
                'operator' => 'SUM',
            ],
            FeedData::FEED_DATA_HUMIDITY => [
                'min' => 0,
                'max' => 100,
                'operator' => 'AVG',
            ],
            FeedData::FEED_DATA_NEBULOSITY => [
                'min' => 0,
                'max' => 100,
                'operator' => 'AVG',
            ],
            FeedData::FEED_DATA_RAIN => [
                'min' => 0,
                'max' => 20,
                'operator' => 'AVG',
            ],
            FeedData::FEED_DATA_PRESSURE => [
                'min' => 103668,
                'max' => 98167,
                'operator' => 'SUM',
            ],
        ];

        foreach ($this->feedDataRepository->findByFeed($feed) as $feedData) {
            $type = $feedData->getDataType();

            $min = $DataTypes[$type]['min'];
            $max = $DataTypes[$type]['max'];
            $operator = $DataTypes[$type]['operator'];

            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                $date,
                DataValue::FREQUENCY['DAY'],
                \rand($min * 10, $max * 10)/10
            );
            $this->entityManager->flush();

            $this->generateAgregatedData($date, $feedData, DataValue::FREQUENCY['WEEK'], $operator);
            $this->generateAgregatedData($date, $feedData, DataValue::FREQUENCY['MONTH'], $operator);
            $this->generateAgregatedData($date, $feedData, DataValue::FREQUENCY['YEAR'], $operator);
        }
    }

    /**
     * Generate Fake data for a electricty typed feed for date for all frenquencies.
     */
    private function generateElectricityData(\DateTime $date, Feed $feed)
    {
        // Get feedData.
        $feedData = $this->feedDataRepository->findOneByFeed($feed);

        // 0 -> 5 kWh
        for ($hour = 0; $hour < 24; $hour++) {

            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                new \DateTime($date->format("Y-m-d") . $hour . ':00'),
                DataValue::FREQUENCY['HOUR'],
                \rand(0, 50)/10
            );
        }
        $this->entityManager->flush();

        $this->generateAgregatedData($date, $feedData, DataValue::FREQUENCY['DAY']);
        $this->generateAgregatedData($date, $feedData, DataValue::FREQUENCY['WEEK']);
        $this->generateAgregatedData($date, $feedData, DataValue::FREQUENCY['MONTH']);
        $this->generateAgregatedData($date, $feedData, DataValue::FREQUENCY['YEAR']);

        $this->entityManager->flush();
    }

    private function generateAgregatedData(\DateTime $date, FeedData $feedData, int $frequency, string $operator = 'SUM')
    {
        list('from' => $firstDay, 'to' =>  $lastDay, 'previousFrequency' => $previousFrequency) = DataValue::getAdaptedBoundariesForFrequency($date, $frequency);

        switch ($operator) {
            case 'SUM':
                $agregateData = $this
                    ->dataValueRepository
                    ->getSumValue(
                        $firstDay,
                        $lastDay,
                        $feedData,
                        $previousFrequency
                    )
                ;
            case 'AVG':
                $agregateData = $this
                    ->dataValueRepository
                    ->getAverageValue(
                        $firstDay,
                        $lastDay,
                        $feedData,
                        $previousFrequency
                    )
                ;
        }

        if (isset($agregateData[0]['value'])) {
            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                $firstDay,
                $frequency,
                \round($agregateData[0]['value'], 1)
            );
        }

        $this->entityManager->flush();
    }
}