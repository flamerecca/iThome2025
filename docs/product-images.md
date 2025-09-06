# 商品圖片（Product Image）API 規格

本文件定義「商品圖片」之 CRUD API 規格，並提供商品下圖片的巢狀端點，供前後端與測試參考。若未特別說明，所有時間皆為 ISO 8601 字串（例：`2025-09-06T09:30:00Z`）。

- 資源名稱：ProductImage（商品圖片）
- 路徑前綴：`/api/product-images`（主資源）
- 巢狀前綴：`/api/products/{product}/images`
- 資料格式：`application/json`
- 語言：繁體中文

## 資源結構（Schema）

ProductImage 物件欄位：
- id：integer，主鍵
- product_id：integer，所屬商品 ID
- url：string，必填，圖片完整 URL（或 storage 路徑，視實作而定）
- alt：string|null，選填，替代文字（SEO／無障礙）
- is_primary：boolean，是否為主圖（每個商品建議僅一張主圖）
- sort_order：integer，排序用（數字越小越前面），預設 0
- is_active：boolean，是否啟用／顯示，預設 true
- created_at：datetime，建立時間
- updated_at：datetime，最後更新時間

### 範例
```json
{
  "id": 1,
  "product_id": 10,
  "url": "https://cdn.example.com/images/p10-1.jpg",
  "alt": "iPhone 42 正面照",
  "is_primary": true,
  "sort_order": 0,
  "is_active": true,
  "created_at": "2025-09-06T09:30:00Z",
  "updated_at": "2025-09-06T09:30:00Z"
}
```

## 錯誤格式
Laravel 預設驗證錯誤格式，例如：
```json
{
  "message": "The url field is required.",
  "errors": {
    "url": [
      "The url field is required."
    ]
  }
}
```

## 權限/認證
- 若專案已有 API 認證（如 Sanctum/Passport），請依現有慣例保護路由。
- 本規格僅描述功能，不限定認證實作方式。

---

## 1) 列表（Index）
GET /api/product-images

用途：分頁取得商品圖片清單。

查詢參數（Query Params）：
- page：integer，頁碼，預設 1
- per_page：integer，每頁筆數，預設 15（建議限制最大 100）
- product_id：integer，過濾特定商品之圖片
- is_active：boolean，過濾啟用狀態
- sort：string，排序欄位，預設 `-id`；可用：`id`, `product_id`, `sort_order`, `created_at`, `updated_at`（前綴 `-` 表降冪）

