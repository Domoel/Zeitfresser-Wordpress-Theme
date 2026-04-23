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

Originally based on the popular [Daisy Blog](https://wordpress.org/themes/daisy-blog/) theme, Zeitfresser has evolved into a fully independent and heavily optimized codebase. Every part of the system has been refactored with performance, clarity, and maintainability in mind.

The design follows a minimalist dark aesthetic inspired by Dracula, with a strong emphasis on typography, structure, and reading flow.

## 🚀 Performance & Architecture

Performance is a core design principle.

- No unnecessary dependencies or heavy libraries  
- Minimal JavaScript footprint  
- Deferred and optimized script loading  
- Lean CSS architecture with reduced redundancy  
- Clean DOM structure for predictable rendering  

Assets are loaded only when needed, avoiding common bottlenecks such as render-blocking scripts or excessive CSS overhead.

### Key Benefits

- No inline styles or dynamically injected CSS  
- No dependency on the WordPress Customizer for color rendering  
- Consistent color usage across all components  
- Easy to maintain and extend  
- Fully compatible with modern browsers and rendering pipelines  

### Design Approach

The theme follows a dark-first design with carefully selected contrast values:

- Background and surface colors are optimized for readability  
- Accent colors are used sparingly to guide attention  
- Typography and spacing work together with color to create hierarchy  

This approach ensures a clean, stable, and performant visual system without unnecessary complexity.

## 🔤 Local Fonts & Typography System

Zeitfresser uses a fully self-hosted font system:

- **Oswald** (400, 500, 700) for headings  
- **Roboto** (400, 500, 700) for body text  

Key improvements:

- No external font requests (Google Fonts removed)  
- Fonts served locally via optimized `.woff2` files  
- Critical font assets are **preloaded** for faster rendering  
- Consistent typography across all environments  
- Full control over font loading and rendering behavior  

The typography system is based on CSS variables and designed to be predictable, maintainable, and visually consistent.

## 🎨 CSS-Based Color System

Zeitfresser uses a fully static, CSS variable-driven color system.

All colors are defined using native CSS custom properties (`:root`) and applied consistently across the entire theme. This replaces traditional PHP-driven or dynamically generated styles with a simpler and more predictable approach.

## ⚡ Core Web Vitals

The theme is optimized for real-world performance:

- Stable layout with no unexpected shifts (CLS-safe)  
- Optimized Largest Contentful Paint (LCP)  
- Reduced render-blocking resources  
- Early font availability through preload strategy  

Even long-form articles render quickly and consistently.

## 📑 Floating Table of Contents

A core feature of the theme is its editorial-style floating Table of Contents.

- Automatically generated from headings  
- Positioned outside the main content flow  
- Highlights the active section  
- Includes a subtle progress indicator  
- Smooth scroll behavior  

Designed to enhance navigation without adding visual noise.

## ⚙️ Customization

The theme integrates cleanly with the WordPress Customizer.

- Enable/disable TOC globally  
- Configure heading thresholds for TOC visibility  
- Adjust layout behavior without adding complexity  

All options are intentionally minimal and focused.

## 🎨 Design Philosophy

Zeitfresser follows a strict philosophy:  
**clarity over decoration**.

- Minimal dark UI with subtle accent colors  
- Typography-driven hierarchy  
- Clean spacing instead of visual clutter  
- Focus on long-form readability  

The result is a calm, distraction-free reading experience.

## 🧹 Code Quality

The codebase has been systematically refactored:

- Legacy components removed  
- CSS conflicts minimized  
- Modular structure  
- No unnecessary abstractions  
- No technical debt patterns  

The theme is designed for long-term maintainability.

## 📱 Responsiveness

The layout adapts cleanly across devices.

- Desktop: full editorial experience  
- Mobile: simplified, focused layout  
- Feature-aware behavior (e.g. TOC disabled when not useful)  

All core functionality remains accessible.

## 📦 Installation

To install the theme:

1. Download or clone the repository  
2. Upload it to your WordPress installation: `/wp-content/themes/`
3. Activate it via: **Appearance → Themes**

## ⚡ Recommended Setup

For best performance:

- Enable caching (server or plugin)  
- Use a CDN for global delivery  
- Optimize existing media assets  

The theme is designed to perform well out of the box, but benefits from a modern hosting setup.

## 🛠 Development & Support

Zeitfresser is actively developed and designed to evolve.

For support or contributions:

- Join the <a href="https://ztfr.eu/matrix">Matrix Community</a>  
- Use the <a href="https://look.ztfr.eu/#/#support:ztfr.eu">Support Channel</a>  

## 📄 License

GPL v2 or later.

Originally based on the [Daisy Blog](https://wordpress.org/themes/daisy-blog/) theme, now heavily modified into an independent codebase.

## 💬 Final Note

Zeitfresser is built for developers and writers who value:

- performance  
- readability  
- clean engineering  

It avoids unnecessary complexity and focuses on doing a few things exceptionally well:

**presenting content clearly, loading fast, and staying maintainable.**
