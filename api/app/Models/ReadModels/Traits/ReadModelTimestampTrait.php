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
    private \DateTime $createdAt;
    private ?\DateTime $updatedAt = null;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return ReadModelTimestampTrait
     */
    public function setCreatedAt(\DateTime $createdAt): ReadModelTimestampTrait
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
