# 🎯 Getting Started - Your 10-Minute Setup Guide

## Welcome! Here's Everything You Need to Get Running

---

## ✅ Step-by-Step Checklist

### Step 1: Test Your Setup (2 minutes)
```
□ Open browser
□ Go to: http://yoursite.com/test_setup.php
□ Check for green checkmarks ✅
□ If any red ❌, check SETUP_GUIDE.md
```

**Expected result:** All tests pass with green checkmarks

---

### Step 2: Configure API Tokens (3 minutes)

Open these 4 files and update the tokens:

**File 1:** `api/v1/export_jobs.php`
```php
□ Find line: $validTokens = ['YOUR_SECRET_API_TOKEN', 'EXTERNAL_APP_TOKEN'];
□ Change to: $validTokens = ['sk_live_your_token_here', 'ext_token_456'];
```

**File 2:** `api/v1/export_applications.php`
```php
□ Same as above - use the same tokens
```

**File 3:** `api/v1/import_jobs.php`
```php
□ Same as above - use the same tokens
```

**File 4:** `api/v1/stats.php`
```php
□ Same as above - use the same tokens
```

**Pro tip:** Use a password generator for secure tokens (32+ characters)

---

### Step 3: Set Up External APIs (5 minutes)

#### Option A: Adzuna API (Recommended - Completely Free)

```
□ Go to: https://developer.adzuna.com/
□ Click "Sign Up" (it's free!)
□ Verify your email
□ Get your App ID (looks like: a1b2c3d4)
□ Get your App Key (looks like: 0123456789abcdef0123456789abcdef)
□ Open: classes/ExternalAPIService.php
□ Line 12: Change YOUR_ADZUNA_APP_ID to your App ID
□ Line 13: Change YOUR_ADZUNA_APP_KEY to your App Key
□ Save file
```

#### Option B: JSearch API (Alternative - Also Free)

```
□ Go to: https://rapidapi.com/
□ Sign up (free)
□ Search for "JSearch API"
□ Subscribe to free tier
□ Copy your RapidAPI key
□ Open: classes/ExternalAPIService.php
□ Line 84: Paste your RapidAPI key
□ Save file
```

**Note:** You can set up both! Use whichever works best.

---

### Step 4: Create Cache Directories (1 minute)

**Windows (PowerShell):**
```powershell
□ Open PowerShell in career_hub folder
□ Run: mkdir cache\api
□ Run: mkdir cache\notifications
```

**Mac/Linux (Terminal):**
```bash
□ Open Terminal in career_hub folder
□ Run: mkdir -p cache/api cache/notifications
□ Run: chmod 755 cache cache/api cache/notifications
```

**Or manually:**
```
□ Create folder: career_hub/cache/
□ Inside cache, create: api/
□ Inside cache, create: notifications/
```

---

### Step 5: Start WebSocket Server (1 minute)

**Windows - Easy Way:**
```
□ Double-click: start_websocket.bat
□ A window will open saying "WebSocket server started"
□ Keep this window open (minimize it)
```

**Windows - Manual Way:**
```powershell
□ Open PowerShell in career_hub folder
□ Run: php websocket_server.php
□ Keep window open
```

**Mac/Linux:**
```bash
□ Open Terminal in career_hub folder
□ Run: php websocket_server.php
□ Keep terminal open (or run in background)
```

**Expected output:**
```
===========================================
Career Hub WebSocket Server
===========================================

WebSocket server started on 0.0.0.0:8080
```

---

### Step 6: Test Everything (2 minutes)

**Test 1: Visit your website**
```
□ Go to your Career Hub website
□ Look for "● Connected" indicator (top-right corner)
□ Press F12 to open console
□ Look for: "WebSocket connected"
```

**Test 2: Test an API endpoint**
```
□ Open browser
□ Go to: http://yoursite.com/api/v1/stats.php?api_token=YOUR_TOKEN
□ Should see JSON with statistics
```

**Test 3: Test external API (if configured)**
```
□ Log in as admin
□ Go to: http://yoursite.com/api/v1/fetch_external_jobs.php?query=developer&source=adzuna
□ Should see job listings from Adzuna
```

---

## 🎉 Done! What Now?

### You Now Have:
- ✅ Object-oriented PHP architecture
- ✅ Two free external job APIs
- ✅ 6 REST API endpoints
- ✅ Real-time WebSocket notifications
- ✅ Complete documentation

### Quick Tests:

**Test the OOP Classes:**
```php
// Create a file: test_oop.php
<?php
require_once 'classes/autoload.php';

$jobModel = new Job();
$jobs = $jobModel->all(10, 0);
echo "Found " . count($jobs) . " jobs!";
?>
```

