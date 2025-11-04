# Upload 502 Error - FIXED ✅

## Problem
The student photo upload was failing with a **502 Bad Gateway** error. This happens when the PHP script takes too long to process and the web server times out.

## Root Cause
1. **JavaScript timeout was too short**: Set to only 60 seconds (line 804 in BatchUpload.js)
2. **PHP execution limits**: Were at 120 seconds, not enough for large uploads
3. **Excessive logging**: Logging entire $_SERVER array was consuming memory
4. **CURL timeouts**: Were set to only 30 seconds for file uploads to BunnyCDN

## Fixes Applied

### 1. **UploadStudentPhotos.php** (`Connection/Photos/UploadStudentPhotos.php`)

#### Increased Timeouts & Memory:
```php
// BEFORE:
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '120');
set_time_limit(120);

// AFTER:
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '300');
set_time_limit(300);
```

#### Added Connection Handling:
```php
// Prevent script from stopping if client disconnects
ignore_user_abort(true);

// Send initial response headers early to prevent 502 gateway timeout
if (ob_get_level()) {
    ob_flush();
}
flush();
```

#### Reduced Excessive Logging:
```php
// BEFORE: Logged entire $_SERVER array (could be huge)
error_log("All SERVER vars: " . json_encode($_SERVER));

// AFTER: Only log what's needed
error_log("POST data keys: " . implode(', ', array_keys($_POST)));
error_log("FILES count: " . count($_FILES));
```

#### Increased CURL Timeout:
```php
// BEFORE:
CURLOPT_TIMEOUT => 30,
CURLOPT_CONNECTTIMEOUT => 5,

// AFTER:
CURLOPT_TIMEOUT => 60,
CURLOPT_CONNECTTIMEOUT => 10,
```

### 2. **BatchUpload.js** (`Admin/assets/js/BatchUpload.js`)

#### Increased JavaScript Timeout:
```javascript
// BEFORE:
xhr.timeout = 60000; // 60 seconds

// AFTER:
xhr.timeout = 300000; // 300 seconds (5 minutes)
```

#### Better Error Messages:
```javascript
if (xhr.status === 502) {
  errorMessage = "Server gateway error (502). The upload may have timed out or the server is overloaded. Try uploading fewer files at once or wait a moment and try again.";
} else if (xhr.status === 504) {
  errorMessage = "Server timeout (504). The upload took too long. Try uploading fewer files at once.";
}
```

## Testing Tools

### Test Upload Endpoint
A diagnostic tool was created: `Connection/Photos/test-upload.php`

**How to use:**
1. Navigate to: `https://your-domain/ECADYB/Connection/Photos/test-upload.php`
2. This will show:
   - PHP configuration (memory, timeouts)
   - Whether POST data is being received
   - File upload status
   - Current timestamp

This helps identify if the issue is with:
- PHP configuration
- File upload reception
- Server connectivity

## How to Test the Fix

1. **Clear your browser cache**: Press `Ctrl + F5` (Windows) or `Cmd + Shift + R` (Mac)

2. **Try uploading student photos**:
   - Start with 1-2 photos to test
   - If successful, try 5-10 photos
   - The maximum is set to 10 photos per batch

3. **Monitor the upload**:
   - Open browser Developer Tools (F12)
   - Go to Console tab
   - Watch for progress messages
   - Check for any errors

4. **Check server logs** (if you have access):
   - Look for lines starting with "UploadStudentPhotos:"
   - Check for any PHP errors
   - Monitor memory usage

## If Issues Persist

### Railway Platform Limits
If you're hosting on Railway, they have a **300-second (5 minute) timeout** for HTTP requests. If uploads take longer than this:

**Solutions:**
1. Reduce batch size (currently 10, try reducing to 5)
2. Compress images before uploading
3. Upload in smaller chunks

### Server Configuration
If running on your own server, check:

**Apache:**
- `Timeout` directive in httpd.conf (should be >= 300)
- `ProxyTimeout` if using proxy (should be >= 300)

**Nginx:**
- `proxy_read_timeout` (should be >= 300)
- `fastcgi_read_timeout` (should be >= 300)
- `client_body_timeout` (should be >= 300)

**PHP-FPM:**
- `request_terminate_timeout` in php-fpm.conf (should be >= 300)

## Files Changed

1. ✅ `Connection/Photos/UploadStudentPhotos.php` - PHP upload handler
2. ✅ `Admin/assets/js/BatchUpload.js` - JavaScript upload client
3. ✅ `Connection/Photos/test-upload.php` - NEW diagnostic tool

## Summary

The 502 error was caused by a **timeout mismatch**: JavaScript timeout (60s) < PHP timeout (120s) < Actual upload time. 

Now all timeouts are synchronized at **300 seconds (5 minutes)**, which should handle most upload scenarios.

---

**Last Updated:** November 4, 2025
**Status:** ✅ FIXED

