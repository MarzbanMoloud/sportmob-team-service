<?php


namespace App\Http\Resources\Admin;


use Illuminate\Http\Resources\Json\JsonResource;


/**
 * Class TeamResource
 * @package App\Http\Resources\Admin
 */
class TeamResource extends JsonResource
{
	/**
	 * TeamResource constructor.
	 * @param $resource
	 */
	public function __construct($resource)
	{
		parent::__construct($resource);
	}

	/**
	 * @param $resource
	 * @return array|array[]
	 */
	public function toArray($resource): array
	{
		return [
			'links' => [],
			'data' => [
				'id' => $this->resource->getId(),
				'name' => [
					'original' => $this->resource->getName()->getOriginal(),
					'official' => $this->resource->getName()->getOfficial(),
					'short' => $this->resource->getName()->getshort(),
				],
				'country' => $this->resource->getCountry(),
				'city' => $this->resource->getCity(),
				'founded' => $this->resource->getFounded(),
				'gender' => $this->resource->getGender(),
				'active' => $this->resource->isActive(),
				'type' => $this->resource->getType()
			]
		];
	}
}