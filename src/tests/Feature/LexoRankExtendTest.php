<?php

use Dede\Lexorank\tests\BaseTest;
use Dede\Lexorank\tests\models\LexoRankEntity;

uses(BaseTest::class);

uses()->group('extend');

beforeEach(function () {
    $this->model = new LexoRankEntity();
});

it('default sortable Field return position', function () {
    $this->assertEquals('position', $this->model->getSortableField());
});

it("can override sortable Field", function () {
    $model = new class extends LexoRankEntity {
        protected static $sortableField = 'rank';
    };

    $this->assertEquals('rank', $model->getSortableField());
});

it("can override applySortableQuery", function () {
    $model = new class extends LexoRankEntity {
        protected static function applySortableQuery($query, $model)
        {
            return $query->where('id', 2);
        }
    };

    $model->newQuery()->create();
    $model->newQuery()->create();

    $this->assertEquals(2, $model->newQuery()->latest('id')->first()->id);
});