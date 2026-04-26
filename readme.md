<p align="center">
  <a href="https://ztfr.eu/matrix">
    <img src="assets/community-badge.png" alt="Join Zeitfresser Matrix Community" height="70" />
  </a>
</p>

<h1 align="center">
Zeitfresser Wordpress Theme
</h1>

<h4 align="center">
A performance-optimized, minimalist dark blog theme for WordPress, built for fast and distraction-free technical writing.
</h4>

<h6 align="center">
  <a href="https://ztfr.eu">🏰 Website</a>
  ·
  <a href="https://ztfr.eu/matrix">📰 Matrix Community</a>
  ·
  <a href="https://social.ztfr.eu/@dome">🐘 Mastodon</a> 
  ·
  <a href="https://look.ztfr.eu/#/#support:ztfr.eu">💬 Support</a> 
</h6>

<br>

<img width="1176" height="1240" alt="preview" src="https://github.com/user-attachments/assets/b7f2d4ce-828a-406e-9302-61074688ff74" />
<br>
<img width="1800" height="1271" alt="preview" src="https://github.com/user-attachments/assets/aefff842-8189-48ae-965c-77799667b2a9" />


## ✨ Overview

Zeitfresser is a custom-built WordPress theme designed with a clear focus:  
**fast, readable, and distraction-free technical blogging**.

Originally based on the popular [Daisy Blog](https://wordpress.org/themes/daisy-blog/) theme, Zeitfresser has evolved into a fully independent and heavily optimized codebase. Over time, every layer of the theme has been reworked with performance, clarity, and maintainability in mind.

The visual identity follows a minimalist dark aesthetic inspired by Dracula, where typography, spacing, and structure take priority over decoration. The result is a calm, highly readable environment tailored for long-form technical content.

## 🚀 Performance & Architecture

Performance is not an afterthought in Zeitfresser — it is the foundation of the entire system.

Instead of relying on heavy abstractions or third-party dependencies, the theme focuses on a lean architecture with minimal JavaScript, carefully structured CSS, and predictable rendering behavior. Assets are only loaded when needed, avoiding unnecessary overhead and reducing the risk of render-blocking resources.

The DOM structure remains intentionally simple, which helps ensure consistent rendering across browsers and improves maintainability over time.

## 🖼️ Image Optimization System (v1.7)

With version **1.7**, Zeitfresser introduces a fully integrated **image optimization pipeline**.

Uploaded images can now be automatically converted to modern formats such as **AVIF** or **WebP**, depending on server capabilities. This significantly reduces file sizes while preserving visual quality, leading to faster page loads and improved Core Web Vitals.

The system is designed to be both flexible and safe:

- Optimization can run automatically on upload or manually via the dashboard  
- Original file paths are preserved before any transformation  
- Cleanup of original files is optional and only performed when safe  
- All operations are versioned and idempotent  

A dedicated **Performance Tools dashboard** allows you to process existing media libraries in batches, monitor progress in real time, and optionally remove original files after successful optimization.

For full control, automation can be configured via the WordPress Customizer. You can choose between manual processing, automatic optimization, or a fully automated workflow that includes cleanup of original images.

## 🔤 Local Fonts & Typography System

Zeitfresser uses a fully self-hosted typography system built around Oswald and Roboto.

All fonts are served locally using optimized `.woff2` files, eliminating external requests and improving privacy and performance. Critical font assets are preloaded to ensure fast rendering, and the entire system is built using CSS variables for consistency and maintainability.

The result is a predictable and visually stable typography layer across all environments.

## 🎨 CSS-Based Color System

The theme relies entirely on a static, CSS variable-driven color system.

Instead of dynamically generated styles or PHP-driven color logic, all values are defined using native CSS custom properties. This approach simplifies the styling layer, improves performance, and ensures consistent rendering without runtime overhead.

## ⚡ Core Web Vitals

Zeitfresser is optimized for real-world performance.

Layouts are stable and free of unexpected shifts, ensuring a solid CLS score. Critical assets are loaded early, and render-blocking resources are minimized. Even long-form articles with complex structures remain responsive and fast.

## 📑 Floating Table of Contents

A key editorial feature of the theme is the floating Table of Contents.

It is generated automatically from headings and positioned outside the main content flow, allowing readers to navigate long articles without distraction. The active section is highlighted, and a subtle progress indicator provides orientation within the document.

## ⚙️ Customization

Zeitfresser integrates cleanly with the WordPress Customizer, offering a small but focused set of options.

Instead of overwhelming users with configuration, the theme provides only what is necessary to adapt behavior without introducing complexity. This includes layout-related options and performance settings such as the image optimization pipeline.

## 🎨 Design Philosophy

The guiding principle behind Zeitfresser is simple:  
**clarity over decoration**.

Every design decision is made to support readability and structure. Colors are used sparingly, spacing is intentional, and typography defines hierarchy. The goal is not to impress visually, but to support sustained reading without fatigue.

## 🧹 Code Quality

The codebase has been systematically refactored to remove legacy patterns and reduce complexity.

The theme avoids unnecessary abstractions and focuses on a modular, predictable structure. This makes it easier to maintain, extend, and reason about over time, without accumulating technical debt.

## 📱 Responsiveness

Zeitfresser adapts naturally across devices.

On desktop, it provides a full editorial experience with structured navigation. On mobile, the layout simplifies while preserving readability and access to core features. Components such as the Table of Contents adjust dynamically based on context.

## 📦 Installation

To install the theme:

1. Download or clone the repository  
2. Upload it to your WordPress installation: `/wp-content/themes/`  
3. Activate it via: **Appearance → Themes**

## ⚡ Recommended Setup

While the theme performs well out of the box, it benefits from a modern setup.

Using caching, a CDN, and optimized hosting will further improve performance, especially for larger sites with extensive media libraries.

## 🛠 Development & Support

If you want to get support or participate in development, you can join the <a href="https://ztfr.eu/matrix">Zeitfresser Matrix Community</a> or the <a href="https://look.ztfr.eu/#/#support:ztfr.eu">Development & Support Channel</a>.

## 📄 License

GPL v2 or later.

Originally based on the [Daisy Blog](https://wordpress.org/themes/daisy-blog/) theme, now heavily modified into an independent codebase.

## 💬 Final Note

Zeitfresser is built for developers and writers who care about:

performance, readability, and clean engineering.

It avoids unnecessary complexity and focuses on doing a few things exceptionally well:  
**presenting content clearly, loading fast, and remaining maintainable over time.**
