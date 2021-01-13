<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/30/2020
 * Time: 3:01 PM
 */

namespace App\Models\ReadModels\Traits;


use DateTimeImmutable;


/**
 * Class ReadModelTimestampTrait
 * @package App\Models\ReadModels\Traits
 */
trait ReadModelTimestampTrait
{
    private DateTimeImmutable $createdAt;
	private ?DateTimeImmutable $updatedAt = null;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

	/**
	 * @param DateTimeImmutable $createdAt
	 * @return $this
	 */
	public function setCreatedAt(DateTimeImmutable $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

	/**
	 * @param DateTimeImmutable|null $updatedAt
	 * @return $this
	 */
	public function setUpdatedAt(?DateTimeImmutable $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
