<?php


namespace App\ValueObjects\Response;


/**
 * Class Head2HeadResponse
 * @package App\ValueObjects\Response
 */
class Head2HeadResponse
{
    private ?AggregationResponse $aggregation = null;
    private ?array $matches = null;

	/**
	 * @param AggregationResponse|null $aggregation
	 * @param array|null $matches
	 * @return Head2HeadResponse
	 */
    public static function create(
		?AggregationResponse $aggregation = null,
		?array $matches = null
	): Head2HeadResponse {
        $instance = new self();
        $instance->aggregation = $aggregation;
        $instance->matches = $matches;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'aggregation' => $this->aggregation ? $this->aggregation->toArray() : null,
            'matches' => $this->matches
        ];
    }
}
