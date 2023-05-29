<?php

namespace App\Twig;

class CustomExtension extends \Twig\Extension\AbstractExtension
{
    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('shuffle', [$this, 'shuffleArray']),
        ];
    }

    /**
     * Shuffle the elements of an array.
     *
     * @param array $array The array to be shuffled
     * @return array The shuffled array
     */
    public function shuffleArray($array)
    {
        shuffle($array);

        return $array;
    }
}

?>