回應（200 OK）：Laravel 分頁結構
```json
{
  "data": [
    {
      "id": 1,
      "product_id": 10,
      "url": "https://cdn.example.com/images/p10-1.jpg",
      "alt": "iPhone 42 正面照",
      "is_primary": true,
      "sort_order": 0,
      "is_active": true,
      "created_at": "2025-09-06T09:30:00Z",
      "updated_at": "2025-09-06T09:30:00Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/product-images?page=1",
    "last": "http://localhost/api/product-images?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [],
    "path": "http://localhost/api/product-images",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

---

## 2) 建立（Store）
POST /api/product-images

用途：建立一筆商品圖片。

Request Body（JSON）：
- product_id：integer，必填，存在於 products.id
- url：string，必填，應為合法 URL 或可用的儲存路徑
- alt：string，選填，最長 255 字
- is_primary：boolean，選填，預設 false
- sort_order：integer，選填，預設 0
- is_active：boolean，選填，預設 true

驗證規則（建議）：
- product_id：required|integer|exists:products,id
- url：required|string|max:2048
- alt：nullable|string|max:255
- is_primary：boolean
- sort_order：nullable|integer|min:0
- is_active：boolean

商業規則（建議）：
- 若 is_primary=true，應將同商品其他圖片的 is_primary 設為 false（確保唯一主圖）。

成功回應（201 Created）：
```json
{
  "data": {
    "id": 20,
    "product_id": 10,
    "url": "https://cdn.example.com/images/p10-2.jpg",
    "alt": "iPhone 42 背面照",
    "is_primary": false,
    "sort_order": 1,
    "is_active": true,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T09:30:00Z"
  }
}
```

失敗回應（422 Unprocessable Entity）：見「錯誤格式」。

---

## 3) 顯示（Show）
GET /api/product-images/{id}

用途：取得單一商品圖片。

路徑參數：
- id：integer，必填

成功回應（200 OK）：
```json
{
  "data": {
    "id": 1,
    "product_id": 10,
    "url": "https://cdn.example.com/images/p10-1.jpg",
    "alt": "iPhone 42 正面照",
    "is_primary": true,
    "sort_order": 0,
    "is_active": true,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T09:30:00Z"
  }
}
```

找不到（404 Not Found）：
```json
{ "message": "Resource not found." }
```

---

## 4) 更新（Update）
PUT /api/product-images/{id}
PATCH /api/product-images/{id}

用途：更新商品圖片。

路徑參數：
- id：integer，必填

Request Body（JSON）：
- product_id：integer，選填（通常不更新）
- url：string，選填
- alt：string，選填
- is_primary：boolean，選填
- sort_order：integer，選填
- is_active：boolean，選填

驗證規則（建議）：
- product_id：sometimes|integer|exists:products,id
- url：sometimes|string|max:2048
- alt：sometimes|nullable|string|max:255
- is_primary：sometimes|boolean
- sort_order：sometimes|nullable|integer|min:0
- is_active：sometimes|boolean

商業規則（建議）：
- 若更新 is_primary=true，應將同商品其他圖片的 is_primary 設為 false。

成功回應（200 OK）：
```json
{
  "data": {
    "id": 1,
    "product_id": 10,
    "url": "https://cdn.example.com/images/p10-1.jpg",
    "alt": "iPhone 42 正面照",
    "is_primary": true,
    "sort_order": 0,
    "is_active": false,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T10:00:00Z"
  }
}
```

---

## 5) 刪除（Destroy）
DELETE /api/product-images/{id}

用途：刪除商品圖片。

路徑參數：
- id：integer，必填

成功回應（204 No Content）：無內容

找不到（404 Not Found）：
```json
{ "message": "Resource not found." }
```

---

## 6) 商品下的巢狀端點（Nested under Product）

### 6.1 列出某商品的圖片（分頁）
GET /api/products/{product}/images

- 路徑參數：product（integer）
- 查詢參數：page、per_page、is_active、sort（可用：`id`, `sort_order`, `created_at`；`-` 表降冪）
- 回應：ProductImage 分頁集合

### 6.2 在某商品下新增圖片
POST /api/products/{product}/images

- 路徑參數：product（integer）
- Request Body：同「建立（Store）」但可省略 product_id（由路徑給定）
- 成功回應：201 Created，回傳建立的圖片

### 6.3 設為主圖（選用行為端點）
PUT /api/product-images/{id}/make-primary

- 用途：將指定圖片設為主圖；同商品其他圖片 is_primary 設為 false。
- 回應：200 OK，回傳更新後資源

### 6.4 批次更新排序（選用）
PATCH /api/products/{product}/images/sort

- Request Body：
  - items：array，元素為 { id: integer, sort_order: integer }
- 用途：一次更新多張圖片的 sort_order。
- 回應：200 OK，回傳該商品所有圖片（或簡要結果）

---

## 接受標準（Acceptance Criteria）
- 提供 `GET /api/product-images` 分頁列表，支援 product_id、is_active 過濾與 sort。
- 提供 `POST /api/product-images` 建立資料，驗證 product_id 與 url；is_primary=true 時確保該商品僅一張主圖。
- 提供 `GET /api/product-images/{id}` 顯示單筆資料。
- 提供 `PUT/PATCH /api/product-images/{id}` 更新資料；設定 is_primary=true 時維持唯一主圖。
- 提供 `DELETE /api/product-images/{id}` 刪除資料。
- 提供巢狀端點：`GET /api/products/{product}/images`、`POST /api/products/{product}/images`；並提供 `PUT /api/product-images/{id}/make-primary` 與（選用）`PATCH /api/products/{product}/images/sort`。
- 回應格式與 Laravel API Resource、分頁結構一致。
- 錯誤時回傳標準驗證錯誤格式（422）。
