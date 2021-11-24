<?php


namespace App\ValueObjects\Response;


/**
 * Class StatItemResponse
 * @package App\ValueObjects\Response
 */
class StatItemResponse
{
    private string $code;
    private string $name;
    private string $type;
    private ?string $value = null;
    private ?string $valuePostfix = null;
    private ?array $metaData = null;

    /**
     * @param array|null $metaData
     * @return StatItemResponse
     */
    public function setMetaData(?array $metaData): StatItemResponse
    {
        $this->metaData = $metaData;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValuePostfix(): ?string
    {
        return $this->valuePostfix;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return StatItemResponse
     */
    public function setValue(?string $value): StatItemResponse
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array|null
     */
    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    /**
     * @param string|null $defaultValue
     * @return array
     */
    public function toArray(string $defaultValue = null): array
    {
        $result = [
            'key' => $this->getName(),
            'value' => (($this->getValue()) ? $this->getValue()  : $defaultValue) . $this->getValuePostfix(),
            'type' => $this->getType(),
        ];
        if ($this->getMetaData()) {
            $result['metaData'] = $this->getMetaData();
        }
        return $result;
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $type
     * @param string|null $valuePostfix
     * @param array|null $metaData
     * @return StatItemResponse
     */
    public static function create(
        string $code,
        string $name,
        string $type,
        ?string $valuePostfix = null,
        ?array $metaData = null
    ): StatItemResponse {
        $instance = new self();
        $instance->code = $code;
        $instance->name = $name;
        $instance->type = $type;
        $instance->valuePostfix = $valuePostfix;
        $instance->metaData = $metaData;
        return $instance;
    }
}
