<?php

namespace Dede\Lexorank\Services;

class LexoRankGenerator
{
    public  $minChar = '0';
    public  $maxChar = 'z';

    private $prev;
    private $next;

    /**
     * Rank constructor.
     */
    public function __construct(string $prev, string $next)
    {

        $this->minChar = config('lexorank.min_char', $this->minChar);
        $this->maxChar = config('lexorank.max_char', $this->maxChar);

        $this->prev = $prev === '' ? $this->minChar : $prev;
        // If no next value is provided, default to MAX_CHAR.
        $this->next = $next === '' ? $this->maxChar : $next;
    }

    /**
     * Instead of using a midpoint algorithm, we simply increment the previous rank.
     *
     * For example:
     *  - 'z' becomes 'za'
     *  - 'za' becomes 'zb'
     *  - 'zb' becomes 'zc'
     *
     * @return string
     */
    public function get(bool $isMoving = false)
    {
        if ($isMoving) {
            return  $this->getMiddleRank();
        }
        return $this->incrementRank($this->prev);
    }

    public function getMiddleRank()
    {
        $rank = '';
        $i = 0;

        while (true) {
            $prevChar = $this->getChar($this->prev, $i, $this->minChar);
            $nextChar = $this->getChar($this->next, $i, $this->maxChar);

            if ($prevChar === $nextChar) {
                $rank .= $prevChar;
                $i++;
                continue;
            }

            $midChar = $this->mid($prevChar, $nextChar);
            if (in_array($midChar, [$prevChar, $nextChar])) {
                $rank .= $prevChar;
                $i++;
                continue;
            }

            $rank .= $midChar;
            break;
        }

        return $rank;
    }

    /**
     * Increment the rank using a simple character incrementing logic.
     *
     * @param string $rank
     * @return string
     */
    private function incrementRank(string $rank): string
    {
        // If the rank is empty, start with 'a'
        if ($rank === '') {
            return 'a';
        }

        // Get the last character of the rank
        $lastChar = substr($rank, -1);

        // If the last character is less than 'z', increment it.
        if ($lastChar < 'z') {
            return substr($rank, 0, -1) . chr(ord($lastChar) + 1);
        }

        // If the last character is already 'z', append an 'a'
        return $rank . 'a';
    }
    
    /**
     * mid
     *
     * @param  string $prev
     * @param  string $next
     * @return string
     */
    private function mid(string $prev, string $next)
    {
        if (ord($prev) > ord($next)) {
            return $prev;
        }

        // Cast the result to an integer to avoid the float-to-int conversion warning
        return chr(intval((ord($prev) + ord($next)) / 2));
    }
    
    /**
     * getChar
     *
     * @param string $s
     * @param int $i
     * @param string $defaultChar
     * @return string
     */
    private function getChar(string $s, int $i, string $defaultChar)
    {
         return $s[$i] ?? $defaultChar;
    }
}
