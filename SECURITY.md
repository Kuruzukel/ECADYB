# Security Guidelines

## 🔐 Protecting Your Credentials

### NEVER Commit Real Credentials

**CRITICAL:** Never commit files containing real credentials to Git/GitHub:

- ❌ **DO NOT** commit `.env` files
- ❌ **DO NOT** put real passwords in documentation
- ❌ **DO NOT** commit configuration files with credentials
- ✅ **DO** use placeholder values in documentation (e.g., `<USERNAME>`, `<PASSWORD>`)
- ✅ **DO** keep `.env` in `.gitignore`
- ✅ **DO** use environment variables in production

### Environment Variables Storage

**Local Development:**

- Store credentials in `.env` file (already in `.gitignore`)
- Use `.env.example` as a template (with placeholders only)

**Production (Railway):**

- Set environment variables in Railway dashboard under "Variables" tab
- Never hardcode credentials in code
- Railway encrypts environment variables at rest

### What to Do If Credentials Are Exposed

If you accidentally commit real credentials:

1. **Immediately rotate/change the credentials:**

   - MongoDB: Change database password in MongoDB Atlas
   - Bunny CDN: Regenerate access keys
   - Email: Change SMTP password or regenerate app-specific password

2. **Remove from Git history:**

   ```bash
   # Remove the file from Git history
   git filter-branch --force --index-filter \
     "git rm --cached --ignore-unmatch <FILE_WITH_CREDENTIALS>" \
     --prune-empty --tag-name-filter cat -- --all

   # Force push (be careful!)
   git push origin --force --all
   ```

3. **Use BFG Repo-Cleaner (easier alternative):**

   ```bash
   # Download BFG from https://rtyley.github.io/bfg-repo-cleaner/
   java -jar bfg.jar --delete-files .env
   git reflog expire --expire=now --all
   git gc --prune=now --aggressive
   git push origin --force --all
   ```

4. **Verify on GitHub:**
   - Check "Security" tab for any remaining alerts
   - Verify credentials are no longer in commit history

### GitHub Secret Scanning

GitHub automatically scans for exposed secrets. If you see alerts:

1. **Review the alert** - Determine if it's a real credential or placeholder
2. **Rotate credentials** - If real, change them immediately
3. **Remove from history** - Use methods above
4. **Mark as resolved** - After fixing, dismiss the alert on GitHub

### Safe Documentation Examples

**❌ BAD - Triggers Security Scanners:**

Even fake credentials can trigger scanners if they match patterns:
- Complete connection strings with `://` protocol
- Realistic-looking API keys
- Full credential examples in code blocks

**✅ GOOD - Use Broken Patterns:**

Break up the string so scanners don't recognize the pattern:
- Structure: `mongodb+srv://` + `[user]:[pass]` + `@[host]/[db]`
- Format: `protocol://[credentials]@[host]/[database]`
- Describe where to get it: "Copy from MongoDB Atlas Dashboard"

**✅ BEST - Reference External Sources:**

Instead of showing examples:
- "Get your connection string from MongoDB Atlas dashboard"
- "Copy the value from Railway Variables tab"
- "See the official documentation at [link]"

This way, no patterns are exposed that could trigger false positives!

### Best Practices

1. **Always use angle brackets `<>` or ALL_CAPS for placeholders**
2. **Add warnings like "Replace with your actual credentials"**
3. **Never use realistic-looking dummy data**
4. **Test documentation examples to ensure they won't be flagged**

### Additional Resources

- [GitHub Secret Scanning](https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning)
- [Railway Environment Variables](https://docs.railway.app/develop/variables)
- [MongoDB Security Best Practices](https://www.mongodb.com/docs/manual/security/)

---

## 📧 Reporting Security Issues

If you discover a security vulnerability, please email the maintainer directly. Do not open a public issue.
