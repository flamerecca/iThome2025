# 商品類別（Category）API 規格

本文件定義「商品類別」之 CRUD API 規格，並提供依類別選取（列出）商品的端點，供前後端與測試參考。若未特別說明，所有時間皆為 ISO 8601 字串（例：`2025-09-06T09:30:00Z`）。

- 資源名稱：Category（商品類別）
- 路徑前綴：`/api/categories`
- 資料格式：`application/json`
- 語言：繁體中文

## 資源結構（Schema）

Category 物件欄位：
- id：integer，主鍵
- name：string，必填，類別名稱（建議唯一）
- slug：string|null，選填，預設可由 name 自動產生（kebab-case）
- description：string|null，選填，說明文字
- is_active：boolean，是否啟用，預設 true
- created_at：datetime，建立時間
- updated_at：datetime，最後更新時間

### 範例
```json
{
  "id": 1,
  "name": "手機",
  "slug": "shou-ji",
  "description": "智慧型手機",
  "is_active": true,
  "created_at": "2025-09-06T09:30:00Z",
  "updated_at": "2025-09-06T09:30:00Z"
}
```

## 錯誤格式
Laravel 預設驗證錯誤格式，例如：
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": [
      "The name field is required."
    ]
  }
}
```

## 權限/認證
- 若專案已有 API 認證（如 Sanctum/Passport），請依現有慣例保護路由。
- 本規格僅描述功能，不限定認證實作方式。

---

## 1) 列表（Index）
GET /api/categories

用途：分頁取得類別清單。

查詢參數（Query Params）：
- page：integer，頁碼，預設 1
- per_page：integer，每頁筆數，預設 15（建議限制最大 100）
- search：string，關鍵字（模糊比對 name、slug）
- sort：string，排序欄位，預設 `-id`（負號代表 DESC）；可用：`id`, `name`, `created_at`, `updated_at`，前綴 `-` 表降冪
- is_active：boolean，可過濾啟用狀態

回應（200 OK）：Laravel 分頁結構
```json
{
  "data": [
    {
      "id": 1,
      "name": "手機",
      "slug": "shou-ji",
      "description": "智慧型手機",
      "is_active": true,
      "created_at": "2025-09-06T09:30:00Z",
      "updated_at": "2025-09-06T09:30:00Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/categories?page=1",
    "last": "http://localhost/api/categories?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [],
    "path": "http://localhost/api/categories",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

---

## 2) 建立（Store）
POST /api/categories

用途：建立一筆類別。

Request Body（JSON）：
- name：string，必填，1~50 字（建議唯一）
- slug：string，選填，1~50 字；若未提供，後端從 name 產生
- description：string，選填，最長 255 字
- is_active：boolean，選填，預設 true

驗證規則（建議）：
- name：required|string|min:1|max:50|unique:categories,name
- slug：nullable|string|min:1|max:50|unique:categories,slug
- description：nullable|string|max:255
- is_active：boolean

成功回應（201 Created）：
```json
{
  "data": {
    "id": 10,
    "name": "手機",
    "slug": "shou-ji",
    "description": "智慧型手機",
    "is_active": true,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T09:30:00Z"
  }
}
```

失敗回應（422 Unprocessable Entity）：見「錯誤格式」。

---

## 3) 顯示（Show）
GET /api/categories/{id}

用途：取得單一類別。

路徑參數：
- id：integer，必填

成功回應（200 OK）：
```json
{
  "data": {
    "id": 1,
    "name": "手機",
    "slug": "shou-ji",
    "description": "智慧型手機",
    "is_active": true,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T09:30:00Z"
  }
}
```

找不到（404 Not Found）：
```json
{
  "message": "Resource not found."
}
```

---

## 4) 更新（Update）
PUT /api/categories/{id}
PATCH /api/categories/{id}

用途：更新類別。

路徑參數：
- id：integer，必填

Request Body（JSON）：
- name：string，選填，1~50 字
- slug：string，選填，1~50 字
- description：string，選填，最長 255 字
- is_active：boolean，選填

驗證規則（建議）：
- name：sometimes|required|string|min:1|max:50|unique:categories,name,{id}
- slug：nullable|string|min:1|max:50|unique:categories,slug,{id}
- description：nullable|string|max:255
- is_active：boolean

成功回應（200 OK）：
```json
{
  "data": {
    "id": 1,
    "name": "智慧型手機",
    "slug": "zhi-hui-xing-shou-ji",
    "description": "說明",
    "is_active": false,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T10:00:00Z"
  }
}
```

---

## 5) 刪除（Destroy）
DELETE /api/categories/{id}

用途：刪除類別。

路徑參數：
- id：integer，必填

成功回應（204 No Content）：無內容

找不到（404 Not Found）：
```json
{
  "message": "Resource not found."
}
```

---

## 6) 依類別選取商品（正式）
本系統採用「類別（categories）」與「商品（products）」之 1 對多或多對多關聯（依實作而定）。提供以下端點以便依類別列出商品：

### 6.1 列出某類別的商品
GET /api/categories/{category}/products

- 路徑參數：category（integer）
- 查詢參數：
  - page、per_page：分頁
  - search：依商品名稱模糊查詢（可選）
  - sort：支援 `id`, `name`, `price`, `created_at`；可用 `-` 前綴為降冪（可選）
  - is_active：boolean，過濾商品上架狀態（可選）
- 回應：Product 的分頁集合。示例單筆欄位：id, name, description, price, stock, is_active, created_at, updated_at

備註：
- 若類別不存在，回應 404。
- 刪除類別時，建議不自動刪除商品；僅處理關聯（依最終資料模型決定）。

---

## 接受標準（Acceptance Criteria）
- 提供 `GET /api/categories` 分頁列表，支援搜尋、排序與 is_active 過濾。
- 提供 `POST /api/categories` 建立資料，驗證 name 唯一；未提供 slug 時自動產生。
- 提供 `GET /api/categories/{id}` 顯示單筆資料。
- 提供 `PUT/PATCH /api/categories/{id}` 更新資料，維持唯一性驗證。
- 提供 `DELETE /api/categories/{id}` 刪除資料。
- 提供 `GET /api/categories/{category}/products` 依類別列出商品，支援分頁、搜尋、排序與 is_active 過濾。
- 回應格式與 Laravel API Resource、分頁結構一致。
- 錯誤時回傳標準驗證錯誤格式（422）。
