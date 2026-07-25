# External Job Application Tracking API

This API allows students to track applications they make to external job sites (LinkedIn, Indeed, etc.) and manage their application status within the Career Hub platform.

## Overview

When jobs are imported from external APIs (JSearch, Adzuna), they are marked as `application_method = 'External'`. Students can:
1. Click "Apply" which opens the external job site
2. After applying externally, return to Career Hub and mark the application as tracked
3. Update application status as they receive feedback from employers
4. View all applications (both internal and external) in one place

---

## API Endpoints

### 1. Track External Application
**Endpoint:** `POST /api/track_external_application.php`

**Description:** Records that a student has applied to an external job posting.

**Authentication:** Required (Student only)

**Request Body:**
```json
{
  "jobId": 123,
  "csrf_token": "your_csrf_token",
  "coverLetter": "Optional: Copy of cover letter used",
  "notes": "Optional: Any notes about the application",
  "appliedDate": "2025-11-12 14:30:00" // Optional, defaults to now
}
```

**Success Response (200):**
```json
{
  "success": true,
  "application_id": 456,
  "message": "External application tracked successfully",
  "job_title": "Software Developer",
  "external_link": "https://linkedin.com/jobs/..."
}
```

**Error Responses:**
- `400` - Missing jobId or job is not external
- `401` - Unauthorized (not logged in or not a student)
- `403` - Invalid CSRF token
- `404` - Job not found
- `409` - Application already tracked for this job

---

### 2. Get Student Applications
**Endpoint:** `GET /api/student_applications.php`

**Description:** Retrieves all applications for the logged-in student, including both internal and external applications.

**Authentication:** Required (Student only)

**Success Response (200):**
```json
{
  "success": true,
  "total_applications": 15,
  "internal_applications": [
    {
      "id": 1,
      "status": "Reviewed",
      "applied_at": "2025-11-10 10:30:00",
      "updated_at": "2025-11-11 14:20:00",
      "is_external": false,
      "job": {
        "id": 5,
        "title": "Marketing Intern",
        "location": "Kampala, Uganda",
        "type": "Internship",
        "status": "Open",
        "external_link": null
      },
      "company": {
        "name": "Tech Startup Ltd",
        "logo": "logo.png",
        "industry": "Technology"
      },
      "cover_letter": "Dear Hiring Manager..."
    }
  ],
  "external_applications": [
    {
      "id": 2,
      "status": "Applied",
      "applied_at": "2025-11-11 16:45:00",
      "updated_at": "2025-11-11 16:45:00",
      "is_external": true,
      "job": {
        "id": 67,
        "title": "Software Developer",
        "location": "Remote",
        "type": "Full-time",
        "status": "Open",
        "external_link": "https://linkedin.com/jobs/view/12345"
      },
      "company": {
        "name": "Global Tech Corp",
        "logo": null,
        "industry": "Technology"
      },
      "cover_letter": "Application notes..."
    }
  ],
  "all_applications": [...],
  "statistics": {
    "total": 15,
    "internal": 8,
    "external": 7,
    "by_status": {
      "Applied": 10,
      "Reviewed": 3,
      "Interview": 1,
      "Hired": 0,
      "Rejected": 1
    }
  }
}
```

**Error Responses:**
- `401` - Unauthorized (not logged in or not a student)
- `500` - Database error

---

### 3. Update Application Status
**Endpoint:** `PUT/PATCH/POST /api/update_application_status.php`

**Description:** Updates the status of an application (especially useful for external applications where students receive feedback via email).

**Authentication:** Required (Student only)

**Request Body (JSON):**
```json
{
  "application_id": 456,
  "status": "Interview",
  "csrf_token": "your_csrf_token",
  "notes": "Interview scheduled for Nov 15 at 2pm"
}
```

**Valid Statuses:**
- `Applied` - Initial application submitted
- `Reviewed` - Application has been reviewed by employer
- `Interview` - Interview scheduled or completed
- `Hired` - Offer received/accepted
- `Rejected` - Application rejected

**Success Response (200):**
```json
{
  "success": true,
  "message": "Application status updated successfully",
  "application_id": 456,
  "job_title": "Software Developer",
  "old_status": "Applied",
  "new_status": "Interview",
  "is_external": true,
  "updated_at": "2025-11-12 10:30:00"
}
```

**Error Responses:**
- `400` - Missing application_id or invalid status
- `401` - Unauthorized (not logged in or not a student)
- `403` - Invalid CSRF token
- `404` - Application not found or doesn't belong to user
- `405` - Method not allowed

---

## Usage Flow

### For External Jobs:

1. **Student views external job:**
   ```javascript
   // Job has application_method = 'External'
   // Shows external_link button
   ```

2. **Student applies externally:**
   ```javascript
   // Opens external_link in new tab
   window.open(job.external_link, '_blank');
   ```

3. **Student returns and tracks application:**
   ```javascript
   fetch('/api/track_external_application.php', {
     method: 'POST',
     headers: { 'Content-Type': 'application/json' },
     body: JSON.stringify({
       jobId: 123,
       csrf_token: csrfToken,
       notes: 'Applied via LinkedIn, used updated resume'
     })
   });
   ```

4. **Student receives interview invitation via email:**
   ```javascript
   fetch('/api/update_application_status.php', {
     method: 'POST',
     headers: { 'Content-Type': 'application/json' },
     body: JSON.stringify({
       application_id: 456,
       status: 'Interview',
       csrf_token: csrfToken,
       notes: 'Interview scheduled for Nov 15 at 2pm via Zoom'
     })
   });
   ```

5. **View all applications:**
   ```javascript
   fetch('/api/student_applications.php')
     .then(res => res.json())
     .then(data => {
       console.log('Total applications:', data.total_applications);
       console.log('External apps:', data.external_applications);
       console.log('Statistics:', data.statistics);
     });
   ```

---

## Database Schema

Applications are stored in the `applications` table:
```sql
CREATE TABLE applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  status ENUM('Applied','Reviewed','Interview','Hired','Rejected') DEFAULT 'Applied',
  coverLetter TEXT,
  createdAt DATETIME NOT NULL,
  updatedAt DATETIME NOT NULL,
  studentId INT,
  jobId INT,
  FOREIGN KEY (studentId) REFERENCES students(id),
  FOREIGN KEY (jobId) REFERENCES jobs(id)
);
```

Jobs with `application_method = 'External'` are imported from external APIs.

---

## Benefits

✅ **Unified Dashboard** - Students see all applications in one place
✅ **Progress Tracking** - Update status as application progresses
✅ **Analytics** - Track success rate across internal vs external applications
✅ **Notes & History** - Keep detailed records of each application
✅ **No Data Loss** - Even external applications are tracked locally

---

## Security

- ✅ CSRF token validation on all write operations
- ✅ User ownership verification (students can only access their own applications)
- ✅ Role-based access control (only students can use these endpoints)
- ✅ SQL injection prevention via prepared statements
- ✅ Input validation and sanitization

---

## Future Enhancements

🔮 **Email Integration** - Parse application confirmation emails automatically
🔮 **Browser Extension** - Auto-detect when user applies on external sites
🔮 **OAuth Integration** - Connect LinkedIn/Indeed accounts for automatic tracking
🔮 **Reminders** - Notify students to follow up on applications
🔮 **Analytics Dashboard** - Visualize application success rates and timelines
