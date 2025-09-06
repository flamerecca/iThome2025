# 商品標籤（Tag）API 規格

本文件定義「商品標籤」之 CRUD API 規格，供前後端與測試參考。若未特別說明，所有時間皆為 ISO 8601 字串（例：`2025-09-06T09:30:00Z`）。

- 資源名稱：Tag（標籤）
- 路徑前綴：`/api/tags`
- 資料格式：`application/json`
- 語言：繁體中文

## 資源結構（Schema）

Tag 物件欄位：
- id：integer，主鍵
- name：string，必填，標籤名稱（唯一）
- slug：string，選填，預設可由 name 自動產生（kebab-case）
- description：string|null，選填，說明文字
- is_active：boolean，是否啟用，預設 true
- created_at：datetime，建立時間
- updated_at：datetime，最後更新時間

### 範例
```json
{
  "id": 1,
  "name": "限時優惠",
  "slug": "xian-shi-you-hui",
  "description": "限時活動使用",
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
GET /api/tags

用途：分頁取得標籤清單。

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
      "name": "限時優惠",
      "slug": "xian-shi-you-hui",
      "description": "限時活動使用",
      "is_active": true,
      "created_at": "2025-09-06T09:30:00Z",
      "updated_at": "2025-09-06T09:30:00Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/tags?page=1",
    "last": "http://localhost/api/tags?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [],
    "path": "http://localhost/api/tags",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

---

## 2) 建立（Store）
POST /api/tags

用途：建立一筆標籤。

Request Body（JSON）：
- name：string，必填，唯一，1~50 字
- slug：string，選填，1~50 字；若未提供，後端從 name 產生
- description：string，選填，最長 255 字
- is_active：boolean，選填，預設 true

驗證規則（建議）：
- name：required|string|min:1|max:50|unique:tags,name
- slug：nullable|string|min:1|max:50|unique:tags,slug
- description：nullable|string|max:255
- is_active：boolean

成功回應（201 Created）：
```json
{
  "data": {
    "id": 10,
    "name": "限時優惠",
    "slug": "xian-shi-you-hui",
    "description": "限時活動使用",
    "is_active": true,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T09:30:00Z"
  }
}
```

失敗回應（422 Unprocessable Entity）：見「錯誤格式」。

---

## 3) 顯示（Show）
GET /api/tags/{id}

用途：取得單一標籤。

路徑參數：
- id：integer，必填

成功回應（200 OK）：
```json
{
  "data": {
    "id": 1,
    "name": "限時優惠",
    "slug": "xian-shi-you-hui",
    "description": "限時活動使用",
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
PUT /api/tags/{id}
PATCH /api/tags/{id}

用途：更新標籤。

路徑參數：
- id：integer，必填

Request Body（JSON）：
- name：string，選填，唯一，1~50 字
- slug：string，選填，1~50 字
- description：string，選填，最長 255 字
- is_active：boolean，選填

驗證規則（建議）：
- name：sometimes|required|string|min:1|max:50|unique:tags,name,{id}
- slug：nullable|string|min:1|max:50|unique:tags,slug,{id}
- description：nullable|string|max:255
- is_active：boolean

成功回應（200 OK）：
```json
{
  "data": {
    "id": 1,
    "name": "改名後",
    "slug": "gai-ming-hou",
    "description": "備註",
    "is_active": false,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T10:00:00Z"
  }
}
```

---

## 5) 刪除（Destroy）
DELETE /api/tags/{id}

用途：刪除標籤。

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

## 6) 與商品多對多關聯（正式）
本系統採用「商品（products）」與「標籤（tags）」之多對多關聯。提供以下端點：

### 6.1 列出某商品的標籤
GET /api/products/{product}/tags

- 路徑參數：product（integer）
- 查詢參數：同 tags index 的搜尋、排序、分頁（可選）
- 回應：Tag 的分頁集合（同上方列表結構）

### 6.2 同步某商品的標籤集合（覆寫）
PUT /api/products/{product}/tags

用途：以給定的 tag_ids 覆寫商品的所有標籤（sync）。

- Request Body：
  - tag_ids：integer[]，必填，存在於 tags.id
- 成功回應：200 OK，回傳同步後的標籤清單（分頁或純陣列，建議純陣列）
- 驗證錯誤：422

### 6.3 附加單一標籤到商品
POST /api/products/{product}/tags/{tag}

- 路徑參數：product（integer）、tag（integer）
- 成功回應：204 No Content
- 例外：已附加則 204，重複附加不報錯（冪等）

### 6.4 自商品移除單一標籤
DELETE /api/products/{product}/tags/{tag}

- 路徑參數：product（integer）、tag（integer）
- 成功回應：204 No Content
- 例外：若原本不存在關聯，仍回 204（冪等）

### 6.5 列出使用某標籤的商品
GET /api/tags/{tag}/products

- 路徑參數：tag（integer）
- 查詢參數：
  - page、per_page：分頁
  - search：依商品名稱模糊查詢
  - sort：支援 id、name、price、created_at 等
- 回應：Product 的分頁集合。示例單筆欄位：id, name, description, price, stock, is_active, created_at, updated_at

關聯刪除策略（建議）：
- 刪除標籤時，不自動刪除商品；僅刪除 pivot 關聯。
- 刪除商品時，不自動刪除標籤；僅刪除 pivot 關聯。

---

## 接受標準（Acceptance Criteria）
- 提供 `GET /api/tags` 分頁列表，支援搜尋、排序與 is_active 過濾。
- 提供 `POST /api/tags` 建立資料，驗證 name 唯一；未提供 slug 時自動產生。
- 提供 `GET /api/tags/{id}` 顯示單筆資料。
- 提供 `PUT/PATCH /api/tags/{id}` 更新資料，維持唯一性驗證。
- 提供 `DELETE /api/tags/{id}` 刪除資料。
- 回應格式與 Laravel API Resource、分頁結構一致。
- 錯誤時回傳標準驗證錯誤格式（422）。
- 商品與標籤為多對多：提供 `/api/products/{product}/tags` 列表/同步、`/api/products/{product}/tags/{tag}` 附加/移除、`/api/tags/{tag}/products` 列表。
