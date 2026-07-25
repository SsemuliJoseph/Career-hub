# Career Hub API Documentation

## Overview
This document provides comprehensive documentation for the Career Hub REST API and WebSocket integration.

## Authentication
All API endpoints (except public ones) require authentication via API token.

### API Token
Include your API token in requests using one of these methods:
- Query parameter: `?api_token=YOUR_TOKEN`
- HTTP Header: `X-API-TOKEN: YOUR_TOKEN`

### Default API Tokens
Configure your tokens in the respective API files:
- `YOUR_SECRET_API_TOKEN`
- `EXTERNAL_APP_TOKEN`

---

## REST API Endpoints

### 1. Export Jobs
**Endpoint:** `GET /api/v1/export_jobs.php`

**Description:** Export job listings in various formats for external applications.

**Parameters:**
- `api_token` (required): Authentication token
- `format` (optional): Response format - `json`, `csv`, or `xml` (default: `json`)
- `limit` (optional): Number of records (default: 100)
- `offset` (optional): Starting position (default: 0)
- `type` (optional): Job type filter - `full-time`, `part-time`, `internship`
- `company` (optional): Filter by company name

**Response Example (JSON):**
```json
{
  "success": true,
  "count": 25,
  "data": [
    {
      "id": 1,
      "title": "Software Engineer",
      "company": "Tech Corp",
      "description": "Full stack developer position...",
      "location": "Remote",
      "type": "full-time",
      "created_at": "2025-01-15 10:30:00"
    }
  ],
  "meta": {
    "limit": 100,
    "offset": 0,
    "timestamp": "2025-01-15 12:00:00"
  }
}
```

**Example Usage:**
```bash
# JSON format
curl "http://yoursite.com/api/v1/export_jobs.php?api_token=YOUR_TOKEN"

# CSV format
curl "http://yoursite.com/api/v1/export_jobs.php?api_token=YOUR_TOKEN&format=csv" -o jobs.csv

# Filter by type
curl "http://yoursite.com/api/v1/export_jobs.php?api_token=YOUR_TOKEN&type=internship"
```

---

### 2. Export Applications
**Endpoint:** `GET /api/v1/export_applications.php`

**Description:** Export job application data.

**Parameters:**
- `api_token` (required): Authentication token
- `format` (optional): Response format (default: `json`)
- `user_id` (optional): Filter by user ID
- `job_id` (optional): Filter by job ID
- `status` (optional): Filter by status - `pending`, `reviewed`, `accepted`, `rejected`

**Response Example:**
```json
{
  "success": true,
  "count": 15,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "job_id": 10,
      "status": "pending",
      "created_at": "2025-01-10 14:20:00"
    }
  ],
  "meta": {
    "timestamp": "2025-01-15 12:00:00",
    "filters": {
      "user_id": null,
      "job_id": null,
      "status": "pending"
    }
  }
}
```

---

### 3. Import Jobs
**Endpoint:** `POST /api/v1/import_jobs.php`

**Description:** Import job listings from external applications.

**Headers:**
- `Content-Type: application/json`
- `X-API-TOKEN: YOUR_TOKEN`

**Request Body (Single Job):**
```json
{
  "title": "Frontend Developer",
  "company": "StartupXYZ",
  "description": "React developer needed...",
  "location": "New York, NY",
  "type": "full-time",
  "salary_min": 80000,
  "salary_max": 120000,
  "url": "https://example.com/apply",
  "source": "external_api"
}
```

**Request Body (Multiple Jobs):**
```json
{
  "jobs": [
    {
      "title": "Backend Engineer",
      "company": "Company A",
      "description": "Node.js developer..."
    },
    {
      "title": "Data Scientist",
      "company": "Company B",
      "description": "ML engineer..."
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "imported": 2,
  "failed": 0,
  "errors": [],
  "timestamp": "2025-01-15 12:00:00"
}
```

**Example Usage:**
```bash
curl -X POST http://yoursite.com/api/v1/import_jobs.php \
  -H "Content-Type: application/json" \
  -H "X-API-TOKEN: YOUR_TOKEN" \
  -d '{"title":"Software Engineer","company":"Tech Inc","description":"Great opportunity..."}'
```

---

### 4. Statistics
**Endpoint:** `GET /api/v1/stats.php`

**Description:** Get platform statistics for data processing and analytics.

**Parameters:**
- `api_token` (required): Authentication token

**Response:**
```json
{
  "success": true,
  "data": {
    "jobs": {
      "total": 150,
      "full_time": 80,
      "internships": 45,
      "part_time": 25,
      "companies": 75
    },
    "applications": {
      "total": 500,
      "pending": 200,
      "reviewed": 150,
      "accepted": 100,
      "rejected": 50
    },
    "users": {
      "students": 300,
      "employers": 75,
      "total": 375
    },
    "timestamp": "2025-01-15 12:00:00"
  }
}
```

---

### 5. Fetch External Jobs
**Endpoint:** `GET /api/v1/fetch_external_jobs.php`

