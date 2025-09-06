# 商品（Product）API 規格

本文件定義「商品」之 CRUD API 規格，供前後端與測試參考。若未特別說明，所有時間皆為 ISO 8601 字串（例：`2025-09-06T09:30:00Z`）。

- 資源名稱：Product（商品）
- 路徑前綴：`/api/products`
- 資料格式：`application/json`
- 語言：繁體中文

## 資源結構（Schema）

Product 物件欄位：
- id：integer，主鍵
- name：string，必填，商品名稱
- description：string|null，選填，說明文字
- price：number（decimal），必填，價格，需 >= 0
- stock：integer，選填，庫存量，預設 0，需 >= 0
- is_active：boolean，選填，是否上架，預設 true
- created_at：datetime，建立時間
- updated_at：datetime，最後更新時間

### 範例
```json
{
  "id": 1,
  "name": "iPhone 42",
  "description": "The future phone",
  "price": 1999.99,
  "stock": 5,
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
GET /api/products

用途：分頁取得商品清單。

查詢參數（Query Params）：
- page：integer，頁碼，預設 1
- per_page：integer，每頁筆數，預設 15（建議限制最大 100）

回應（200 OK）：Laravel 分頁結構
```json
{
  "data": [
    {
      "id": 1,
      "name": "iPhone 42",
      "description": "The future phone",
      "price": 1999.99,
      "stock": 5,
      "is_active": true,
      "created_at": "2025-09-06T09:30:00Z",
      "updated_at": "2025-09-06T09:30:00Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/products?page=1",
    "last": "http://localhost/api/products?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [],
    "path": "http://localhost/api/products",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

---

## 2) 建立（Store）
POST /api/products

用途：建立一筆商品。

Request Body（JSON）：
- name：string，必填，1~255 字
- description：string，選填
- price：number，必填，需 >= 0
- stock：integer，選填，需 >= 0，未提供時預設 0
- is_active：boolean，選填，未提供時預設 true

驗證規則（目前實作）：
- name：required|string|max:255
- description：nullable|string
- price：required|numeric|min:0
- stock：nullable|integer|min:0
- is_active：nullable|boolean

成功回應（201 Created）：
```json
{
  "data": {
    "id": 10,
    "name": "iPhone 42",
    "description": "The future phone",
    "price": 1999.99,
    "stock": 5,
    "is_active": true,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T09:30:00Z"
  }
}
```

失敗回應（422 Unprocessable Entity）：見「錯誤格式」。

---

## 3) 顯示（Show）
GET /api/products/{id}

用途：取得單一商品。

路徑參數：
- id：integer，必填

成功回應（200 OK）：
```json
{
  "data": {
    "id": 1,
    "name": "iPhone 42",
    "description": "The future phone",
    "price": 1999.99,
    "stock": 5,
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
PUT /api/products/{id}
PATCH /api/products/{id}

用途：更新商品。

路徑參數：
- id：integer，必填

Request Body（JSON）：
- name：string，選填，1~255 字
- description：string，選填
- price：number，選填，需 >= 0（PUT 時必填 / PATCH 可選填）
- stock：integer，選填，需 >= 0
- is_active：boolean，選填

驗證規則（目前實作）：
- name：sometimes|required|string|max:255
- description：sometimes|nullable|string
- price：sometimes|required|numeric|min:0
- stock：sometimes|nullable|integer|min:0
- is_active：sometimes|nullable|boolean

成功回應（200 OK）：
```json
{
  "data": {
    "id": 1,
    "name": "Updated",
    "description": "The future phone",
    "price": 10,
    "stock": 5,
    "is_active": true,
    "created_at": "2025-09-06T09:30:00Z",
    "updated_at": "2025-09-06T10:00:00Z"
  }
}
```

---

## 5) 刪除（Destroy）
DELETE /api/products/{id}

用途：刪除商品。

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

## 接受標準（Acceptance Criteria）
- 提供 `GET /api/products` 分頁列表，支援 `page`、`per_page`。 
- 提供 `POST /api/products` 建立資料，驗證 name 與 price，未提供 stock 時預設 0，未提供 is_active 時預設 true。
- 提供 `GET /api/products/{id}` 顯示單筆資料。
- 提供 `PUT/PATCH /api/products/{id}` 更新資料，驗證規則同上（PATCH 使用 sometimes）。
- 提供 `DELETE /api/products/{id}` 刪除資料。
- 回應格式與 Laravel API Resource、分頁結構一致。
- 錯誤時回傳標準驗證錯誤格式（422）。
