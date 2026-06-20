<?php

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Player Auth API', function (): void {
    it('creates a new player on first login', function (): void {
        $response = $this->postJson('/api/auth/login', ['name' => 'Thomas']);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'player' => ['id', 'uuid', 'display_name', 'elo_rating'],
                'token',
            ]);

        expect(Player::where('name', 'thomas')->exists())->toBeTrue();
    });

    it('returns same player on second login', function (): void {
        $this->postJson('/api/auth/login', ['name' => 'Thomas']);
        $response = $this->postJson('/api/auth/login', ['name' => 'Thomas']);

        $response->assertStatus(200);
        expect(Player::where('name', 'thomas')->count())->toBe(1);
    });

    it('fails without name', function (): void {
        $response = $this->postJson('/api/auth/login', []);
        $response->assertStatus(422);
    });

    it('returns me when authenticated', function (): void {
        $loginRes = $this->postJson('/api/auth/login', ['name' => 'Lucas']);
        $token = $loginRes->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('player.display_name', 'Lucas');
    });

    it('returns 401 without token', function (): void {
        $response = $this->getJson('/api/auth/me');
        $response->assertStatus(401);
    });
});
