<?php
/**
 * Class MatchOverviewResponse
 * @author Fariba Karimi <f.karimi@tgbsco.com>
 * @package App\ValueObjects\Response
 * Date: 11/7/2021
 * Time: 4:15 PM
 */

namespace App\ValueObjects\Response;


/**
 * Class MatchOverviewResponse
 * @package App\ValueObjects\Response
 */
class MatchOverviewResponse
{
    private MatchResponse $match;
    private ?TeamFormSymbolsResponse $homeTeamFormSymbols = null;
    private ?TeamFormSymbolsResponse $awayTeamFormSymbols = null;
    /**
     * @var TeamTableItemResponse[]|null
     */
    private ?array $teamTableInfo = null;

    /**
     * @param MatchResponse $match
     * @param TeamFormSymbolsResponse|null $homeTeamFormSymbols
     * @param TeamFormSymbolsResponse|null $awayTeamFormSymbols
     * @param array|null $teamTableInfo
     * @return MatchOverviewResponse
     */
    public static function create(
        MatchResponse $match,
        ?TeamFormSymbolsResponse $homeTeamFormSymbols = null,
        ?TeamFormSymbolsResponse $awayTeamFormSymbols = null,
        ?array $teamTableInfo = null
    ): MatchOverviewResponse
    {
        $instance = new self;
        $instance->match = $match;
        $instance->homeTeamFormSymbols = $homeTeamFormSymbols;
        $instance->awayTeamFormSymbols = $awayTeamFormSymbols;
        $instance->teamTableInfo = $teamTableInfo;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'match' => $this->match->toArray(),
            'homeTeamFormSymbols' => $this->homeTeamFormSymbols ? $this->homeTeamFormSymbols->toArray() : null,
            'awayTeamFormSymbols' => $this->awayTeamFormSymbols ? $this->awayTeamFormSymbols->toArray() : null,
            'teamTableInfo' => $this->teamTableInfo,
        ]);
    }
}