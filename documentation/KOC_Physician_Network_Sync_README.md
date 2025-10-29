Here’s a complete **README.md** draft for your `koc-physician-network-sync` plugin:

---

```markdown
# KOC Physician Network Sync

**Version:** 1.0.0  
**Plugin Slug:** `koc-physician-network-sync`  
**Author:** Moxcar Communications  
**Primary Site:** [KOCOrtho.com](https://kocortho.com)  
**Consumer Sites:** [KOSCortho.com](https://koscortho.com), [UOSOrtho.com](https://uosortho.com)

---

## Overview

This plugin synchronizes physician profile data from the **source of truth** at **KOCOrtho.com** to multiple consumer WordPress sites (**KOSCortho** and **UOSOrtho**).  

It enables consistent, secure, and automated updates for all physician information while still allowing limited local edits via a configurable whitelist.

---

## Key Features

- 🔁 **Two-way architecture:** One-way sync from KOC → Consumer sites (read-only downstream).  
- 🔐 **Secure API:** WordPress REST API protected by Application Passwords and IP allowlists.  
- ⏱️ **Automated sync:** Full import + incremental updates on a configurable schedule (default 8 hours).  
- 🧠 **UUID-based identity:** Every physician post is assigned a globally unique ID for consistent mapping across sites.  
- ⚙️ **Field-level control:** Admin whitelist for fields allowed to remain locally editable.  
- 🧾 **Detailed logging:** Sync activity and errors stored locally for review.  
- 🚫 **No media syncing:** All images and links remain pointed at KOCOrtho (headshots remain manual).  

---

## Plugin Architecture

### 1. Source Site (KOCOrtho.com)

**Responsibilities:**
- Hosts the canonical physician database.  
- Exposes REST API endpoints for full and incremental sync.  
- Maintains UUIDs for all physician posts.  
- Uses Application Passwords and IP allowlists for secure access.  

**API Endpoints:**
```

GET /wp-json/koc-sync/v1/physicians?updated_since=ISO8601&page&per_page
GET /wp-json/koc-sync/v1/physicians/{uuid}

````

**Response Schema:**
```json
{
  "schema_version": "1",
  "data": [
    {
      "uuid": "xxxx-xxxx-xxxx",
      "post_type": "physician",
      "slug": "john-doe",
      "post_title": "Dr. John Doe",
      "post_content": "<p>Full HTML content...</p>",
      "updated_at": "2025-10-28T15:03:00Z",
      "acf": {
        "last_name": "Doe",
        "title": "MD",
        "specialty": ["Sports Medicine", "Shoulder"],
        "...": "..."
      }
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 50,
    "total": 92,
    "next": "https://kocortho.com/wp-json/koc-sync/v1/physicians?page=2"
  }
}
````

---

### 2. Consumer Sites (KOSCortho / UOSOrtho)

**Responsibilities:**

* Pull updates from KOCOrtho.
* Write synced data to the existing `physician` custom post type.
* Retain whitelist-controlled local fields.
* Manage cron-based and manual syncs.

**Storage:**

* `_koc_global_uuid` (string)
* `_koc_source_updated_at` (ISO8601)
* `_koc_last_synced_at` (ISO8601)

**Sync Flow:**

1. **Full Import:** Fetch all physicians → match by UUID → fallback to slug+full name+post_type → log unmatched.
2. **Incremental Sync:** Fetch by `updated_since` every 8 hours.
3. **Conflict Handling:** KOC wins for all non-whitelisted fields.
4. **Deletions:** Downstream posts are unpublished or marked inactive when removed upstream.

---

## Installation

### On KOCOrtho.com (Source)

1. Upload the plugin to `/wp-content/plugins/koc-physician-network-sync/`.
2. Activate the plugin.
3. Go to **Settings → Physician Sync (Source)**.
4. Click **Generate UUIDs** to assign IDs to all physician posts.
5. Create an **Application Password** for the sync user.
6. Configure IP allowlist on your hosting or security layer.
7. Test API endpoint via browser or curl:

   ```
   curl -u user:app-password https://kocortho.com/wp-json/koc-sync/v1/physicians
   ```

### On KOSCortho.com / UOSOrtho.com (Consumers)

1. Upload and activate the same plugin.
2. Go to **Settings → Physician Sync (Consumer)**.
3. Enter:

   * Base URL: `https://kocortho.com`
   * Username & App Password
