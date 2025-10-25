# SendGrid Setup for Railway Email Sending

Railway blocks outbound SMTP ports (25, 465, 587), so we need to use SendGrid's API instead.

## Step 1: Create SendGrid Account

1. Go to https://signup.sendgrid.com/
2. Sign up for a free account (100 emails/day free)
3. Verify your email address

## Step 2: Create API Key

1. Log in to SendGrid dashboard
2. Go to **Settings** → **API Keys**
3. Click **Create API Key**
4. Name it: `ECADYB-RAILWAY`
5. Select **Full Access** (or at minimum: Mail Send permissions)
6. Click **Create & View**
7. **COPY THE API KEY** (you won't see it again!)

## Step 3: Verify Sender Email

1. Go to **Settings** → **Sender Authentication**
2. Click **Verify a Single Sender**
3. Fill in the form:
   - From Name: `Exact Colleges of Asia - Graduation Gallery`
   - From Email Address: `admain.ecadyb@gmail.com`
   - Reply To: `admain.ecadyb@gmail.com`
   - Company Address: Your school address
4. Click **Create**
5. Check your email (`admain.ecadyb@gmail.com`) and click the verification link

## Step 4: Add API Key to Railway

1. Go to Railway dashboard: https://railway.app/
2. Select your project
3. Click on your service
4. Go to **Variables** tab
5. Add new variable:
   - Name: `SENDGRID_API_KEY`
   - Value: (paste the API key you copied)
6. Click **Add**

Railway will automatically redeploy.

## Step 5: Update Your Code

Replace the SendOTP.php endpoint with SendOTPSendGrid.php:

In your frontend JavaScript file (`ForgotPassword.js`), change:
```javascript
// OLD
fetch('/Connection/Student/SendOTP.php', ...)

// NEW
fetch('/Connection/Student/SendOTPSendGrid.php', ...)
```

## Testing

After setup, test the forgot password feature. Check Railway logs for:
- `✓ SendGrid email sent successfully to: [email] (Status: 202)`

If you see errors, check:
1. API key is correct
2. Sender email is verified
3. You haven't exceeded free tier limit (100 emails/day)

## Alternative: Keep Using Gmail SMTP (Not Recommended for Railway)

If you want to keep using Gmail, you would need to:
1. Set up a VPN or proxy server
2. Route SMTP traffic through it
3. This is complex and not recommended

SendGrid is the better solution for Railway deployments.
