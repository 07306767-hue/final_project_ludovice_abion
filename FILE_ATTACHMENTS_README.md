# File Attachment Feature for LMS

This feature allows teachers and students to attach files when creating/submitting activities.

## Setup Instructions

### 1. Database Migration
Run the SQL migration script to add file attachment columns:

```sql
-- Run this in your MySQL database
ALTER TABLE `activities` ADD COLUMN `attachment_path` VARCHAR(500) NULL AFTER `due_date`;
ALTER TABLE `submissions` ADD COLUMN `attachment_path` VARCHAR(500) NULL AFTER `answer`;
```

Or use the provided migration file:
- Execute `add_file_attachments.sql` in your MySQL database

### 2. File Permissions
Ensure the web server has write permissions to the uploads directories:
- `uploads/activities/`
- `uploads/submissions/`

### 3. Features Added

#### For Teachers:
- **Create Activity**: Can attach files when creating new activities
- **Edit Activity**: Can update or replace attachments when editing activities
- **View Activity**: Can see download links for attached files

#### For Students:
- **Submit Activity**: Can attach files when submitting activities
- **Edit Submission**: Can update or replace attachments when editing submissions
- **View Activity**: Can see their own submission attachments

#### File Restrictions:
- **Maximum Size**: 10MB per file
- **Allowed Types**: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG, ZIP, RAR
- **Storage**: Files are stored in organized directories with unique filenames

### 4. Security Features
- File type validation
- File size limits
- Unique filename generation to prevent conflicts
- .htaccess protection for uploaded files
- Access control maintained (only authorized users can access files)

### 5. Files Modified
- `public/pages/create_activity.php` - Added file upload field
- `public/pages/submit_activity.php` - Added file upload field
- `public/pages/edit_activity.php` - Added file upload fields for both teachers and students
- `public/pages/activity.php` - Added attachment display
- `public/pages/view_submissions.php` - Added attachment display for submissions
- `public/api/store_activity.php` - Added file upload handling
- `public/api/store_submission.php` - Added file upload handling
- `public/api/update_activity.php` - Added file upload handling
- `public/api/update_submission.php` - Added file upload handling

### 6. Testing
1. Create an activity with an attachment as a teacher
2. Submit an activity with an attachment as a student
3. Verify files can be downloaded
4. Edit activities/submissions and update attachments
5. Check file size and type restrictions work properly

## Troubleshooting

### Files not uploading:
- Check file permissions on uploads directories
- Verify PHP upload settings in php.ini
- Check file size limits

### Files not displaying:
- Ensure .htaccess file is in place
- Check file paths in database are correct
- Verify web server can serve the file types

### Database errors:
- Ensure migration script was run successfully
- Check column names match the code expectations