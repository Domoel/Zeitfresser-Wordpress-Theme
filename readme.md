# Zeitfresser Theme

A performance-optimized, minimalist dark blog theme for WordPress, focused on **privacy, security, and technical content**.

Originally based on Daisy Blog, this theme has been heavily refactored, simplified, and extended into a **lean, production-ready custom theme**.

---

## ✨ Features

### 🚀 Performance First
- Optimized asset loading (no unnecessary scripts/styles)
- Removed WordPress bloat (emoji, embeds, etc.)
- Lazy loading images (`loading="lazy"`)
- Async decoding (`decoding="async"`)
- WebP image support (if server supports it)
- Reduced image sizes & optimized upload handling
- Minimal JavaScript footprint (vanilla JS only)

---

### 🧠 Core Web Vitals Optimized
- No layout shifts (CLS-safe)
- Optimized Largest Contentful Paint (LCP)
- Efficient DOM structure
- Lightweight scroll-based TOC (no heavy libraries)

---

### 🖼️ Image Optimization (Native)
- Automatic downscaling of large uploads
- Removed unused intermediate image sizes
- Balanced image quality vs. file size
- Optional WebP generation for thumbnails

---

### 📑 Floating Table of Contents (TOC)
- Editorial-style floating TOC (left side)
- Automatically generated from headings (H2–H4)
- Smooth scrolling navigation
- Active section highlighting
- Progress indicator
- Responsive (hidden on smaller screens)
- Scrollable without visible scrollbar

---

### ⚙️ Customizer Options

Located under **General Options → Article TOC**

- **Show Article TOC**
  - Enable/disable TOC globally
  - Default: enabled

- **Number of Headlines to Start TOC**
  - Minimum number of headings required
  - Default: 3
  - Range: 1–50

---

### 🎨 Design Philosophy
- Minimalist dark theme
- Strong focus on readability
- Accent color driven (purple highlight system)
- No visual clutter
- Editorial-style layout inspired by modern tech blogs

---

### 🧹 Clean Code Architecture
- Removed legacy theme bloat
- Modular structure
- Reduced CSS overrides and conflicts
- Centralized configuration handling
- No unnecessary dependencies

---

### 🔤 Typography
- Oswald (headings)
- Roboto (body)
- Optimized loading and fallback handling

---

### 📱 Responsive
- Fully responsive layout
- TOC automatically disabled on smaller screens
- Optimized mobile navigation

---

## 🛠️ Development Notes

- Built with **performance and maintainability in mind**
- No jQuery dependency
- Async-first approach where possible
- Designed for long-form technical content

---

## 📦 Installation

1. Download or clone the repository
2. Upload the theme to: /wp-content/themes/
3. Activate the theme in WordPress: Appearance → Themes

---

## ⚡ Recommended Setup

For best performance:

- Enable server-side caching
- Use a CDN (optional but recommended)
- Optimize existing images (if migrating from another theme)
- Use modern hosting (PHP 8+ recommended)

---

## 📌 Roadmap (Optional Ideas)

- Code block enhancements (copy button, syntax highlighting)
- Advanced TOC features (collapsible sections)
- Content utilities (callouts, notes, warnings)
- Further Core Web Vitals improvements

---

## 📄 License

GPL v2 or later  
Based on Daisy Blog, heavily modified and optimized.

---

## 💬 About

Zeitfresser is designed for **developers, privacy enthusiasts, and technical writers** who want a fast, clean, and distraction-free blogging experience.

---
