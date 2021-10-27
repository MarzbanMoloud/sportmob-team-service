<?php


namespace App\ValueObjects\Response;


/**
 * Class CountryResponse
 * @package App\ValueObjects\Response
 */
class CountryResponse
{
    private string $id;
    private ?string $name = null;

    /**
     * @param string $id
     * @param string $name
     * @return CountryResponse
     */
    public static function create(
    	string $id,
		?string $name = null
	): CountryResponse {
        $instance = new self();
        $instance->id = $id;
        $instance->name = $name;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}
