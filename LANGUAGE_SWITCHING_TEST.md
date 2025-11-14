# Language Switching Test Guide

Use this guide to verify that the language switching and sidebar positioning are working correctly.

## Quick Test Steps

### 1. Test Default State (Arabic RTL)

**Action:** Open the application in a fresh browser session (or incognito mode)

**Expected Results:**
- ✅ Interface displays in **Arabic**
- ✅ Page direction is **RTL** (right-to-left)
- ✅ Sidebar is positioned on the **RIGHT** side
- ✅ Content area is on the **LEFT** side
- ✅ Text alignment is **right-aligned**
- ✅ Menu icon (hamburger) on the **LEFT** side of navbar

**Visual Check:**
```
┌─────────────────────────────────────────────────┐
│ [☰]              Navbar              [User] [🌐]│
├─────────────────────────┬───────────────────────┤
│    Main Content        │   📋 Sidebar (RIGHT)  │
│                        │   • Dashboard         │
│  ← Arabic text flows   │   • Products          │
│                        │   • Orders            │
└─────────────────────────┴───────────────────────┘
```

### 2. Switch to English (LTR)

**Action:** Click the language switcher (🌐) and select "🇬🇧 English"

**Expected Results:**
- ✅ Page reloads automatically
- ✅ Interface displays in **English**
- ✅ Page direction changes to **LTR** (left-to-right)
- ✅ Sidebar **moves** to the **LEFT** side
- ✅ Content area is now on the **RIGHT** side
- ✅ Text alignment is **left-aligned**
- ✅ Menu icon (hamburger) on the **LEFT** side of navbar

**Visual Check:**
```
┌─────────────────────────────────────────────────┐
│ [☰]              Navbar              [User] [🌐]│
├───────────────────────┬─────────────────────────┤
│  📋 Sidebar (LEFT)   │      Main Content       │
│   • Dashboard        │                         │
│   • Products         │   English text flows →  │
│   • Orders           │                         │
└───────────────────────┴─────────────────────────┘
```

### 3. Switch Back to Arabic

**Action:** Click language switcher again and select "🇸🇦 العربية"

**Expected Results:**
- ✅ Page reloads
- ✅ Interface returns to **Arabic**
- ✅ Sidebar **moves back** to the **RIGHT** side
- ✅ Layout returns to **RTL**

### 4. Test Persistence Across Pages

**Action:** After switching to English, navigate to:
- Products page
- Orders page
- Settings page

**Expected Results:**
- ✅ Language stays **English** on all pages
- ✅ Sidebar stays on the **LEFT** on all pages
- ✅ No layout glitches or jumps

### 5. Test Mobile Responsiveness

**Action:** Resize browser to mobile width (< 1200px)

**Expected Results (Arabic):**
- ✅ Sidebar becomes a slide-out drawer
- ✅ Drawer slides from **RIGHT** side
- ✅ Hamburger menu works

**Expected Results (English):**
- ✅ Sidebar becomes a slide-out drawer
- ✅ Drawer slides from **LEFT** side
- ✅ Hamburger menu works

### 6. Test Template Customizer Removal

**Action:** Look for the settings/gear icon on the screen

**Expected Results:**
- ✅ **No gear/settings button** visible anywhere
- ✅ No floating customizer panel
- ✅ Clean interface without extra buttons

### 7. Test Admin Language Setting Save

**Action (as Admin):**
1. Go to **Settings → Localization & Currency Settings**
2. Check current **Site Language** value
3. Switch language via navbar
4. Return to Settings page

**Expected Results:**
- ✅ Site Language setting automatically updates
- ✅ Matches the language you selected in navbar

## Detailed Visual Comparisons

