# HTML Learning Guide for Full-Stack Web Development

## Table of Contents
1. [Introduction to HTML](#introduction-to-html)
2. [HTML Document Structure](#html-document-structure)
3. [Essential HTML Elements](#essential-html-elements)
4. [Text Formatting](#text-formatting)
5. [Links and Navigation](#links-and-navigation)
6. [Images and Media](#images-and-media)
7. [Lists](#lists)
8. [Tables](#tables)
9. [Forms and Input](#forms-and-input)
10. [Semantic HTML](#semantic-html)
11. [HTML5 Features](#html5-features)
12. [Meta Tags and SEO](#meta-tags-and-seo)
13. [Accessibility](#accessibility)
14. [Best Practices](#best-practices)
15. [Common Patterns](#common-patterns)

---

## Introduction to HTML

**HTML (HyperText Markup Language)** is the standard markup language for creating web pages. It describes the structure and content of a webpage using elements (tags).

### Key Concepts
- **Elements**: Building blocks of HTML (e.g., `<div>`, `<p>`, `<h1>`)
- **Tags**: Markers that define elements (`<tagname>content</tagname>`)
- **Attributes**: Additional information about elements (`class="navbar"`)
- **Nesting**: Elements can contain other elements

### Basic Syntax
```html
<tagname attribute="value">Content goes here</tagname>
```

**Self-closing tags** (no content):
```html
<img src="photo.jpg" alt="Description" />
<br />
<input type="text" />
```

---

## HTML Document Structure

Every HTML document follows this basic structure:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page description for SEO">
    <title>Page Title - Shows in Browser Tab</title>
    
    <!-- External Resources -->
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="script.js" defer></script>
</head>
<body>
    <!-- Your content goes here -->
    <h1>Welcome to My Website</h1>
    <p>This is a paragraph.</p>
</body>
</html>
```

### Essential Components

| Element | Purpose |
|---------|---------|
| `<!DOCTYPE html>` | Declares HTML5 document type |
| `<html>` | Root element containing all content |
| `<head>` | Metadata, links to CSS/JS, title |
| `<meta charset="UTF-8">` | Character encoding (supports all languages) |
| `<meta name="viewport">` | Makes site responsive on mobile |
| `<title>` | Page title shown in browser tab |
| `<body>` | Visible content of the webpage |

---

## Essential HTML Elements

### Headings (H1-H6)
Headings create hierarchy. Use only **one H1** per page for SEO.

```html
<h1>Main Page Title (Most Important)</h1>
<h2>Section Heading</h2>
<h3>Subsection Heading</h3>
<h4>Minor Heading</h4>
<h5>Smaller Heading</h5>
<h6>Smallest Heading</h6>
```

### Paragraphs and Line Breaks
```html
<p>This is a paragraph. It automatically adds spacing above and below.</p>

<p>Multiple spaces     and line breaks
in HTML are ignored. Use tags for formatting.</p>

<p>Line one<br>Line two with a line break</p>

<hr> <!-- Horizontal rule (divider line) -->
```

### Divs and Spans
```html
<!-- DIV: Block-level container (takes full width) -->
<div class="container">
    <p>Content inside a div</p>
</div>

<!-- SPAN: Inline container (only takes needed width) -->
<p>This is <span class="highlight">highlighted text</span> in a paragraph.</p>
```

---

## Text Formatting

### Bold, Italic, Underline
```html
<!-- Semantic (preferred) -->
<strong>Important text (bold)</strong>
<em>Emphasized text (italic)</em>

<!-- Visual only (not recommended for meaning) -->
<b>Bold text</b>
<i>Italic text</i>
<u>Underlined text</u>

<!-- Other formatting -->
<mark>Highlighted text</mark>
<small>Small text</small>
<del>Deleted text (strikethrough)</del>
<ins>Inserted text (underlined)</ins>
<sub>Subscript: H<sub>2</sub>O</sub>
<sup>Superscript: x<sup>2</sup></sup>
```

### Code and Preformatted Text
```html
<p>The <code>console.log()</code> function prints to the console.</p>

<pre>
  This is preformatted text.
  Spaces and line breaks
  are preserved exactly.
</pre>

<pre><code>
function greet(name) {
    console.log("Hello, " + name);
}
</code></pre>
```

### Quotes
```html
<blockquote>
    "This is a long quotation that will be indented."
    <cite>- Author Name</cite>
</blockquote>

<p>He said, <q>This is a short inline quote.</q></p>
```

---

## Links and Navigation

### Basic Links
```html
<!-- External link -->
<a href="https://example.com">Visit Example</a>

<!-- Internal link (same site) -->
<a href="/about.php">About Page</a>
<a href="contact.php">Contact</a>

<!-- Open in new tab -->
<a href="https://google.com" target="_blank" rel="noopener noreferrer">
    Google (opens in new tab)
</a>

<!-- Email link -->
<a href="mailto:info@example.com">Send Email</a>

<!-- Phone link -->
<a href="tel:+1234567890">Call Us</a>

<!-- Download link -->
<a href="resume.pdf" download>Download Resume</a>
```

### Link Attributes
- `href`: URL destination
- `target="_blank"`: Opens in new tab
- `rel="noopener noreferrer"`: Security for external links
- `title`: Tooltip text on hover

### Anchor Links (Jump to Section)
```html
<!-- Link to section -->
<a href="#section1">Jump to Section 1</a>

<!-- Target section -->
<h2 id="section1">Section 1</h2>
<p>Content here...</p>
```

### Navigation Menus
```html
<nav>
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/jobs.php">Jobs</a></li>
        <li><a href="/about.php">About</a></li>
        <li><a href="/contact.php">Contact</a></li>
    </ul>
</nav>
```

---

## Images and Media

### Images
```html
<!-- Basic image -->
<img src="photo.jpg" alt="Description of image">

<!-- Image with dimensions -->
<img src="logo.png" alt="Company Logo" width="200" height="100">

<!-- Responsive image -->
<img src="image.jpg" alt="Description" style="max-width: 100%; height: auto;">

<!-- Image as link -->
<a href="/home">
    <img src="logo.png" alt="Home">
</a>

<!-- Figure with caption -->
<figure>
    <img src="chart.png" alt="Sales Chart">
    <figcaption>Q4 Sales Performance</figcaption>
</figure>
```

**Important**: Always include `alt` attribute for accessibility!

### Video
```html
<video width="640" height="360" controls>
    <source src="video.mp4" type="video/mp4">
    <source src="video.webm" type="video/webm">
    Your browser does not support the video tag.
</video>

<!-- Video attributes -->
<video controls autoplay muted loop poster="thumbnail.jpg">
    <source src="intro.mp4" type="video/mp4">
</video>
```

### Audio
```html
<audio controls>
    <source src="song.mp3" type="audio/mpeg">
    <source src="song.ogg" type="audio/ogg">
    Your browser does not support the audio element.
</audio>
```

### Iframe (Embed Content)
```html
<!-- Embed YouTube video -->
<iframe width="560" height="315" 
    src="https://www.youtube.com/embed/VIDEO_ID" 
    frameborder="0" 
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
    allowfullscreen>
</iframe>

<!-- Embed Google Maps -->
<iframe src="https://www.google.com/maps/embed?pb=..." 
    width="600" height="450" 
    style="border:0;" 
    allowfullscreen="" 
    loading="lazy">
</iframe>
```

---

## Lists

### Unordered List (Bullets)
```html
<ul>
    <li>Item 1</li>
    <li>Item 2</li>
    <li>Item 3</li>
</ul>
```

### Ordered List (Numbers)
```html
<ol>
    <li>First step</li>
    <li>Second step</li>
    <li>Third step</li>
</ol>

<!-- Start from different number -->
<ol start="5">
    <li>This is item 5</li>
    <li>This is item 6</li>
</ol>

<!-- Different numbering types -->
<ol type="A">  <!-- A, B, C -->
<ol type="a">  <!-- a, b, c -->
<ol type="I">  <!-- I, II, III -->
<ol type="i">  <!-- i, ii, iii -->
```

### Description List
```html
<dl>
    <dt>HTML</dt>
    <dd>HyperText Markup Language</dd>
    
    <dt>CSS</dt>
    <dd>Cascading Style Sheets</dd>
    
    <dt>JavaScript</dt>
    <dd>Programming language for web interactivity</dd>
</dl>
```

### Nested Lists
```html
<ul>
    <li>Main Item 1
        <ul>
            <li>Sub-item 1.1</li>
            <li>Sub-item 1.2</li>
        </ul>
    </li>
    <li>Main Item 2</li>
</ul>
```

---

## Tables

### Basic Table
```html
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Age</th>
            <th>City</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>John</td>
            <td>25</td>
            <td>New York</td>
        </tr>
        <tr>
            <td>Sarah</td>
            <td>30</td>
            <td>London</td>
        </tr>
    </tbody>
</table>
```

### Table with Styling
```html
<table border="1" cellpadding="10" cellspacing="0">
    <caption>Employee Data</caption>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Department</th>
            <th>Salary</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>001</td>
            <td>Alice</td>
            <td>Engineering</td>
            <td>$80,000</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">Total Employees:</td>
            <td>1</td>
        </tr>
    </tfoot>
</table>
```

### Merged Cells
```html
<table border="1">
    <tr>
        <th>Name</th>
        <th colspan="2">Contact</th> <!-- Spans 2 columns -->
    </tr>
    <tr>
        <td>John</td>
        <td>Email</td>
        <td>Phone</td>
    </tr>
    <tr>
        <td rowspan="2">Sarah</td> <!-- Spans 2 rows -->
        <td>sarah@example.com</td>
        <td>555-1234</td>
    </tr>
    <tr>
        <td>sarah2@example.com</td>
        <td>555-5678</td>
    </tr>
</table>
```

---

## Forms and Input

### Basic Form Structure
```html
<form action="/api/submit.php" method="POST">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    
    <button type="submit">Submit</button>
</form>
```

### Form Attributes
- `action`: URL where form data is sent
- `method`: HTTP method (`GET` or `POST`)
- `enctype="multipart/form-data"`: Required for file uploads

### Input Types

```html
<!-- Text inputs -->
<input type="text" placeholder="Enter text">
<input type="password" placeholder="Password">
<input type="email" placeholder="email@example.com">
<input type="tel" placeholder="Phone number">
<input type="url" placeholder="https://example.com">
<input type="search" placeholder="Search...">

<!-- Number inputs -->
<input type="number" min="0" max="100" step="5" value="50">
<input type="range" min="0" max="100" value="50">

<!-- Date and time -->
<input type="date">
<input type="time">
<input type="datetime-local">
<input type="month">
<input type="week">

<!-- Selection -->
<input type="checkbox" id="agree" name="agree">
<input type="radio" name="gender" value="male"> Male
<input type="radio" name="gender" value="female"> Female

<!-- File upload -->
<input type="file" accept=".jpg,.png,.pdf">
<input type="file" multiple> <!-- Multiple files -->

<!-- Other -->
<input type="color" value="#ff0000">
<input type="hidden" name="user_id" value="123">

<!-- Buttons -->
<input type="submit" value="Submit">
<input type="reset" value="Reset">
<input type="button" value="Click Me">
```

### Textarea
```html
<textarea name="message" rows="5" cols="40" placeholder="Enter your message..."></textarea>

<!-- With character limit -->
<textarea maxlength="500" required></textarea>
```

### Select Dropdown
```html
<select name="country" required>
    <option value="">Select a country</option>
    <option value="us">United States</option>
    <option value="uk">United Kingdom</option>
    <option value="ca">Canada</option>
</select>

<!-- With grouped options -->
<select name="job_type">
    <optgroup label="Full-Time">
        <option value="fulltime-dev">Developer</option>
        <option value="fulltime-designer">Designer</option>
    </optgroup>
    <optgroup label="Part-Time">
        <option value="parttime-dev">Developer</option>
        <option value="parttime-designer">Designer</option>
    </optgroup>
</select>

<!-- Multiple selection -->
<select name="skills[]" multiple size="5">
    <option value="html">HTML</option>
    <option value="css">CSS</option>
    <option value="js">JavaScript</option>
    <option value="php">PHP</option>
</select>
```

### Complete Form Example
```html
<form action="/api/register.php" method="POST" enctype="multipart/form-data">
    <fieldset>
        <legend>Personal Information</legend>
        
        <label for="name">Full Name *</label>
        <input type="text" id="name" name="name" required>
        
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required>
        
        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" pattern="[0-9]{10}">
        
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob">
        
        <label>Gender</label>
        <input type="radio" id="male" name="gender" value="male">
        <label for="male">Male</label>
        
        <input type="radio" id="female" name="gender" value="female">
        <label for="female">Female</label>
        
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="4"></textarea>
        
        <label for="resume">Upload Resume</label>
        <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
        
        <label>
            <input type="checkbox" name="terms" required>
            I agree to the terms and conditions *
        </label>
        
        <button type="submit">Register</button>
        <button type="reset">Clear Form</button>
    </fieldset>
</form>
```

### Input Attributes
```html
<input type="text" 
    name="username"
    id="username"
    placeholder="Enter username"
    value="Default value"
    required
    readonly
    disabled
    maxlength="50"
    minlength="3"
    pattern="[A-Za-z0-9]+"
    autocomplete="off"
    autofocus>
```

---

## Semantic HTML

Semantic HTML uses meaningful tags that describe content purpose, not just appearance.

### Page Structure
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Semantic HTML Example</title>
</head>
<body>
    <!-- Top of page -->
    <header>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about">About</a></li>
            </ul>
        </nav>
    </header>
    
    <!-- Main content -->
    <main>
        <article>
            <header>
                <h1>Article Title</h1>
                <p>Published on <time datetime="2025-12-04">December 4, 2025</time></p>
            </header>
            
            <section>
                <h2>Section 1</h2>
                <p>Content...</p>
            </section>
            
            <section>
                <h2>Section 2</h2>
                <p>Content...</p>
            </section>
            
            <footer>
                <p>Written by: John Doe</p>
            </footer>
        </article>
        
        <aside>
            <h3>Related Articles</h3>
            <ul>
                <li><a href="#">Article 1</a></li>
                <li><a href="#">Article 2</a></li>
            </ul>
        </aside>
    </main>
    
    <!-- Bottom of page -->
    <footer>
        <p>&copy; 2025 Your Company. All rights reserved.</p>
    </footer>
</body>
</html>
```

### Semantic Tags Reference

| Tag | Purpose |
|-----|---------|
| `<header>` | Introductory content, logo, navigation |
| `<nav>` | Navigation links |
| `<main>` | Primary content (one per page) |
| `<article>` | Self-contained content (blog post, news article) |
| `<section>` | Thematic grouping of content |
| `<aside>` | Sidebar, related content |
| `<footer>` | Footer information, copyright |
| `<figure>` | Images, diagrams with captions |
| `<figcaption>` | Caption for figure |
| `<time>` | Date/time information |
| `<mark>` | Highlighted text |
| `<details>` | Expandable content widget |
| `<summary>` | Summary for details element |

### Details/Summary (Accordion)
```html
<details>
    <summary>Click to expand</summary>
    <p>Hidden content that appears when summary is clicked.</p>
</details>

<details open>
    <summary>This one starts expanded</summary>
    <p>Content is visible by default.</p>
</details>
```

---

## HTML5 Features

### Data Attributes
Store custom data on elements:
```html
<div class="user-card" data-user-id="12345" data-role="admin">
    User Information
</div>

<button data-action="delete" data-confirm="Are you sure?">
    Delete
</button>

<!-- Access in JavaScript: element.dataset.userId -->
```

### Canvas (Drawing Graphics)
```html
<canvas id="myCanvas" width="400" height="200"></canvas>

<script>
const canvas = document.getElementById('myCanvas');
const ctx = canvas.getContext('2d');
ctx.fillStyle = 'blue';
ctx.fillRect(10, 10, 100, 50);
</script>
```

### SVG (Scalable Vector Graphics)
```html
<svg width="100" height="100">
    <circle cx="50" cy="50" r="40" stroke="black" stroke-width="3" fill="red" />
</svg>

<svg viewBox="0 0 100 100">
    <rect x="10" y="10" width="80" height="80" fill="blue" />
    <text x="50" y="55" text-anchor="middle" fill="white">Hello</text>
</svg>
```

### Dialog (Modal)
```html
<dialog id="myDialog">
    <h2>This is a dialog</h2>
    <p>Dialog content goes here.</p>
    <button onclick="document.getElementById('myDialog').close()">Close</button>
</dialog>

<button onclick="document.getElementById('myDialog').showModal()">
    Open Dialog
</button>
```

### Progress Bar
```html
<label for="progress">Download progress:</label>
<progress id="progress" value="70" max="100">70%</progress>

<!-- Indeterminate progress -->
<progress></progress>
```

### Meter (Gauge)
```html
<label for="disk-usage">Disk usage:</label>
<meter id="disk-usage" value="0.6" min="0" max="1" low="0.3" high="0.8" optimum="0.2">
    60%
</meter>
```

---

## Meta Tags and SEO

### Essential Meta Tags
```html
<head>
    <!-- Character encoding -->
    <meta charset="UTF-8">
    
    <!-- Responsive viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO basics -->
    <title>Page Title (50-60 characters)</title>
    <meta name="description" content="Page description for search results (150-160 characters)">
    <meta name="keywords" content="keyword1, keyword2, keyword3">
    <meta name="author" content="Your Name">
    
    <!-- Robots -->
    <meta name="robots" content="index, follow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
</head>
```

### Open Graph (Social Media Sharing)
```html
<meta property="og:title" content="Your Page Title">
<meta property="og:description" content="Description for social media">
<meta property="og:image" content="https://example.com/image.jpg">
<meta property="og:url" content="https://example.com/page">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Your Site Name">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Your Page Title">
<meta name="twitter:description" content="Description for Twitter">
<meta name="twitter:image" content="https://example.com/image.jpg">
```

### PWA (Progressive Web App)
```html
<!-- Manifest -->
<link rel="manifest" href="/manifest.json">

<!-- Theme color -->
<meta name="theme-color" content="#0a66c2">

<!-- Apple -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="App Name">
<link rel="apple-touch-icon" href="/icon-192.png">
```

---

## Accessibility

### ARIA Attributes
```html
<!-- Role -->
<div role="navigation">
    <ul>
        <li><a href="/">Home</a></li>
    </ul>
</div>

<!-- Labels -->
<button aria-label="Close dialog">×</button>
<img src="icon.png" aria-label="Settings icon">

<!-- States -->
<button aria-pressed="true">Bold</button>
<div aria-expanded="false" aria-controls="menu">Menu</div>

<!-- Live regions -->
<div aria-live="polite" aria-atomic="true">
    Status updates appear here
</div>
```

### Accessible Forms
```html
<form>
    <!-- Always associate labels with inputs -->
    <label for="email">Email Address</label>
    <input type="email" id="email" name="email" 
           aria-required="true" 
           aria-describedby="email-help">
    <small id="email-help">We'll never share your email.</small>
    
    <!-- Error messages -->
    <input type="text" aria-invalid="true" aria-describedby="error-msg">
    <span id="error-msg" role="alert">This field is required</span>
</form>
```

### Skip Links
```html
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <nav>
        <!-- Navigation -->
    </nav>
    
    <main id="main-content">
        <!-- Main content -->
    </main>
</body>
```

### Alt Text Best Practices
```html
<!-- Descriptive alt text -->
<img src="chart.png" alt="Bar chart showing 25% increase in sales from Q3 to Q4">

<!-- Decorative images -->
<img src="decoration.png" alt="">

<!-- Images as links -->
<a href="/profile">
    <img src="profile.jpg" alt="View user profile">
</a>
```

---

## Best Practices

### 1. Use Semantic HTML
```html
<!-- ❌ Bad -->
<div class="header">
    <div class="nav">...</div>
</div>

<!-- ✅ Good -->
<header>
    <nav>...</nav>
</header>
```

### 2. Proper Nesting
```html
<!-- ❌ Bad -->
<p><div>Text</div></p>

<!-- ✅ Good -->
<div><p>Text</p></div>
```

### 3. Always Close Tags
```html
<!-- ❌ Bad -->
<p>Paragraph
<p>Another paragraph

<!-- ✅ Good -->
<p>Paragraph</p>
<p>Another paragraph</p>
```

### 4. Use Lowercase
```html
<!-- ❌ Bad -->
<DIV CLASS="Container">
    <P>Text</P>
</DIV>

<!-- ✅ Good -->
<div class="container">
    <p>Text</p>
</div>
```

### 5. Quote Attributes
```html
<!-- ❌ Bad -->
<input type=text name=username>

<!-- ✅ Good -->
<input type="text" name="username">
```

### 6. Minimize Inline Styles
```html
<!-- ❌ Bad -->
<p style="color: red; font-size: 18px;">Text</p>

<!-- ✅ Good -->
<p class="highlight">Text</p>
<!-- Style in CSS file -->
```

### 7. Use Comments Wisely
```html
<!-- Main navigation section -->
<nav>
    <!-- Navigation links -->
</nav>

<!-- Don't over-comment obvious things -->
```

### 8. Validate Your HTML
Use [W3C Validator](https://validator.w3.org/) to check for errors.

---

## Common Patterns

### Card Component
```html
<div class="card">
    <img src="thumbnail.jpg" alt="Card image">
    <div class="card-content">
        <h3>Card Title</h3>
        <p>Card description goes here.</p>
        <a href="/details" class="button">Learn More</a>
    </div>
</div>
```

### Hero Section
```html
<section class="hero">
    <div class="hero-content">
        <h1>Welcome to Our Website</h1>
        <p>Discover amazing opportunities</p>
        <a href="/signup" class="cta-button">Get Started</a>
    </div>
</section>
```

### Profile Card
```html
<div class="profile-card">
    <img src="avatar.jpg" alt="John Doe profile picture" class="avatar">
    <h2>John Doe</h2>
    <p class="title">Full-Stack Developer</p>
    <div class="social-links">
        <a href="#">LinkedIn</a>
        <a href="#">GitHub</a>
        <a href="#">Twitter</a>
    </div>
</div>
```

### Breadcrumb Navigation
```html
<nav aria-label="Breadcrumb">
    <ol class="breadcrumb">
        <li><a href="/">Home</a></li>
        <li><a href="/jobs">Jobs</a></li>
        <li aria-current="page">Software Engineer</li>
    </ol>
</nav>
```

### Modal/Dialog
```html
<div class="modal" id="loginModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Login</h2>
            <button class="close" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form>
                <input type="email" placeholder="Email">
                <input type="password" placeholder="Password">
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</div>
```

### Tabs
```html
<div class="tabs">
    <div class="tab-buttons" role="tablist">
        <button role="tab" aria-selected="true" aria-controls="tab1">Tab 1</button>
        <button role="tab" aria-selected="false" aria-controls="tab2">Tab 2</button>
        <button role="tab" aria-selected="false" aria-controls="tab3">Tab 3</button>
    </div>
    
    <div id="tab1" role="tabpanel">Content for Tab 1</div>
    <div id="tab2" role="tabpanel" hidden>Content for Tab 2</div>
    <div id="tab3" role="tabpanel" hidden>Content for Tab 3</div>
</div>
```

### Pagination
```html
<nav aria-label="Pagination">
    <ul class="pagination">
        <li><a href="?page=1" aria-label="Previous page">«</a></li>
        <li><a href="?page=1">1</a></li>
        <li><a href="?page=2" aria-current="page">2</a></li>
        <li><a href="?page=3">3</a></li>
        <li><a href="?page=3" aria-label="Next page">»</a></li>
    </ul>
</nav>
```

### Grid Layout
```html
<div class="grid">
    <div class="grid-item">Item 1</div>
    <div class="grid-item">Item 2</div>
    <div class="grid-item">Item 3</div>
    <div class="grid-item">Item 4</div>
</div>
```

### Contact Form
```html
<form action="/api/contact.php" method="POST">
    <h2>Contact Us</h2>
    
    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject">
    </div>
    
    <div class="form-group">
        <label for="message">Message *</label>
        <textarea id="message" name="message" rows="6" required></textarea>
    </div>
    
    <button type="submit">Send Message</button>
</form>
```

---

## Quick Reference: Essential HTML Elements

### Structure
```html
<!DOCTYPE html>, <html>, <head>, <body>, <div>, <span>
```

### Metadata
```html
<title>, <meta>, <link>, <style>, <script>
```

### Content Sectioning
```html
<header>, <nav>, <main>, <article>, <section>, <aside>, <footer>
```

### Text Content
```html
<p>, <h1>-<h6>, <ul>, <ol>, <li>, <dl>, <dt>, <dd>, <blockquote>, <pre>
```

### Inline Text
```html
<a>, <strong>, <em>, <span>, <br>, <code>, <mark>, <small>
```

### Forms
```html
<form>, <input>, <textarea>, <button>, <select>, <option>, <label>, <fieldset>
```

### Media
```html
<img>, <video>, <audio>, <iframe>, <canvas>, <svg>
```

### Tables
```html
<table>, <thead>, <tbody>, <tfoot>, <tr>, <th>, <td>
```

---

## HTML Character Entities

Special characters that need encoding:

```html
&lt;     <!-- < -->
&gt;     <!-- > -->
&amp;    <!-- & -->
&quot;   <!-- " -->
&apos;   <!-- ' -->
&nbsp;   <!-- Non-breaking space -->
&copy;   <!-- © -->
&reg;    <!-- ® -->
&trade;  <!-- ™ -->
&euro;   <!-- € -->
&pound;  <!-- £-->
&yen;    <!-- ¥ -->
```

---

## Practice Exercise: Build a Complete Page

Put it all together:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CareerConnect Hub - Find your dream job">
    <title>CareerConnect Hub - Job Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <img src="logo.png" alt="CareerConnect Hub Logo">
                <h1>CareerConnect Hub</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/jobs.php">Jobs</a></li>
                    <li><a href="/about.php">About</a></li>
                    <li><a href="/contact.php">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Find Your Dream Job Today</h2>
            <p>Connect with top employers and start your career journey</p>
            <form action="/search" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Job title or keyword">
                <input type="text" name="location" placeholder="Location">
                <button type="submit">Search Jobs</button>
            </form>
        </div>
    </section>
    
    <!-- Featured Jobs -->
    <main>
        <section class="featured-jobs">
            <div class="container">
                <h2>Featured Jobs</h2>
                <div class="job-grid">
                    <article class="job-card">
                        <img src="company-logo.png" alt="Company Logo">
                        <h3>Software Engineer</h3>
                        <p class="company">Tech Corp</p>
                        <p class="location">San Francisco, CA</p>
                        <p class="salary">$120,000 - $150,000</p>
                        <a href="/job/123" class="button">View Details</a>
                    </article>
                    
                    <!-- More job cards... -->
                </div>
            </div>
        </section>
        
        <!-- How It Works -->
        <section class="how-it-works">
            <div class="container">
                <h2>How It Works</h2>
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>Create Profile</h3>
                        <p>Sign up and build your professional profile</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <h3>Search Jobs</h3>
                        <p>Browse thousands of job opportunities</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>Apply</h3>
                        <p>Submit your application with one click</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>About Us</h4>
                    <ul>
                        <li><a href="/about">Company</a></li>
                        <li><a href="/team">Team</a></li>
                        <li><a href="/careers">Careers</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="/help">Help Center</a></li>
                        <li><a href="/contact">Contact</a></li>
                        <li><a href="/faq">FAQ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="/privacy">Privacy Policy</a></li>
                        <li><a href="/terms">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 CareerConnect Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="app.js"></script>
</body>
</html>
```

---

## Next Steps

1. **Practice Building Pages**: Create different types of pages (landing, form, blog, dashboard)
2. **Learn CSS**: Style your HTML with CSS for beautiful designs
3. **Add JavaScript**: Make pages interactive with JavaScript
4. **Study Accessibility**: Make your sites usable for everyone
5. **Explore Frameworks**: Learn React, Vue, or Angular for modern web apps
6. **Backend Integration**: Connect HTML forms to PHP/Node.js backends
7. **Responsive Design**: Make sites work on all devices
8. **SEO Optimization**: Learn to rank better in search engines

---

## Resources

- **MDN Web Docs**: https://developer.mozilla.org/en-US/docs/Web/HTML
- **W3Schools**: https://www.w3schools.com/html/
- **HTML Validator**: https://validator.w3.org/
- **Can I Use**: https://caniuse.com/ (Browser compatibility)
- **WebAIM**: https://webaim.org/ (Accessibility)

---

## Summary

**HTML is the foundation of web development.** Master these concepts:

✅ Document structure (`<!DOCTYPE>`, `<html>`, `<head>`, `<body>`)  
✅ Semantic elements (`<header>`, `<nav>`, `<main>`, `<footer>`)  
✅ Forms and inputs (all input types, validation)  
✅ Tables (structure, headers, merged cells)  
✅ Media (images, video, audio, iframe)  
✅ Accessibility (ARIA, alt text, semantic HTML)  
✅ SEO (meta tags, Open Graph, structured data)  
✅ Best practices (validation, semantic markup, performance)  

**Keep practicing and building real projects!** 🚀

---

*This guide covers everything you need to know about HTML for full-stack web development. Practice regularly and refer back to this guide as needed.*
