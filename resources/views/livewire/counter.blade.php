<?php

use Livewire\Volt\Component;

new class extends Component {
    public int $count = 0;
    public int $initial = 0;
    public ?int $max = null;

    public function mount(int $initial = 0, ?int $max = null): void
    {
        $this->initial = $initial;
        $this->count = $initial;
        $this->max = $max;
    }

    public function increment(): void
    {
        if ($this->max !== null && $this->count >= $this->max) {
            return;
        }

        $this->count++;
        $this->dispatch('counter-incremented', ['count' => $this->count]);
    }
};
?>

<div class="inline-flex items-center gap-3">
    <span class="text-2xl font-medium" aria-live="polite">{{ $count }}</span>

    <button
        type="button"
        wire:click="increment"
        aria-label="increase counter"
        @if($max !== null && $count >= $max) disabled @endif
        class="px-3 py-2 rounded bg-blue-600 text-white disabled:bg-gray-400"
    >
        +1
    </button>
</div>
