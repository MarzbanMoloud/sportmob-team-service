<?php
/**
 * Class VarResponse
 * @author Fariba Karimi <f.karimi@tgbsco.com>
 * @package App\ValueObjects\Response
 * Date: 11/15/2021
 * Time: 1:37 PM
 */

namespace App\ValueObjects\Response;

/**
 * Class VarResponse
 * @package App\ValueObjects\Response
 */
class VarResponse
{
    private string $type;
    private string $decision;
    private ?string $outcome = null;
    private ?string $reason = null;

    /**
     * @param string $type
     * @param string $decision
     * @param string|null $outcome
     * @param string|null $reason
     * @return VarResponse
     */
    public static function create(
        string $type,
        string $decision,
        ?string $outcome = null,
        ?string $reason = null
    ): VarResponse
    {
        $instance = new self();
        $instance->type = $type;
        $instance->decision = $decision;
        $instance->outcome = $outcome;
        $instance->reason = $reason;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'decision' => $this->decision,
            'outcome' => $this->outcome,
            'reason' => $this->reason,
        ]);
    }
}