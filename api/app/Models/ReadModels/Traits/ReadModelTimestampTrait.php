<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/30/2020
 * Time: 3:01 PM
 */

namespace App\Models\ReadModels\Traits;


/**
 * Class ReadModelTimestampTrait
 * @package App\Models\ReadModels\Traits
 */
trait ReadModelTimestampTrait
{

    private \DateTimeImmutable $createdAt;

    private ?\DateTime $updatedAt = null;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     * @return ReadModelTimestampTrait
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): ReadModelTimestampTrait
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     * @return ReadModelTimestampTrait
     */
    public function setUpdatedAt(?\DateTime $updatedAt): ReadModelTimestampTrait
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}
