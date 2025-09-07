# Livewire 元件規格：點擊後 +1 計數器（Increment Counter)

本文件定義一個以 Livewire 3（搭配 Volt 單檔元件風格）實作的「點擊後 +1」計數器元件之規格，供前後端、設計、與測試參考。除特別註明外，本文檔不限定實際檔名與路徑，但範例以 `resources/views/livewire/counter.blade.php`（Volt 單檔元件）呈現。

- 技術棧：Laravel 12、Livewire 3、Livewire Volt、Tailwind CSS v4、Flux UI Free（可選）
- 語言：繁體中文

## 目標與敘述
- 提供一個簡單的互動元件，顯示目前數值 `count`，並在使用者點擊按鈕後使 `count` 增加 1。
- 元件需在不重新整理頁面的情況下即時更新（Livewire 互動）。
- 元件應易於在任何 Blade 頁面中重複使用。

## 使用情境（Use Cases）
1. 使用者看到一個數字（預設 0），按下「+1」按鈕後，數字立即變成 1、2、3…。
2. 可自訂初始值（例如來自父層傳入或資料庫取得）。
3. 可限制最大值（選用）。

## 功能需求（Functional Requirements）
- 顯示目前計數值 `count`。
- 提供按鈕或可點擊元素，觸發 `increment()` 行為，使 `count = count + 1`。
- 提供屬性/參數：
  - `initial`（number，選填，預設 0）：初始值。
  - `max`（number，選填，預設 null）：若設定，則 `count` 不得超過 `max`。
- 若已達 `max`，應禁用按鈕或提供視覺狀態提示。

## 非功能需求（Non-Functional Requirements）
- 可存取性（a11y）：
  - 按鈕需有可理解的 `aria-label`（例：`aria-label="increase counter"`）。
  - 透過鍵盤（Enter/Space）可觸發。
  - 若禁用狀態，使用 `disabled` 屬性，並有對應的樣式提示顏色。
- 響應式：在不同裝置尺寸下均可正常顯示。
- 效能：避免不必要的重新渲染；使用 Livewire 預設即可。
- i18n：文案可由父層覆寫或使用簡單字串常數，預設顯示「+1」。

## 互動詳述
- Action：`increment()`
  - 條件：若 `max` 為 null 或 `count < max`。
  - 結果：`count++` 並更新 UI。
  - 邊界：若 `count` 在呼叫前已等於/超過 `max`，則不遞增且可視化為禁用。

## 介面與樣式
- 預設使用 Tailwind v4。
- 可選：使用 Flux UI Free 的 `<flux:button/>` 作為按鈕元件，以符合專案 UI 風格。
- 建議樣式：
  - 計數顯示：大字體、字重中等。
  - 按鈕：主要色系，禁用時降低對比。

## 屬性與狀態
- 狀態（State）：
  - `public int $count`（或 `public float $count` 視需求，但本元件以 `int` 為準）。
- 參數（Props）：
  - `public int $initial = 0;`
  - `public ?int $max = null;`
- 初始化：`mount($initial = 0, $max = null)` 中設定 `$this->count = (int) $initial; $this->max = $max !== null ? (int) $max : null;`

## 事件（可選）
- `counter-incremented`：每次成功 +1 後可透過 `$this->dispatch('counter-incremented', count: $this->count)` 對外發布事件，供父層監聽。

## 錯誤處理
- 若 `max` 非數字或小於 `initial`，以就近原則：
  - 將 `max` 設為 `null` 或
  - 規格化：`max = max(initial, max)`。
- 本元件不進行伺服器端資料儲存；如需持久化，須由父層或外部行為處理。

## Volt 單檔元件範例
檔案：`resources/views/livewire/counter.blade.php`

