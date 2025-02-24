<?php
namespace Dede\Lexorank\tests\models;

use Dede\Lexorank\LexoRankTrait;
use Illuminate\Database\Eloquent\Model;

class LexoRankEntity extends Model
{
    protected $table = 'lexo_rank_entities';
    
    use LexoRankTrait;
}
