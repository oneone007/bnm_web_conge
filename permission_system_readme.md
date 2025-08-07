# Permission System Documentation

This document explains how to implement the permission check system in your pages.

## Overview

The permission system checks if a user has access to a specific page based on their role and the permissions defined in `permissions.json`. If a user doesn't have permission, they are redirected to an access denied page.

## Implementation Options

There are three different ways to implement the permission check in your pages:

### Option 1: Direct include with page name (Recommended)

```php
<?php
// At the top of your page
require_once 'permission_guard.php';

// Rest of your page code...
?>
```

This method automatically uses the current PHP file name as the page identifier for permission checks.

### Option 2: Manual check with custom page name

```php
<?php
require_once 'check_permission.php';
verify_permission('custom_page_name', 'access_denied.html');

// Rest of your page code...
?>
```

Use this when the page identifier in permissions.json is different from the actual PHP file name.

### Option 3: Auto verification with constant

```php
<?php
define('VERIFY_PERMISSION', true);
require_once 'check_permission.php';

// Rest of your page code...
?>
```

This is useful when you want to enable/disable permission checks conditionally.

## Permission Configuration

Permissions are managed in the `permissions.json` file. Each role can have either:
- `"all"` access (full access to all pages)
- An array of specific page names that the role can access

Example:
```json
{
    "Admin": "all",
    "Developer": "all",
    "Editor": ["page1", "page2", "dashboard"],
    "Viewer": ["dashboard", "reports"]
}
```

## Functions Available

The permission system provides several utility functions:

- `check_page_permission($page)`: Returns true/false if user has permission
- `verify_permission($page, $redirectTo)`: Checks permission and redirects if not allowed
- `get_current_page()`: Gets current page name (without .php extension)

## Example

See `permission_example.php` for a complete working example.
