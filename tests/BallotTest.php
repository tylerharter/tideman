<?php

namespace PivotLibre\Tideman;

use PHPUnit\Framework\TestCase;

class BallotTest extends GenericCollectionTestCase
{
    private const ALICE_ID = "A";
    private const ALICE_NAME = "Alice";
    private const BOB_ID = "B";
    private const BOB_NAME = "Bob";
    private const CHERYL_ID = "C";
    private const CHERYL_NAME = "Cheryl";
    private const DARIUS_ID = "D";
    private const DARIUS_NAME = "Darius";

    protected $alice;
    protected $bob;
    protected $darius;

    protected function setUpValues() : void
    {
        $this->alice = new Candidate(self::ALICE_ID, self::ALICE_NAME);
        $this->bob = new Candidate(self::BOB_ID, self::BOB_NAME);
        $tiedCandidateList = new CandidateList($this->alice, $this->bob);
        $this->darius = new Candidate(self::DARIUS_ID, self::DARIUS_NAME);
        $anotherCandidateList = new CandidateList($this->darius);
        $this->values = array($tiedCandidateList, $anotherCandidateList);
    }
    protected function setUp()
    {
        $this->setUpValues();
        $this->instance = new Ballot(...$this->values);
        $this->concreteType = Ballot::class;
    }

    public function testTieBreakingOnBallotWithoutTies() : void
    {
        $expectedCandidateOrder = Ballot::wrapEachInCandidateList($this->darius, $this->bob, $this->alice);
        $ballotWithoutTies = new Ballot(...Ballot::wrapEachInCandidateList($this->darius, $this->bob, $this->alice));

        $actualCandidateOrder = $ballotWithoutTies->getCopyWithRandomlyResolvedTies()->toArray();
        $this->assertEquals($expectedCandidateOrder, $actualCandidateOrder);
    }

    public function testTieBreakingOnBallotWithTies() : void
    {
        $expectedCandidateOrder = Ballot::wrapEachInCandidateList($this->bob, $this->alice, $this->darius);
        //seed the random number generator so that we can reliably test
        mt_srand(4242);
        try {
            $actualCandidateOrder = $this->instance->getCopyWithRandomlyResolvedTies()->toArray();
            $this->assertEquals($expectedCandidateOrder, $actualCandidateOrder);
        } finally {
            //reset the random number generator
            mt_srand();
        }
    }
}
