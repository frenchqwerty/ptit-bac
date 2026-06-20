<?php

use App\Domain\Score\Services\ScoreEngine;

beforeEach(function (): void {
    $this->engine = new ScoreEngine;
});

describe('ScoreEngine', function (): void {
    it('gives 0 for empty answers', function (): void {
        $answers = [
            1 => ['city' => '', 'country' => '', 'first_name' => '', 'pokemon' => '', 'brand' => '', 'movie' => ''],
        ];

        $results = $this->engine->calculate($answers, 'M');

        expect($results['1']['round_score'])->toBe(0);
        expect($results['1']['has_perfect'])->toBeFalse();
    });

    it('gives 10 for unique valid answer', function (): void {
        $answers = [
            1 => ['city' => 'Marseille', 'country' => '', 'first_name' => '', 'pokemon' => '', 'brand' => '', 'movie' => ''],
            2 => ['city' => 'Milan', 'country' => '', 'first_name' => '', 'pokemon' => '', 'brand' => '', 'movie' => ''],
        ];

        $results = $this->engine->calculate($answers, 'M');

        // Both gave different answers starting with M — each gets 10
        expect($results['1']['categories']['city']['points'])->toBe(10);
        expect($results['2']['categories']['city']['points'])->toBe(10);
    });

    it('gives 5 for shared valid answer', function (): void {
        $answers = [
            1 => ['city' => 'Marseille', 'country' => '', 'first_name' => '', 'pokemon' => '', 'brand' => '', 'movie' => ''],
            2 => ['city' => 'Marseille', 'country' => '', 'first_name' => '', 'pokemon' => '', 'brand' => '', 'movie' => ''],
        ];

        $results = $this->engine->calculate($answers, 'M');

        expect($results['1']['categories']['city']['points'])->toBe(5);
        expect($results['2']['categories']['city']['points'])->toBe(5);
    });

    it('adds perfect bonus of 20 when all categories are valid', function (): void {
        $answers = [
            1 => [
                'city' => 'Marseille',
                'country' => 'Madagascar',
                'first_name' => 'Marc',
                'pokemon' => 'Magicarpe',
                'brand' => 'Microchips',
                'movie' => 'Matrix',
            ],
        ];

        $results = $this->engine->calculate($answers, 'M');

        expect($results['1']['has_perfect'])->toBeTrue();
        // 6 unique answers × 10 + 20 bonus = 80
        expect($results['1']['round_score'])->toBe(80);
    });

    it('ignores answers not starting with the letter', function (): void {
        $answers = [
            1 => ['city' => 'Paris', 'country' => '', 'first_name' => '', 'pokemon' => '', 'brand' => '', 'movie' => ''],
        ];

        $results = $this->engine->calculate($answers, 'M');

        expect($results['1']['categories']['city']['valid'])->toBeFalse();
        expect($results['1']['categories']['city']['points'])->toBe(0);
    });

    it('draws a valid letter', function (): void {
        $letter = ScoreEngine::drawLetter();
        expect($letter)->toBeString();
        expect(strlen($letter))->toBe(1);
        expect(ctype_upper($letter))->toBeTrue();
    });

    it('calculates unique count per player', function (): void {
        $answers = [
            1 => ['city' => 'Marseille', 'country' => 'Maroc', 'first_name' => 'Marc', 'pokemon' => 'Magicarpe', 'brand' => 'Microchips', 'movie' => 'Matrix'],
            2 => ['city' => 'Monaco', 'country' => 'Maroc', 'first_name' => 'Marie', 'pokemon' => 'Mewtwo', 'brand' => 'Microsoft', 'movie' => 'Matrix'],
        ];

        $results = $this->engine->calculate($answers, 'M');

        // Player 1: Marseille(unique), Maroc(shared), Marc(unique), Magicarpe(unique), Microchips(unique), Matrix(shared)
        expect($results['1']['unique_count'])->toBe(4);
    });
});
