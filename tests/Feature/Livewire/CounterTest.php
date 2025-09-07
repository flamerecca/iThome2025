<?php

declare(strict_types=1);

use Livewire\Volt\Volt as LivewireVolt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('defaults to 0 and increments twice to 2', function () {
    LivewireVolt::test('counter')
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->call('increment')
        ->assertSet('count', 2)
        ->assertSee('2');
});

it('respects initial prop', function () {
    LivewireVolt::test('counter', ['initial' => 5])
        ->assertSet('count', 5)
        ->assertSee('5');
});

it('stops at max and does not increment beyond', function () {
    LivewireVolt::test('counter', ['initial' => 5, 'max' => 6])
        ->assertSet('count', 5)
        ->call('increment')
        ->assertSet('count', 6)
        ->call('increment')
        ->assertSet('count', 6);
});

it('dispatches counter-incremented event on success', function () {
    LivewireVolt::test('counter')
        ->call('increment')
        ->assertDispatched('counter-incremented', ['count' => 1]);
});