4. Click **Test Connection**.
5. Optionally adjust sync frequency (default 8 hours).
6. Run **Full Import** for the first time.

---

## Field Reference

Each physician post includes these ACF-style fields:

| #  | Field Label                  | Field Name                     | Type                      |
| -- | ---------------------------- | ------------------------------ | ------------------------- |
| 1  | Personal Information         | `personal_information`         | WYSIWYG                   |
| 2  | Last Name                    | `last_name`                    | Text                      |
| 3  | Title                        | `title`                        | Text                      |
| 4  | Social Climb ID              | `social_climb_id`              | Number                    |
| 5  | Specialty                    | `specialty`                    | Checkbox                  |
| 6  | Secondary Specialty          | `secondary_specialty`          | Checkbox                  |
| 7  | Education                    | `education`                    | WYSIWYG                   |
| 8  | Internship                   | `intership`                    | WYSIWYG                   |
| 9  | Residency                    | `residency`                    | WYSIWYG                   |
| 10 | Fellowship                   | `felloswhip`                   | WYSIWYG                   |
| 11 | Began Practice at KOC        | `began_practice_at_koc`        | Text                      |
| 12 | Board Certification          | `board_certification`          | WYSIWYG                   |
| 13 | Professional Distinctions    | `professional_distinctions`    | WYSIWYG                   |
| 14 | Orthopaedic Specialty        | `orthopaedic_specialty`        | WYSIWYG                   |
| 15 | Office Info                  | `office_info`                  | WYSIWYG                   |
| 16 | Office Location              | `office_location`              | WYSIWYG                   |
| 17 | Appointments Number          | `appointments_number`          | Text                      |
| 18 | Administrative Assistant     | `administrative_assistant`     | Text                      |
| 19 | Nurse                        | `nurse`                        | Text                      |
| 20 | Professional Interests       | `professional_interests`       | Text                      |
| 21 | Teaching Appointments        | `teaching_appointments`        | Text                      |
| 22 | Medical Associations         | `medical_associations`         | Text                      |
| 23 | Educational Links            | `educational_links`            | Text                      |
| 24 | Patient Forms                | `patient_forms`                | Text                      |
| 25 | Procedures Performed         | `procedures_performed`         | WYSIWYG                   |
| 26 | Conditions Treated           | `conditions_treated`           | WYSIWYG                   |
| 27 | Schedule an Appointment Link | `schedule_an_appointment_link` | Text                      |
| 28 | Appointment Button Text      | `appointment_button_text`      | Text                      |
| 29 | Affiliation                  | `affiliation`                  | Radio                     |
| 30 | Affiliation Link             | `affiliation_link`             | Text                      |
| 31 | Performs Surgery             | `Surgery`                      | Radio                     |
| 32 | API Name                     | `api_name`                     | Text                      |
| 33 | Professional Headshot        | `professional_headshot`        | Text *(ignored for sync)* |
| 34 | UOS Embed                    | `uos_embed`                    | WYSIWYG                   |

---

## Admin Tools

### On KOCOrtho

* **UUID Generator** (one-time)
* **API Status** (view total physicians, last export)

### On Consumer Sites

* **Connection Settings**
* **Test Connection**
* **Full Import / Run Now**
* **Whitelist Configuration** (select fields that can remain local)
* **Sync Logs** (view recent imports, errors, unmatched posts)

---

## Logging

Logs are stored in a custom table:
`wp_koc_sync_logs`

**Fields:**

* `timestamp`
* `uuid`
* `post_id`
* `action` (import/update/delete)
* `level` (INFO/WARN/ERROR)
* `message`
* `context` (JSON)

Retention: 60–90 days (configurable)

---

## Security Notes

* Always use HTTPS for all API requests.
* Limit access to IPs of consumer sites only.
* Use dedicated least-privilege users with Application Passwords.
* No PHI/PII is transmitted—only public physician data.

---

## Future Enhancements

* Diff preview before import
* Retry queue for transient errors
* Push notification webhook from KOC → consumers
* Optional API pagination optimizations
* Sync of new metadata groups (e.g., insurance, procedures)

---

## License

© 2025 Moxcar Communications.
All rights reserved.

---

## Maintainer

**Author:** Gino Peterson (@gpeterson)
**Organization:** [Moxcar Communications](https://moxcar.com)

```

---

Would you like me to add a **developer section** (detailing class structure, filters, and extensibility hooks for future developers)? That would make the README suitable for internal handoff or version control.
```