**Test the API (using curl):**
```bash
curl "http://yoursite.com/api/v1/stats.php?api_token=YOUR_TOKEN"
```

**Test WebSocket (JavaScript console):**
```javascript
wsClient.on('connected', () => console.log('WebSocket works!'));
```

---

## 📚 What to Read Next

**Choose your path:**

### Path 1: I Want to Use the APIs
→ Read: **API_QUICK_REFERENCE.md** (5 minutes)
→ Then: **API_DOCUMENTATION.md** (detailed)

### Path 2: I Want to Understand OOP
→ Read: **README_OOP.md** (15 minutes)
→ Try: Updating existing code to use OOP

### Path 3: I Want the Big Picture
→ Read: **IMPLEMENTATION_SUMMARY.md** (10 minutes)
→ Then: **README_NEW_FEATURES.md**

### Path 4: I Want to Integrate External Systems
→ Read: **API_DOCUMENTATION.md** (20 minutes)
→ Test: Export/Import APIs

---

## 🆘 Troubleshooting

### Problem: test_setup.php shows errors
**Solution:** Check SETUP_GUIDE.md → "Common Issues & Solutions"

### Problem: WebSocket won't start
**Solution:** 
```
□ Check if port 8080 is in use
□ Run: netstat -ano | findstr :8080 (Windows)
□ Enable sockets extension in php.ini
```

### Problem: API returns 401 Unauthorized
**Solution:**
```
□ Check API token is correct
□ Make sure no extra spaces
□ Verify token in both request and API file
```

### Problem: External API not working
**Solution:**
```
□ Verify API credentials are correct
□ Check you copied entire key (no spaces)
□ Test API directly on their website first
```

---

## 🎯 Quick Command Reference

**Start WebSocket:**
```bash
php websocket_server.php
```

**Test API:**
```bash
curl "http://yoursite.com/api/v1/stats.php?api_token=TOKEN"
```

**Import Jobs:**
```bash
curl -X POST http://yoursite.com/api/v1/import_jobs.php \
  -H "Content-Type: application/json" \
  -H "X-API-TOKEN: TOKEN" \
  -d '{"title":"Test","company":"ABC","description":"..."}'
```

**Fetch External Jobs:**
```
http://yoursite.com/api/v1/fetch_external_jobs.php?query=developer&import=true
```

---

## 📖 All Documentation Files

| File | What It's For | Time to Read |
|------|---------------|--------------|
| **GETTING_STARTED.md** | You are here! Setup guide | 10 min |
| **IMPLEMENTATION_SUMMARY.md** | What was built | 5 min |
| **README_NEW_FEATURES.md** | Feature overview | 10 min |
| **SETUP_GUIDE.md** | Detailed setup | 30 min |
| **API_DOCUMENTATION.md** | Complete API reference | 30 min |
| **API_QUICK_REFERENCE.md** | API cheat sheet | 5 min |
| **README_OOP.md** | OOP architecture guide | 20 min |
| **test_setup.php** | Verify setup (run it!) | 2 min |

---

## 💡 Pro Tips

1. **Bookmark test_setup.php** - Run it anytime to verify everything works
2. **Keep WebSocket running** - Use PM2 or Task Scheduler for 24/7 operation
3. **Generate strong tokens** - Use 32+ character random strings
4. **Test in staging first** - Always test before production
5. **Enable error logging** - Helps with debugging
6. **Read the docs** - They have all the answers!

---

## 🚀 Your Next Actions

**Right now:**
```
1. □ Complete the checklist above
2. □ Run test_setup.php
3. □ Start WebSocket server
4. □ Test one API endpoint
```

**This week:**
```
5. □ Read API_QUICK_REFERENCE.md
6. □ Try importing jobs from external APIs
7. □ Test real-time notifications
8. □ Update existing code to use OOP
```

**This month:**
```
9. □ Build integrations with external systems
10. □ Create analytics dashboard using stats API
11. □ Set up production deployment
12. □ Monitor and optimize
```

---

## 🎊 Congratulations!

You now have a **professional, enterprise-grade career platform** with:
- Modern architecture
- External integrations
- Real-time features
- Data sharing capabilities
- Complete documentation

**Start with the checklist above and you'll be running in 10 minutes!**

---

## 📞 Need Help?

**Quick Help:**
- Run test_setup.php for diagnostics
- Check SETUP_GUIDE.md for troubleshooting
- Review API_DOCUMENTATION.md for API questions

**Common Questions:**
- "How do I...?" → Check README_OOP.md
- "API not working?" → See API_DOCUMENTATION.md
- "Setup problems?" → Read SETUP_GUIDE.md
- "What can I do?" → See README_NEW_FEATURES.md

---

**Ready? Start with Step 1 above! ⬆️**

**Good luck! You've got this! 🚀**
