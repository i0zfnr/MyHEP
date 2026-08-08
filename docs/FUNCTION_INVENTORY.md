# StudentEdge Implemented Function Inventory

Last updated: 2026-08-08  
Audited release: `5d5ec84`

## Count and Method

StudentEdge currently exposes **142 implemented named application functions**.

| Area | Function count |
| --- | ---: |
| Public and shared | 23 |
| Student | 21 |
| Admin | 98 |
| **Total** | **142** |

The count is generated from `php artisan route:list --json` and includes each named application endpoint once. It excludes Laravel vendor routes such as `/up` and framework storage routes. Internal PHP helper methods, scheduled commands, UI-only buttons, and multiple HTTP verbs on one named endpoint are not counted separately. This makes the number reproducible and avoids inflating the total.

## Public and Shared Functions (23)

### Home and system overview (2)

- Open the public home page.
- Load live public system overview metrics.

### Authentication and password recovery (9)

- Open and submit login.
- Log out.
- Open and submit forgot-password request.
- Open and submit reset-code verification.
- Open and submit the new password.

### Preferences, roles, and active sessions (8)

- Change locale and theme.
- Open and update account settings.
- Switch linked role mode.
- Revoke one other authenticated device.
- Revoke all other authenticated devices.

### Notifications and support (4)

- Load the authenticated notification feed.
- Subscribe and unsubscribe a browser from Web Push.
- Open and submit a problem report.

## Student Functions (21)

| Module | Count | Implemented functions |
| --- | ---: | --- |
| Dashboard | 1 | View student dashboard and operational summary. |
| Profile | 3 | View profile, update profile, change password. |
| Scholarships | 4 | View scholarships, view announcements, open declaration, submit declaration and offer letter. |
| Discipline | 5 | View offenses, print an offense, submit fine receipt, view rules, view discipline announcements. |
| Vehicle stickers | 2 | View applications and submit an application with required evidence. |
| Movement | 3 | View history, open QR scanner, submit checkout or return. |
| Documents | 2 | View owned private documents and download an owned document. |
| AI Helper | 1 | Open the student entry point, which currently returns the documented unavailable state. |
| **Total** | **21** | |

## Admin Functions (98)

| Module | Count | Implemented functions |
| --- | ---: | --- |
| Dashboard and monitoring | 2 | Dashboard and live System Admin monitoring. |
| Admin users and lecturer access | 7 | List, create, store, edit, update, reset password, delete; lecturer page access is configured with the account. |
| Student management | 11 | List, AJAX lookup, view sensitive profile, create, store, edit, update, delete, import, export, reset password. |
| Scholarships | 10 | CRUD and export plus B40 TVET view, import, and export. |
| Scholarship announcements | 7 | List, create, store, edit, update, delete, export. |
| Student scholarship status | 2 | Review declarations and download linked offer letters. |
| Offenses | 10 | List/filter, AJAX-backed registration, store, export, print, PDF, edit, update, mark paid, delete. |
| Rules | 7 | List, create, store, edit, update, delete, export. |
| Discipline announcements | 7 | List, create, store, edit, update, delete, export. |
| Fine applications | 3 | List, export, approve/reject decision. |
| Vehicle stickers | 4 | List, export, approve/reject decision, delete. |
| Movement | 10 | Records, export, outside list, violations, QR control/update/display/status, settings/update. |
| Student documents | 3 | Review list, authenticated download, approve/reject review. |
| Feature and session controls | 3 | View/update feature flags and update configured session lifetime. |
| Maintenance and push | 4 | View controls, change maintenance/cache state, test current-admin push, broadcast scheduled maintenance notice. |
| Monthly report | 1 | Generate the selected monthly operational analytics report. |
| AI Helper | 2 | Open helper and submit a read-only-context question. |
| Bug reports | 3 | List, update status, delete. |
| Admin profile | 2 | View profile and upload/crop profile photo. |
| **Total** | **98** | |

## Role Coverage

- **Student:** the 21 student functions plus authenticated shared settings, sessions, notifications, and push subscription.
- **Lecturer:** limited AJAX student lookup and individually enabled offense registration/list pages.
- **Guard:** movement operations and limited non-sensitive student list/search.
- **Scholarship Admin:** scholarship workflows and permitted student lookup/list access.
- **Discipline Admin:** offenses, fines, rules, announcements, stickers, movement, and permitted student data actions.
- **Head of Student Affairs:** scholarship, discipline, movement, student management/export, and document review, but no system administration.
- **System Admin:** all registered abilities, admin management, feature/session controls, monitoring, maintenance, and push testing/broadcast.

## Recount Command

Run this after adding or removing routes:

```powershell
$routes = (php artisan route:list --json | ConvertFrom-Json) |
    Where-Object { $_.path -notlike 'vendor/*' -and $_.name }
$routes.Count
$routes | Group-Object {
    if ($_.uri -like 'admin/*') { 'Admin' }
    elseif ($_.uri -like 'student/*') { 'Student' }
    else { 'Public/Shared' }
} | Sort-Object Name | Select-Object Name, Count
```

The inventory must be updated whenever the audited total changes.
