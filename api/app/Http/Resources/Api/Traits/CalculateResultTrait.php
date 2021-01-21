<?php


namespace App\Http\Resources\Api\Traits;


/**
 * Trait CalculateResultTrait
 * @package App\Http\Resources\Api\Traits
 */
trait CalculateResultTrait
{
	/**
	 * @param array $result
	 * @return array
	 */
	private function getResult(array $result): array
	{
		if (empty($result)) {
			return $result;
		}
		return [
			'score' => [
				"home" => !empty($result['penalty']) ? $result['total']['home'] - $result['penalty']['home'] : $result['total']['home'],
				"away" => !empty($result['penalty']) ? $result['total']['away'] - $result['penalty']['away'] : $result['total']['away'],
			],
			'penalty' => !empty($result['penalty']) ? $result['penalty'] : [],
		];
	}
}