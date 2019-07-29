<?php

namespace Aptenex\Upp\Parser\Structure\Condition;

use Aptenex\Upp\Helper\ArrayAccess;
use Aptenex\Upp\Parser\Structure\Condition;

class DistributionCondition extends Condition
{

    const CHANNEL_AIRBNB = 'airbnb';
    const CHANNEL_HOMEAWAY = 'homeaway';
    const CHANNEL_RENTIVO = 'rentivo';
    const CHANNEL_RENTALS_UNITED = 'rentalsunited';

    /**
     * @var string[]
     */
    private $channels = [];

    const CHANNELS_LIST = [
        'airbnb',
        'homeaway',
        'rentivo',
        'rentalsunited',
    ];

    /**
     * @return \string[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @param \string[] $channels
     */
    public function setChannels($channels)
    {
        if (!is_array($channels)) {
            $channels = [$channels];
        }

        $this->channels = ArrayAccess::filterByWhitelist($channels, DistributionCondition::CHANNELS_LIST);
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return array_replace(parent::__toArray(), [
            'distributionChannels' => $this->getChannels()
        ]);
    }

}