<?php


namespace App\ValueObjects\Response;


/**
 * Class ResultResponse
 * @package App\ValueObjects\Response
 */
class ResultResponse
{
	private HomeAwayResponse $total;
	private ?HomeAwayResponse $penalty = null;
	private ?HomeAwayResponse $aggregate = null;
	private ?HomeAwayResponse $firstHalf = null;
	private ?HomeAwayResponse $secondHalf = null;
	private ?HomeAwayResponse $extraHalf = null;

	/**
	 * @param HomeAwayResponse $total
	 * @param HomeAwayResponse|null $penalty
	 * @param HomeAwayResponse|null $aggregate
	 * @param HomeAwayResponse|null $firstHalf
	 * @param HomeAwayResponse|null $secondHalf
	 * @param HomeAwayResponse|null $extraHalf
	 * @return ResultResponse
	 */
	public static function create(
		HomeAwayResponse $total,
		?HomeAwayResponse $penalty = null,
		?HomeAwayResponse $aggregate = null,
		?HomeAwayResponse $firstHalf = null,
		?HomeAwayResponse $secondHalf = null,
		?HomeAwayResponse $extraHalf = null
	): ResultResponse {
		$instance = new self();
		$instance->total = $total;
		$instance->penalty = $penalty;
		$instance->aggregate = $aggregate;
		$instance->firstHalf = $firstHalf;
		$instance->secondHalf = $secondHalf;
		$instance->extraHalf = $extraHalf;
		return $instance;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return array_filter([
			'total' => $this->total->toArray(),
			'penalty' => $this->penalty ? $this->penalty->toArray() : null,
			'aggregate' => $this->aggregate ? $this->aggregate->toArray() : null,
			'firstHalf' => $this->firstHalf ? $this->firstHalf->toArray() : null,
			'secondHalf' => $this->secondHalf ? $this->secondHalf->toArray() : null,
			'extraHalf' => $this->extraHalf ? $this->extraHalf->toArray() : null,
		]);
	}
}