```php
<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state, mount};

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
        $this->dispatch('counter-incremented', count: $this->count);
    }
};
?>

<div class="inline-flex items-center gap-3">
    <span class="text-2xl font-medium" aria-live="polite">{{ $count }}</span>

    <!-- 使用 Flux UI（若可用）-->
    @if (class_exists('Livewire\\Flux\\Components\\Button'))
        <flux:button
            wire:click="increment"
            aria-label="increase counter"
            @if($max !== null && $count >= $max) disabled @endif
            variant="primary"
        >
            +1
        </flux:button>
    @else
        <!-- 後備：原生按鈕 -->
        <button
            type="button"
            wire:click="increment"
            aria-label="increase counter"
            @if($max !== null && $count >= $max) disabled @endif
            class="px-3 py-2 rounded bg-blue-600 text-white disabled:bg-gray-400"
        >
            +1
        </button>
    @endif
</div>
```

### 在 Blade 中使用
```blade
<x-layout>
    @livewire('livewire.counter', ['initial' => 5, 'max' => 10])
</x-layout>
```

> 注意：實際 `@livewire()` 語法可能依專案的 Volt 預編譯輸出名稱而異；若專案已有 Volt 元件命名慣例，請依該慣例嵌入。若使用傳統 Livewire 類別元件，請改用對應的 `App\\Livewire\\Counter` 類名。

## 傳統 Livewire 類別元件（替代方案）
若不使用 Volt，則可採用類別元件：

- 類別：`app/Livewire/Counter.php`
- 檢視：`resources/views/livewire/counter.blade.php`

範例：
```php
<?php

namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
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
        $this->dispatch('counter-incremented', count: $this->count);
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

Blade：
```blade
<div class="inline-flex items-center gap-3">
    <span class="text-2xl font-medium" aria-live="polite">{{ $count }}</span>
    <button type="button" wire:click="increment" aria-label="increase counter" @disabled($max !== null && $count >= $max) class="px-3 py-2 rounded bg-blue-600 text-white disabled:bg-gray-400">+1</button>
</div>
```

## 可存取性（A11y）規範
- 計數文字區塊加上 `aria-live="polite"`，以便螢幕閱讀器在值更新時宣告。
- 按鈕需有描述性的 `aria-label`。
- 禁用狀態需以 `disabled` 屬性呈現，並有視覺樣式區分。

## 測試規格（Pest + Livewire）
- 檔案建議：`tests/Feature/Livewire/CounterTest.php`
- 測試案例：
  1. 初始值為預設 0：渲染後顯示 0。
  2. 呼叫 `increment` 一次，顯示 1；再呼叫一次，顯示 2。
  3. 設定 `initial = 5`，渲染後顯示 5。
  4. 設定 `max = 6`，在 `count == 6` 時再呼叫 `increment` 不再增加。
  5. 事件 `counter-incremented` 有在成功 +1 後觸發（可選測）。

範例（傳統 Livewire 範式）：
```php
<?php

declare(strict_types=1);

use App\Livewire\Counter;
use Livewire\Livewire;

it('increments', function () {
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->call('increment')
        ->assertSet('count', 2)
        ->assertSee('2');
});

it('respects initial and max', function () {
    Livewire::test(Counter::class, ['initial' => 5, 'max' => 6])
        ->assertSet('count', 5)
        ->call('increment')
        ->assertSet('count', 6)
        ->call('increment')
        ->assertSet('count', 6); // 不再增加
});
```

若使用 Volt，請參考 Volt 測試方式（`Livewire\Volt\Volt::test()`）。

## 整合建議
- 將元件置於常見版面 layout 中，如 `components.layouts.app`。
- 在需要的頁面插入元件，並視需求傳入 `initial` 與 `max`。
- 若需記錄使用者點擊次數，可在父層監聽 `counter-incremented` 事件並觸發後續行為（例如 API 紀錄）。

## 開發注意事項
- Livewire 3 事件請使用 `$this->dispatch()`（非舊版的 `emit`）。
- 需要即時更新時，輸入綁定使用 `wire:model.live`；本元件僅有按鈕行為，無輸入欄位。
- Tailwind v4 請使用 `@import "tailwindcss";` 的引入方式，避免 v3 的 `@tailwind` 寫法。

---

以上為「點擊後 +1」Livewire 元件的完整規格。