### Arabic Layout (Default)
```
Navigation Bar
┌────────────────────────────────────────────────────────┐
│ [☰]  Search            🌐 English | العربية  👤 Admin  │
└────────────────────────────────────────────────────────┘

Main Layout
┌────────────────────────────────────────────────────────┐
│                                                         │
│  Content Area (Left)              Sidebar (Right) 📋   │
│  ┌─────────────────────┐         ┌────────────────┐   │
│  │                     │         │ 🏠 Dashboard   │   │
│  │  ← Text here flows  │         │ 📦 Products    │   │
│  │                     │         │ 🛒 Orders      │   │
│  │  this way (RTL)     │         │ 👥 Users       │   │
│  │                     │         │ ⚙️  Settings    │   │
│  └─────────────────────┘         └────────────────┘   │
│                                                         │
└────────────────────────────────────────────────────────┘
```

### English Layout
```
Navigation Bar
┌────────────────────────────────────────────────────────┐
│  [☰]  Search            🌐 العربية | English  👤 Admin│
└────────────────────────────────────────────────────────┘

Main Layout
┌────────────────────────────────────────────────────────┐
│                                                         │
│  📋 Sidebar (Left)              Content Area (Right)   │
│  ┌────────────────┐         ┌─────────────────────┐   │
│  │ 🏠 Dashboard   │         │                     │   │
│  │ 📦 Products    │         │  Text here flows →  │   │
│  │ 🛒 Orders      │         │                     │   │
│  │ 👥 Users       │         │  this way (LTR)     │   │
│  │ ⚙️  Settings    │         │                     │   │
│  └────────────────┘         └─────────────────────┘   │
│                                                         │
└────────────────────────────────────────────────────────┘
```

## Common Issues & Solutions

### Issue: Sidebar Not Moving
**Solution:**
1. Hard refresh the page (Ctrl + Shift + R)
2. Clear browser cache
3. Check browser console for errors

### Issue: Language Changes But Layout Doesn't
**Solution:**
1. Ensure `dir` attribute in HTML element is changing
2. Check browser dev tools → Elements → `<html>` tag
3. Verify `dir="rtl"` or `dir="ltr"` is present

### Issue: Template Customizer Still Visible
**Solution:**
1. Hard refresh (Ctrl + Shift + R)
2. Clear browser cache
3. Check if custom CSS is overriding our styles

### Issue: Sidebar Overlapping Content
**Solution:**
1. Check browser width (must be > 1200px for side-by-side layout)
2. Verify margin on `.layout-page` element
3. Check browser console for CSS conflicts

## Developer Tools Check

### Chrome DevTools:
1. Press F12 to open DevTools
2. Go to **Elements** tab
3. Find the `<html>` element
4. Verify attributes change when switching language:

**Arabic:**
```html
<html lang="ar" dir="rtl" class="light-style layout-navbar-fixed layout-menu-fixed">
```

**English:**
```html
<html lang="en" dir="ltr" class="light-style layout-navbar-fixed layout-menu-fixed">
```

### CSS Inspection:
1. Right-click on sidebar → Inspect
2. Look for `.layout-menu` class
3. Verify computed styles:

**Arabic:**
```css
.layout-menu {
    right: 0px;     /* Should be 0 */
    left: auto;     /* Should be auto */
}
```

**English:**
```css
.layout-menu {
    left: 0px;      /* Should be 0 */
    right: auto;    /* Should be auto */
}
```

## Success Criteria

All tests pass when:
- ✅ Default language is Arabic with RTL layout
- ✅ Sidebar positioned on RIGHT for Arabic
- ✅ Sidebar positioned on LEFT for English
- ✅ Smooth transitions when switching (0.3s)
- ✅ No template customizer button visible
- ✅ Responsive design works on mobile
- ✅ Language persists across navigation
- ✅ Admin language changes save to database

## Final Verification

Run through this quick checklist:

1. [ ] Fresh browser loads in Arabic RTL with sidebar RIGHT
2. [ ] Switch to English moves sidebar to LEFT
3. [ ] Switch back to Arabic moves sidebar to RIGHT
4. [ ] No gear/customizer button anywhere
5. [ ] Mobile menu works on small screens
6. [ ] Language persists across page navigation
7. [ ] No console errors
8. [ ] Smooth animations when switching

If all checkboxes are ✅, the implementation is working perfectly! 🎉
