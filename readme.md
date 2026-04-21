<p align="center">
  <a href="https://ztfr.eu/matrix">
    <img src="assets/community-badge.png" alt="Join Zeitfresser Matrix Community" height="70" />
  </a>
</p>

<h1 align="center">
Zeitfresser Wordpress Theme
</h1>

<h4 align="center">
A performance-optimized, minimalist dark blog theme for WordPress, inspired by the popular Dracula aesthetic.
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

Zeitfresser is a custom-built WordPress theme designed with a clear focus: **fast, readable, and distraction-free technical blogging**.

Originally based on the popular [Daisy Blog](https://wordpress.org/themes/daisy-blog/) Theme. However with this version the theme has evolved into a fully independent, heavily optimized codebase. Every part of the system has been reworked with performance, clarity, and maintainability in mind. The result is not just a styled theme, but a streamlined platform for long-form content.

The design follows a minimalist dark aesthetic inspired by Dracula, while placing strong emphasis on typography, structure, and reading flow.

## 🚀 Performance & Architecture

Performance is not treated as an afterthought, but as a core design principle. The theme removes unnecessary WordPress overhead and delivers a lean frontend experience with minimal dependencies.

Assets are loaded selectively, scripts are deferred where possible, and no heavy libraries are used. The entire frontend runs on lightweight, purpose-built logic, avoiding common bottlenecks such as render-blocking JavaScript or bloated CSS.

Images are handled intelligently: large uploads are automatically scaled down, unused sizes are removed, and modern formats like WebP are supported when available. Combined with lazy loading and async decoding, this ensures efficient delivery without sacrificing visual quality.

## 🧠 Core Web Vitals

Zeitfresser is designed to perform well under real-world conditions.

Layout shifts are avoided by design, the DOM structure is kept clean and predictable, and the Largest Contentful Paint is optimized through prioritization of key elements. The result is a stable and responsive experience that holds up even for long, content-heavy articles.

## 📑 Floating Table of Contents

One of the core features of the theme is its editorial-style floating Table of Contents.

The TOC is automatically generated from the article structure and positioned outside the main content area, allowing readers to navigate long articles without breaking reading flow. It follows the scroll position, highlights the current section, and includes a subtle progress indicator.

Special care has been taken to ensure that this feature enhances usability without adding visual noise or performance overhead.

## ⚙️ Customization

The theme integrates directly into the WordPress Customizer, allowing essential behavior to be configured without introducing unnecessary complexity.

Within the General Options, the Table of Contents can be enabled or disabled globally. Additionally, a threshold can be defined that determines how many headings must be present before the TOC appears. This prevents unnecessary UI elements on shorter posts while keeping the feature effective for longer content.

## 🎨 Design Philosophy

Zeitfresser follows a simple but strict philosophy: **clarity over decoration**.

The visual design is intentionally minimal, using a dark color scheme with subtle purple accents to guide attention. Instead of relying on visual noise, the theme uses spacing, typography, and structure to create hierarchy.

This results in a reading experience that feels focused and calm, even for very long and complex articles.

## 🧹 Code Quality

The theme has been systematically cleaned and refactored.

Legacy components and unused features have been removed, CSS conflicts reduced, and functionality centralized where appropriate. The codebase is modular, predictable, and designed for long-term maintainability.

No unnecessary dependencies are introduced, and the theme avoids patterns that typically lead to technical debt in WordPress environments.

## 📱 Responsiveness

The layout adapts cleanly across devices.

While the full editorial experience is optimized for larger screens, essential functionality remains accessible on smaller devices. Features such as the Table of Contents are intelligently disabled when they no longer provide value, ensuring that the mobile experience remains clean and usable.

## 📦 Installation

To install the theme:

1. Download or clone the repository  
2. Upload it to your WordPress installation: /wp-content/themes/
3. Activate it via: Appearance → Themes

## ⚡ Recommended Setup

For best results, it is recommended to run the theme in a modern environment with caching enabled.

A CDN can further improve delivery performance, especially for global audiences. If migrating from another theme, existing images should be optimized to fully benefit from the built-in image handling.

## 🛠 Development & Support

Zeitfresser is actively developed and designed to evolve.

If you need to get support or want to participate in the active development of this software, you can <a href="https://ztfr.eu/matrix">join our Zeitfresser Matrix Community</a> or the <a href="https://look.ztfr.eu/#/#support:ztfr.eu">Development & Support Channel</a> on Matrix.

## 📄 License

GPL v2 or later. Originally based on the [Daisy Blog](https://wordpress.org/themes/daisy-blog/) Theme, now heavily modified and optimized into an independent theme.

## 💬 Final Note

Zeitfresser is built for people who care about performance, readability, and clean engineering.

It avoids unnecessary complexity and focuses on doing a few things exceptionally well:  
**presenting content clearly, loading fast, and staying maintainable.**