**Description:** Fetch jobs from external APIs (Adzuna, JSearch) and optionally import them.

**Authentication:** Requires admin session login.

**Parameters:**
- `query` (optional): Search query (default: "software developer")
- `source` (optional): API source - `adzuna` or `jsearch` (default: `adzuna`)
- `import` (optional): Import jobs to database - `true` or `false`

**Response:**
```json
{
  "success": true,
  "source": "adzuna",
  "query": "software developer",
  "count": 20,
  "imported": 20,
  "jobs": [...],
  "timestamp": "2025-01-15 12:00:00"
}
```

---

### 6. Send Notification
**Endpoint:** `POST /api/v1/notify.php`

**Description:** Queue a WebSocket notification.

**Authentication:** Requires user session.

**Request Body:**
```json
{
  "type": "job_notification",
  "userId": 5,
  "data": {
    "jobId": 10,
    "title": "New Job Posted",
    "company": "Tech Corp"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Notification queued",
  "notification": {...}
}
```

---

## WebSocket Integration

### Connection
**WebSocket URL:** `ws://localhost:8080`

### Client Initialization
```javascript
// Include the WebSocket client script
<script src="/js/websocket-client.js"></script>

// The client automatically connects on page load
// Access via global variable: wsClient
```

### Message Types

#### 1. Register User
```javascript
wsClient.register(userId);
```

#### 2. Job Notification
**Received from server:**
```json
{
  "type": "job_notification",
  "jobId": 10,
  "title": "Software Engineer",
  "company": "Tech Corp",
  "timestamp": "2025-01-15 12:00:00"
}
```

#### 3. Application Update
**Received from server:**
```json
{
  "type": "application_update",
  "applicationId": 5,
  "status": "accepted",
  "message": "Congratulations!",
  "timestamp": "2025-01-15 12:00:00"
}
```

### Custom Event Listeners
```javascript
// Listen for job notifications
wsClient.on('jobNotification', function(data) {
  console.log('New job:', data);
  // Update UI
});

// Listen for application updates
wsClient.on('applicationUpdate', function(data) {
  console.log('Application status:', data);
  // Update UI
});

// Listen for connection status
wsClient.on('connected', function() {
  console.log('WebSocket connected');
});

wsClient.on('disconnected', function() {
  console.log('WebSocket disconnected');
});
```

---

## External API Configuration

### Adzuna API
1. Sign up at: https://developer.adzuna.com/
2. Get your App ID and App Key
3. Update in `classes/ExternalAPIService.php`:
```php
$this->adzunaAppId = 'YOUR_APP_ID';
$this->adzunaAppKey = 'YOUR_APP_KEY';
```

### JSearch API (RapidAPI)
1. Sign up at: https://rapidapi.com/
2. Subscribe to JSearch API
3. Get your RapidAPI Key
4. Update in `classes/ExternalAPIService.php`

---

## Setup Instructions

### 1. Install Dependencies
No external PHP dependencies required. Uses native PHP with PDO and cURL.

### 2. Configure Database
The OOP classes use the existing database configuration.

### 3. Set API Tokens
Update API tokens in:
- `/api/v1/export_jobs.php`
- `/api/v1/export_applications.php`
- `/api/v1/import_jobs.php`
- `/api/v1/stats.php`

### 4. Configure External APIs
Update credentials in `/classes/ExternalAPIService.php`

### 5. Start WebSocket Server
```bash
php websocket_server.php
```

Keep this running in the background or use a process manager like `supervisor`.

### 6. Include WebSocket Client
Add to your HTML pages:
```html
<script src="/js/websocket-client.js"></script>
```

---

## Testing Examples

### Test Export Jobs API
```bash
curl "http://localhost/career_hub/api/v1/export_jobs.php?api_token=YOUR_SECRET_API_TOKEN&limit=10"
```

### Test Import Jobs API
```bash
curl -X POST http://localhost/career_hub/api/v1/import_jobs.php \
  -H "Content-Type: application/json" \
  -H "X-API-TOKEN: YOUR_SECRET_API_TOKEN" \
  -d '{
    "jobs": [
      {
        "title": "Test Job",
        "company": "Test Company",
        "description": "Test description",
        "location": "Remote",
        "type": "full-time"
      }
    ]
  }'
```

### Test Statistics API
```bash
curl "http://localhost/career_hub/api/v1/stats.php?api_token=YOUR_SECRET_API_TOKEN"
```

---

## Error Handling

### HTTP Status Codes
- `200` - Success
- `400` - Bad Request (invalid data)
- `401` - Unauthorized (missing/invalid token)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

### Error Response Format
```json
{
  "error": "Error message here",
  "message": "Detailed error description"
}
```

---

## Rate Limiting
Currently no rate limiting is implemented. Consider adding rate limiting for production use.

## Security Best Practices
1. Use HTTPS in production
2. Store API tokens securely (environment variables)
3. Implement rate limiting
4. Validate and sanitize all inputs
5. Use prepared statements (already implemented)
6. Keep external API keys secret

---

## Support
For issues or questions, contact the development team.
