<?php

use App\Models\GameRoom;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAuthenticatedPlayer(): array
{
    $res = test()->postJson('/api/auth/login', ['name' => fake()->firstName()]);

    return [
        'player' => Player::find($res->json('player.id')),
        'token' => $res->json('token'),
        'headers' => ['Authorization' => 'Bearer '.$res->json('token')],
    ];
}

describe('Game Room API', function (): void {
    it('creates a room', function (): void {
        $auth = createAuthenticatedPlayer();

        $response = $this->withHeaders($auth['headers'])
            ->postJson('/api/rooms', ['total_rounds' => 3, 'max_players' => 5]);

        $response->assertStatus(201)
            ->assertJsonStructure(['room' => ['code', 'status', 'host']]);

        expect(GameRoom::count())->toBe(1);
    });

    it('lets another player join', function (): void {
        $host = createAuthenticatedPlayer();
        $guest = createAuthenticatedPlayer();

        $createRes = $this->withHeaders($host['headers'])->postJson('/api/rooms', []);
        $code = $createRes->json('room.code');

        $joinRes = $this->withHeaders($guest['headers'])->postJson("/api/rooms/{$code}/join");
        $joinRes->assertStatus(200);

        expect(GameRoom::where('code', $code)->first()->players()->count())->toBe(2);
    });

    it('prevents joining full room', function (): void {
        $host = createAuthenticatedPlayer();
        $createRes = $this->withHeaders($host['headers'])
            ->postJson('/api/rooms', ['max_players' => 2]);
        $code = $createRes->json('room.code');

        $guest1 = createAuthenticatedPlayer();
        $this->withHeaders($guest1['headers'])->postJson("/api/rooms/{$code}/join");

        $guest2 = createAuthenticatedPlayer();
        $joinRes = $this->withHeaders($guest2['headers'])->postJson("/api/rooms/{$code}/join");
        $joinRes->assertStatus(422);
    });

    it('lists public waiting rooms', function (): void {
        $auth = createAuthenticatedPlayer();
        $this->withHeaders($auth['headers'])->postJson('/api/rooms', []);

        $response = $this->getJson('/api/rooms');
        $response->assertStatus(200)->assertJsonStructure(['rooms']);
        expect(count($response->json('rooms')))->toBe(1);
    });
});
