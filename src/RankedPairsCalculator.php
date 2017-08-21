<?php

namespace PivotLibre\Tideman;

use \InvalidArgumentException;
use \Exception;
use PivotLibre\Tideman\MarginList;
use PivotLibre\Tideman\CandidateList;
use PivotLibre\Tideman\CandidateComparator;
use PivotLibre\Tideman\TieBreakingMarginComparator;

class RankedPairsCalculator
{
    private $tieBreakingBallot;

    /**
     * Constructs a Ranked Pairs Calculator, verifying that the specified tie-breaking ballot contains no ties.
     * Retains a copy of the tie-breaking Ballot so that the caller may modify the parameterized Ballot without
     * affecting this class.
     * @param tieBreakingBallot
     */
    public function __construct(Ballot $tieBreakingBallot)
    {
        if ($tieBreakingBallot->containsTies()) {
            throw new InvalidArgumentException("Tie breaking ballot must not contain any ties. $tieBreakingBallot");
        } else {
            $this->tieBreakingBallot = clone $tieBreakingBallot;
        }
    }

    /**
     * @return CandidateList in which the zeroth Candidate is the most preferred, the first Candidate is the second most
     * preferred, and so on until the last Candidate who is the least preferred.
     */
    public function calculate(NBallot ...$nBallots) : CandidateList
    {
        $marginList = $this->getMargins(...$nBallots);
        $sortedMarginList = $this->sortMargins($marginList);
        $rankedCandidates = $this->rankCandidates($sortedMarginList);
    }

    /**
     * Tallies the Margins and returns the Margins with difference properties >= 0
     */
    public function getMargins(NBallot ...$nBallots) : MarginList
    {
        $marginCalculator = new MarginCalculator();
        $marginRegistry = $marginCalculator->calculate(...$nBallots);
        $allMargins = $marginRegistry->getAll();
        $positiveOrZeroMargins = array_filter($allMargins->toArray(), function (Margin $margin) {
            return $margin->getDifference() >= 0;
        });
        $marginList = new MarginList(...$positiveOrZeroMargins);
        return $marginList;
    }

    /**
     * Sorts all Margins in order of descending getDifference(). When Margins have the same difference property, ties
     * are broken according to Tideman and Zavist's 1989 "Complete Independence of Clones" rule using the Ballot passed
     * to this instance's constructor.
     */
    public function sortMargins(MarginList $marginList) : MarginList
    {
        $tieBreaker = new TotallyOrderedBallotMarginTieBreaker(new CandidateComparator($this->tieBreakingBallot));
        $tieBreakingMarginComparator = new TieBreakingMarginComparator($tieBreaker);
        $sortedMargins = usort($positiveOrZeroMargins, $tieBreakingMarginComparator);
        $sortedMarginList = new MarginList(...$sortedMargins);
        return $sortedMargins;
    }

    /**
     * Locks in Margins in order of descending difference, ignoring any Margins that would contradict a
     * previously-locked-in Margin.
     * @param MarginList a MarginList whose Margin's are sorted in order of descending difference.
     * @return CandidateList - a list of Candidates in descending order of preference. Candidates that are more
     * preferred have a lower index than Candidates that are less preferred.
     */
    public function rankCandidates(MarginList $sortedMarginList) : CandidateList
    {
        throw new Exception("Not implemented yet");
    }
}
