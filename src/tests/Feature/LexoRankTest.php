<?php

use Dede\Lexorank\tests\BaseTest;
use Dede\Lexorank\tests\models\LexoRankEntity;

uses(BaseTest::class);

beforeEach(function () {
    LexoRankEntity::boot();
});

// Test: Create a new entity and verify its position
it('can create a new entity and get position "a"', function () {
    $entity = createAndSaveEntity();
    $this->assertDatabaseHas('lexo_rank_entities', ['id' => $entity->id]);
    $this->assertEquals('a', $entity->position);
});

// Test: Get position "b" after "a"
it("get position 'b' after 'a'", function () {
    $entityA = createAndSaveEntity();
    $entityB = createAndSaveEntity();
    $this->assertEquals('a', $entityA->position);
    $this->assertEquals('b', $entityB->position);
});

// Test: Get position "za" after "z"
it('get "za" after "z"', function () {
    for ($i = 0; $i < 26; $i++) {
        createAndSaveEntity();
    }
    $entityZ = createAndSaveEntity();
    $this->assertEquals('za', $entityZ->position);
});

// Test: Move entity A after B, and entity C after B
it("get 'ba' after move 'a'", function () {
    $entityA = createAndSaveEntity();
    $entityB = createAndSaveEntity();
    $entityC = createAndSaveEntity();

    moveEntityAfter($entityA, $entityB);
    moveEntityAfter($entityB, $entityA);
    moveEntityAfter($entityC, $entityB);

    $this->assertEquals('bU', $entityA->position);
    $this->assertEquals('bg', $entityB->position);
    $this->assertEquals('bp', $entityC->position);
});

// Test: Reset all entity positions
it("reset position", function () {
    $entityA = createAndSaveEntity();
    $entityB = createAndSaveEntity();
    $entityC = createAndSaveEntity();
    moveEntityAfter($entityA, $entityB);
    moveEntityAfter($entityB, $entityA);
    moveEntityAfter($entityC, $entityB);

    LexoRankEntity::resetPositions();

    $entityA->refresh();
    $entityB->refresh();
    $entityC->refresh();

    $this->assertEquals('a', $entityA->position);
    $this->assertEquals('b', $entityB->position);
    $this->assertEquals('c', $entityC->position);

});

it('get "aU" if C was moved za and zb', function () {
    
    $entityA = createAndSaveEntity();
    $entityB = createAndSaveEntity();
    $entityC = createAndSaveEntity();
    
    $entityC->moveAfter($entityA);

    $this->assertEquals('aU', $entityC->position);
});

// Helper functions to reduce code duplication
function createAndSaveEntity() {
    $entity = new LexoRankEntity();
    $entity->save();
    return $entity;
}

function moveEntityAfter($entity1, $entity2) {
    $entity1->moveAfter($entity2);
}
