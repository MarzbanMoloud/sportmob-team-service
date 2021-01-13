<?php


namespace App\Models\ReadModels\Embedded;


/**
 * Class TeamName
 * @package App\Models\ReadModels\Embedded
 */
class TeamName
{
	private string $original;
	private string $official;
	private string $short;

	/**
	 * @return string
	 */
	public function getOriginal(): string
	{
		return $this->original;
	}

	/**
	 * @param string $original
	 * @return TeamName
	 */
	public function setOriginal(string $original): TeamName
	{
		$this->original = $original;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getOfficial(): string
	{
		return $this->official;
	}

	/**
	 * @param string $official
	 * @return TeamName
	 */
	public function setOfficial(string $official): TeamName
	{
		$this->official = $official;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getShort(): string
	{
		return $this->short;
	}

	/**
	 * @param string $short
	 * @return TeamName
	 */
	public function setShort(string $short): TeamName
	{
		$this->short = $short;
		return $this;
	}
}