# BRAC Tools — Incident Report / Register Module (PHP + MySQL)

This module implements:
- Incident Register (Liquor Control Act 1988 (WA) s116A aligned fields)
- Refusal of Entry/Service Register
- Soft lock + audited edits (before/after snapshot + diff + edit reason)
- Photo/PDF attachments
- Link incidents/refusals to staff (users)
- One-click "Police notified" flag
- Monthly compliance report (incidents + refusals)

## Install

1) Copy files into your BRAC Tools web root, e.g. `/incidents/`

2) Create DB tables:
- Run `sql/schema.sql` in your MySQL database.

3) Configure DB connection:
- Copy `app/config.sample.php` to `app/config.php` and set credentials.
- Ensure `uploads/incidents/` exists and is writable by PHP.

4) Hook into existing BRAC Tools login
- This module expects `$_SESSION['user'] = ['id'=>..., 'display_name'=>..., 'role'=>...]`.
- If you already store different session keys, edit `app/auth.php` accordingly.

## Permissions
- staff: can create incidents/refusals
- manager/admin: can view registers, edit incidents (audited), upload files, run reports

## Notes
- Remove the `dev_user` fallback in `app/auth.php` for production.


## BRAC Tools integration notes
- Your current login writes `$_SESSION['staff'] = ['id','name','role']` in `auth_login.php`. This module reads that automatically.
- This module includes its own `incidents/app/config.php` and will read DB constants from your existing root `config.php` (DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASS). Do **not** overwrite your root config.
