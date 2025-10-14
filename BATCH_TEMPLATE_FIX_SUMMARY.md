# Batch Template Upload Fix Summary

## Problem
When uploading CSV files and photos to Batch Template 2 or 3, the data was always going to Batch Template 1 instead of the selected template.

## Root Causes

### 1. Hardcoded Template Value
**File:** `Admin/Components/BatchUpload.php` (Line 308)
- The hidden input field had `value="1"` hardcoded
- **Fix:** Changed to `value=""` to allow JavaScript to set it dynamically

### 2. Incorrect Template Number Handling
**File:** `Admin/Components/BatchUpload.php` (Function `getSelectedTemplateDatabase`)
- When template number was sent as "2" or "3", the function didn't recognize it as numeric
- It would default back to "BatchTemplate1"
- **Fix:** Added logic to handle both numeric values (e.g., "2") and full names (e.g., "Batch Template 2")

```php
// Before: Would fail for numeric inputs like "2"
if (strpos($dbName, 'BatchTemplate') !== 0) {
    $dbName = 'BatchTemplate1';
}

// After: Handles both numeric and text formats
if (is_numeric($selectedTemplate)) {
    $templateNumber = intval($selectedTemplate);
    if ($templateNumber >= 1 && $templateNumber <= 3) {
        $dbName = 'BatchTemplate' . $templateNumber;
    } else {
        $dbName = 'BatchTemplate1';
    }
} else {
    // Handle "Batch Template 1" format
    $dbName = str_replace(' ', '', $selectedTemplate);
    if (strpos($dbName, 'BatchTemplate') !== 0) {
        $dbName = 'BatchTemplate1';
    }
}
```

### 3. Missing Default Value
**File:** `Admin/assets/js/BatchUpload.js` (Lines 237-255)
- If no template was selected in localStorage, the hidden field would remain empty
- **Fix:** Added default value of "1" if no template is selected

## MongoDB Collections Structure

After these fixes, the system will correctly create/use these databases and collections:

### Batch Template 1 (`BatchTemplate1` database):
- **`top_management_message`** - CSV data for top management messages (name, position, message, academicyear)
- **`top_management_photos`** - Photo URLs and metadata for top management
- **`StudentPhotos`** - Student photo URLs and metadata (stores regular, FILIPINIANA, TOGA, UNIFORM photos)
- **`YearbookCovers`** - Cover images for yearbook
- **Department Collections:** `bsme`, `bsmt`, `bscje`, `bstm`, `btvted`, `beced`, `bsn`, `bsis`, `bsma`, `bse` - Student info CSV data by department

### Batch Template 2 (`BatchTemplate2` database):
- Same collections as Batch Template 1

### Batch Template 3 (`BatchTemplate3` database):
- Same collections as Batch Template 1

## Additional Improvements

### 1. Enhanced Logging
Added debug logging to track template selection:
- Logs which template number is selected when uploading
- Logs the database name being used for uploads
- Helps troubleshoot any future issues

### 2. MongoDB Connection Consistency
Updated to check both `MONGODB_URI` and `MONGO_URL` environment variables for better compatibility:
```php
$mongoUrl = getenv('MONGODB_URI') ?: getenv('MONGO_URL') ?: 'fallback_url';
```

## Testing Instructions

### To verify the fix works:

1. **Select Batch Template 2** in BatchTemplates.php
   - Click on "Batch Template 2" section header
   - Confirm the selection in the modal

2. **Upload Top Management CSV**
   - Go to BatchUpload.php
   - Upload a CSV file with columns: `name`, `position`, `message`, `academicyear`
   - Check browser console for log: "BatchUpload: Using template number from localStorage: 2"

3. **Upload Top Management Photos**
   - Upload photos named exactly as the names in the CSV
   - Check for success notification
   - Verify in MongoDB that `BatchTemplate2` database has `top_management_message` and `top_management_photos` collections

4. **Upload Student Info CSV**
   - Upload CSV with student data
   - Should create department collections (bsme, bsmt, etc.) in `BatchTemplate2` database

5. **Upload Student Photos**
   - Upload student photos named with student IDs
   - Should create `StudentPhotos` collection in `BatchTemplate2` database

6. **Verify in MongoDB**
   - Connect to your MongoDB instance
   - Check that `BatchTemplate2` database exists
   - Verify collections are populated with your uploaded data

## Files Modified

1. ✅ `Admin/Components/BatchUpload.php`
   - Fixed `getSelectedTemplateDatabase()` function
   - Removed hardcoded template value
   - Added logging for debugging

2. ✅ `Admin/assets/js/BatchUpload.js`
   - Added default value fallback
   - Added console logging for debugging

## Files Already Correct

The following files were already handling templates correctly:
- ✅ `Connection/Photos/UploadStudentPhotos.php`
- ✅ `Connection/Photos/UPloadTopManagementPhotos.php`
- ✅ `Admin/Components/StudentList.php`
- ✅ `Connection/Cover/FetchCovers.php`
- ✅ `Connection/Photos/FetchStudentData.php`

## Expected Behavior After Fix

- When you select **Batch Template 1**: All uploads go to `BatchTemplate1` database
- When you select **Batch Template 2**: All uploads go to `BatchTemplate2` database
- When you select **Batch Template 3**: All uploads go to `BatchTemplate3` database

Each template maintains completely separate data, allowing you to manage multiple yearbook batches independently.

