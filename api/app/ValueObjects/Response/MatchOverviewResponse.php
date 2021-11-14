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
    private ?TeamRankingResponse $ranking = null;

    /**
     * @param MatchResponse $match
     * @param TeamRankingResponse|null $homeTeamFormSymbols
     * @param TeamFormSymbolsResponse|null $awayTeamFormSymbols
     * @param TeamRankingResponse|null $ranking
     * @return MatchOverviewResponse
     */
    public static function create(
        MatchResponse $match,
        ?TeamRankingResponse $homeTeamFormSymbols,
        ?TeamFormSymbolsResponse $awayTeamFormSymbols,
        ?TeamRankingResponse $ranking
    ): MatchOverviewResponse
    {
        $instance = new self;
        $instance->match = $match;
        $instance->homeTeamFormSymbols = $homeTeamFormSymbols;
        $instance->awayTeamFormSymbols = $awayTeamFormSymbols;
        $instance->ranking = $ranking;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'match' => $this->match->toArray(),
            'homeTeamFormSymbols' => $this->homeTeamFormSymbols->toArray(),
            'awayTeamFormSymbols' => $this->awayTeamFormSymbols->toArray(),
            'ranking' => $this->ranking->toArray(),
        ]);
    }
}