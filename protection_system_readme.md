# Page Protection System

## Overview
The `protection.php` file provides a comprehensive role-based access control system for your PHP application. It automatically checks user permissions based on their session role and the permissions defined in `permissions.json`.

## Features
- Automatic session management
- Role-based access control
- Custom page identifiers
- Access logging for security monitoring
- Flexible integration options
- Automatic file name mapping

## Basic Usage

### Method 1: Simple Include (Recommended)
```php
<?php include 'protection.php'; ?>
```
This will automatically:
- Start the session if needed
- Check if user is logged in
- Determine the page identifier from the filename
- Check permissions
- Redirect to 403.html if access is denied

### Method 2: Custom Page Identifier
```php
<?php 
$page_identifier = 'Product'; // Custom page identifier
include 'protection.php'; 
?>
```
Use this when the automatic filename mapping doesn't match your permission identifiers.

### Method 3: Manual Control
```php
<?php 
define('DISABLE_AUTO_PROTECTION', true); // Disable auto-protection
include 'protection.php';

// Manual permission check
if (!isPageAllowed('Custom_Page', $_SESSION['Role'], loadPermissions())) {
    header("Location: 403.html");
    exit();
}
?>
```

## File Name Mappings
The system automatically maps common file names to permission identifiers:

| File Name | Permission Identifier |
|-----------|----------------------|
| etatstock.php | Etatstock |
| product.php | Product |
| recap_achat.php | Recap_Achat |
| recap_vente.php | Recap_Vente |
| rotation.php | Rotation |
| simulation.php | simuler |
| bank.php | bank |
| charges_dashboard.php | charge |
| moneyv2.php | mony |
| Any file with 'inv' | inventory/inv |
| Files with 'inv' + 'saisie' | inventory/inv_saisie |

## Functions Available

### `protectPage()`
Main protection function that handles the complete access control flow.

### `isPageAllowed($page, $role, $permissions)`
Checks if a specific page is allowed for a given role.
- Returns `true` if access is allowed
- Returns `false` if access is denied

### `loadPermissions()`
Loads permissions from `permissions.json` file.
- Returns permissions array
- Falls back to default permissions if file is not found

### `checkUserLogin()`
Verifies if user is properly logged in with a valid session.

## Security Features

### Access Logging
Failed access attempts are logged to `access_denied.log` with:
- Timestamp
- Username
- User role
- Attempted page access

### Session Validation
- Checks for valid user session
- Verifies required session variables exist
- Redirects to 403.html for invalid sessions

## Permissions Structure
The system uses the permissions defined in `permissions.json`:

```json
{
    "Admin": "all",
    "Developer": "all",
    "DRH": "all",
    "Sup Achat": [
        "Annual_Recap_A",
        "Product",
        "Etatstock",
        // ... more pages
    ],
    // ... other roles
}
```

## Error Handling
- Invalid sessions → Redirect to 403.html
- Missing permissions file → Use default permissions
- Unknown roles → Access denied
- File access errors → Graceful fallback

## Integration Examples

### Protecting a Product Page
```php
<?php include 'protection.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
</head>
<body>
    <h1>Product Management</h1>
    <!-- Your page content here -->
</body>
</html>
```

### Conditional Content Based on Permissions
```php
<?php 
include 'protection.php';
$permissions = loadPermissions();
$userRole = $_SESSION['Role'];
?>

<!DOCTYPE html>
<html>
<body>
    <h1>Dashboard</h1>
    
    <?php if (isPageAllowed('Product', $userRole, $permissions)): ?>
        <a href="product.php">Product Management</a>
    <?php endif; ?>
    
    <?php if (isPageAllowed('bank', $userRole, $permissions)): ?>
        <a href="bank.php">Bank Management</a>
    <?php endif; ?>
</body>
</html>
```

## Troubleshooting

### Common Issues
1. **403 errors for valid users**: Check if page identifier matches permissions.json
2. **Not redirecting**: Ensure no output before `include 'protection.php'`
3. **Session issues**: Verify session_start() is called properly

### Debug Mode
To debug permission issues, you can temporarily add:
```php
<?php 
include 'protection.php';
echo "User Role: " . $_SESSION['Role'] . "<br>";
echo "Page Identifier: " . getPageIdentifier() . "<br>";
echo "Has Access: " . (isPageAllowed(getPageIdentifier(), $_SESSION['Role'], loadPermissions()) ? 'Yes' : 'No');
?>
```

## Security Best Practices
1. Always include `protection.php` at the very top of protected pages
2. Regularly review access logs for suspicious activity
3. Keep permissions.json updated with role changes
4. Use HTTPS for all authenticated pages
5. Implement proper session timeout mechanisms
