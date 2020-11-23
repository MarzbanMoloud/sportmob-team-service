<?php
/**
 * Created by PhpStorm.
 * User: m.marzban
 * Date: 7/30/2020
 * Time: 3:01 PM
 */

namespace App\Models\ReadModels\Traits;


use Carbon\Carbon;


/**
 * Class ReadModelTimestampTrait
 * @package App\Models\ReadModels\Traits
 */
trait ReadModelTimestampTrait
{
    /**
     * @var string
     */
    private string $createdAt;

    /**
     * @var string
     */
    private ?string $updatedAt = null;

    /**
     * @return string
     */
    public function getCreatedAt(): string
	{
		return $this->createdAt;
	}

    /**
     *
     */
    public function setCreatedAt()
	{
		$this->createdAt = Carbon::now()->toDateTimeString();
	}

    /**
     * @return string
     */
    public function getUpdatedAt(): ?string
	{
		return $this->updatedAt;
	}

    /**
     *
     */
    public function setUpdatedAt()
	{
		$this->updatedAt = Carbon::now()->toDateTimeString();
	}

    /**
     *
     */
    public function updated()
	{
		$this->updatedAt = Carbon::now()->toDateTimeString();
	}
}
