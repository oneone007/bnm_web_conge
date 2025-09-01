# BNM Mail Management System

This system provides a complete email management solution for the BNM inventory system with database-driven templates, contact management, and email logging.

## Features

- **Email Templates**: Create and manage reusable email templates with placeholders
- **Contact Management**: Maintain a database of email contacts organized by department
- **Email Logging**: Track all sent emails with status and error information
- **Admin Dashboard**: Web-based interface for managing all aspects of the mail system
- **API Integration**: RESTful API for sending emails from the inventory system

## Setup Instructions

### 1. Database Setup

1. Run the SQL script to create the required tables:
   ```bash
   mysql -u root -p bnm < /opt/lampp/htdocs/bnm_web/inventory/setup_mail_tables.sql
   ```

2. Or use the API endpoints to setup tables automatically:
   ```bash
   # Setup mail tables
   curl -X POST http://localhost:5003/mail/setup
   
   # Initialize default data
   curl -X POST http://localhost:5003/mail/init_data
   ```

### 2. Python Service

The mail functionality is integrated into the existing `inv.py` Flask service. Make sure the service is running:

```bash
cd /opt/lampp/htdocs/bnm_web/inventory
python inv.py
```

The service will run on `http://localhost:5003`

### 3. Admin Dashboard

Access the mail admin dashboard at:
```
http://localhost/bnm_web/adminmail.php
```

## Usage

### Admin Dashboard Features

#### 1. Dashboard
- View email statistics (templates, contacts, emails sent today, failed emails)
- Monitor recent email activity

#### 2. Email Templates
- Create new email templates with placeholders (e.g., `{inventory_details}`)
- Edit existing templates
- Preview templates before sending
- Manage SMTP settings per template

#### 3. Contacts Management
- Add/edit email contacts
- Organize contacts by department (Management, Operations, IT, Finance)
- Enable/disable contacts

#### 4. Send Email
- Select a template
- Choose recipients from contact list
- Provide replacement values for placeholders
- Send emails to multiple recipients

#### 5. Email Logs
- View all sent emails with status
- Filter by success/failure
- Search email history
- View email content

#### 6. Settings
- Setup database tables
- Initialize default data

### API Endpoints

#### Mail Configuration
- `GET /mail/configs` - Get all email templates
- `POST /mail/configs` - Create new template
- `PUT /mail/configs/<id>` - Update template

#### Contacts
- `GET /mail/contacts` - Get all contacts
- `POST /mail/contacts` - Create new contact

#### Sending Emails
- `POST /mail/send` - Send email using template
  ```json
  {
    "config_name": "inventory_created",
    "to_emails": ["user@example.com"],
    "replacements": {
      "inventory_details": "Inventory ID: 123..."
    },
    "inventory_id": 123
  }
  ```

#### Email Logs
- `GET /mail/logs` - Get email history

#### System
- `POST /mail/setup` - Create database tables
- `POST /mail/init_data` - Initialize default templates and contacts

### Integration with Inventory System

The inventory system now uses the mail management system:

1. **Inventory Creation**: Automatically sends emails to management when inventories are created
2. **Template-based**: Uses the "inventory_created" template
3. **Dynamic Recipients**: Pulls recipients from the contacts database
4. **Logging**: All emails are logged for tracking

### Email Templates

#### Default Templates

1. **inventory_created**: Sent when a new inventory is created
2. **inventory_saisie_notification**: Notification for operations team
3. **inventory_info_notification**: General info notification

#### Template Placeholders

Templates support placeholders that are replaced when sending:
- `{inventory_details}` - Detailed inventory information
- Custom placeholders can be added as needed

### Database Schema

#### email_configs
- `id`: Primary key
- `config_name`: Unique template identifier
- `subject`: Email subject
- `body`: Email body with placeholders
- `from_email`: Sender email
- `from_password`: SMTP password
- `smtp_server`: SMTP server
- `smtp_port`: SMTP port
- `is_active`: Enable/disable template

#### email_contacts
- `id`: Primary key
- `name`: Contact name
- `email`: Email address (unique)
- `department`: Department (Management, Operations, etc.)
- `position`: Job position
- `is_active`: Enable/disable contact

#### email_logs
- `id`: Primary key
- `config_name`: Template used
- `subject`: Actual subject sent
- `body`: Actual body sent
- `from_email`: Sender
- `to_email`: Recipient
- `sent_at`: Timestamp
- `status`: success/failed
- `error_message`: Error details if failed
- `related_inventory_id`: Link to inventory

## Troubleshooting

1. **Database Connection Issues**: Check MySQL connection settings in `get_localdb_connection()`
2. **SMTP Issues**: Verify SMTP settings in email templates
3. **Permission Issues**: Ensure the web server has access to the files
4. **API Not Working**: Check if the Python service is running on port 5003

## Security Notes

- Email passwords are stored in the database (consider encryption in production)
- API endpoints should be secured in production
- Consider rate limiting for email sending
- Validate all user inputs

## Future Enhancements

- Email template versioning
- Email scheduling
- Bulk email operations
- Email analytics and reporting
- Integration with external email services
- Template import/export functionality
