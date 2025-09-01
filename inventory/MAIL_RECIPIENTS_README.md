# Mail Recipients Management System

This system allows you to manage email recipients for different mail routes through a JSON configuration file instead of hardcoding them in the source code.

## How it works

### 1. JSON Configuration File
- **Location**: `/opt/lampp/htdocs/bnm_web/inventory/mail_recipients.json`
- **Format**: JSON object with route names as keys and arrays of email addresses as values

### 2. Supported Routes
- **send_saisie_mail**: Recipients for inventory operations notifications
- **send_info_mail**: Recipients for information notifications  
- **inventory_save**: Recipients for inventory creation notifications

### 3. Management Interface
- **Settings Page**: `http://localhost/bnm_web/mail/settings.php`
- **Recipients Management**: `http://localhost/bnm_web/mail/recipients.php`

## Features

### Web Interface
- Add/remove recipients for each route
- Real-time updates (saves immediately to JSON)
- Email validation
- Export/import configuration
- Reset to defaults option

### API Endpoints
- `GET /mail/recipients` - Get all recipients configuration
- `GET /mail/recipients/<route_name>` - Get recipients for specific route
- `POST /mail/recipients/<route_name>` - Save recipients for specific route
- `POST /mail/recipients/all` - Save complete configuration

### Fallback System
Each route has fallback recipients in case the JSON file is missing or empty:

**send_saisie_mail fallback:**
- guend.hamza@bnmparapharm.com
- seifeddine.nemdili@bnmparapharm.com
- belhanachi.abdenour@bnmparapharm.com

**send_info_mail fallback:**
- maamri.yasser@bnmparapharm.com
- mahroug.nazim@bnmparapharm.com
- benmalek.abderrahmane@bnmparapharm.com

**inventory_save fallback:**
- benmalek.abderrahmane@bnmparapharm.com
- mahroug.nazim@bnmparapharm.com

## Usage

### Through Web Interface
1. Go to Mail Settings: `http://localhost/bnm_web/mail/settings.php`
2. Click "Manage Recipients"
3. Add/remove recipients for each route
4. Changes are saved automatically

### Direct JSON Editing
You can also edit the JSON file directly:
```json
{
    "send_saisie_mail": [
        "email1@bnmparapharm.com",
        "email2@bnmparapharm.com"
    ],
    "send_info_mail": [
        "email3@bnmparapharm.com",
        "email4@bnmparapharm.com"
    ],
    "inventory_save": [
        "email5@bnmparapharm.com",
        "email6@bnmparapharm.com"
    ]
}
```

## Benefits

1. **No Code Changes**: Add/remove recipients without touching source code
2. **Real-time Updates**: Changes take effect immediately
3. **Backup & Restore**: Easy to export/import configurations
4. **Web Interface**: User-friendly management through browser
5. **Fallback Safety**: Always has default recipients if configuration fails
6. **API Access**: Programmatic access for automation

## Troubleshooting

- If recipients aren't updating, check that the inventory service is running on port 5003
- If JSON file is corrupted, use "Reset to Defaults" in the web interface
- Check browser console for JavaScript errors if web interface isn't working
- Verify file permissions on the mail_recipients.json file
