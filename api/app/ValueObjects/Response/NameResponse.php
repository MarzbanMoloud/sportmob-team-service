<?php


namespace App\ValueObjects\Response;


/**
 * Class NameResponse
 * @package App\ValueObjects\Response
 */
class NameResponse
{
    private string $full;
    private ?string $short;
    private ?string $official;

	/**
	 * @param string $full
	 * @param string|null $short
	 * @param string|null $official
	 * @return NameResponse
	 */
	public static function create(
    	string $full,
		?string $short = null,
		?string $official = null
	): NameResponse {
        $instance = new self();
        $instance->full = $full;
        $instance->short = $short;
        $instance->official = $official;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'full' => $this->full,
            'short' => $this->short ?? null,
            'official' => $this->official ?? null
        ]);
    }
}
