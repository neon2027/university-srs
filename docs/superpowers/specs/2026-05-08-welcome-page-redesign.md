# Welcome Page Redesign — Spec

**Date:** 2026-05-08
**Status:** Approved

## Overview

Redesign `resources/views/welcome.blade.php` to a modern, typography-driven layout inspired by the Bicol University institutional design style. No icons. Clean, structured, human — not generic or AI-template-looking.

## Color Palette

| Role | Value |
|------|-------|
| Primary (Blue) | `#0089CB` |
| Accent (Orange) | `#FE8926` |
| Headline text | `#111111` |
| Body text | `#555555` |
| Muted text | `#888888` |
| Background | `#FFFFFF` |
| Light tint (stat bar bg) | `#F5FBFF` |
| Footer background | `#111111` |

## Typography

- Font: `font-sans` — resolves to Instrument Sans (set in `app.css` via `--font-sans`), already applied globally
- Headline: `font-black` (900 weight), tight letter-spacing, large size
- Section labels: `uppercase`, `font-extrabold`, small with letter-spacing
- Body text: regular weight, relaxed line-height

## Page Sections (top to bottom)

### 1. Navbar
- Background: `#0089CB`
- Left: "BICOL UNIVERSITY — SERVICE REQUEST SYSTEM" in white, uppercase, bold, letter-spaced
- Right: "Log in" and "Register" links in white (link to Fortify auth routes)
- No logo icon — text-only branding

### 2. Hero Headline
- Full-width, white background
- Large bold headline: **"Get the help you need — fast."**
- Subtext: one sentence description of the system
- Bottom border: `border-b-2 border-black` (thick black rule separating hero from content)

### 3. Two Action Columns
- Side-by-side columns separated by a vertical border
- **Left column** — "New Request"
  - Label: `#0089CB`, uppercase
  - Description text
  - Primary CTA button: `bg-[#0089CB] text-white` — "Submit Now" → links to request form (auth-required)
- **Right column** — "Track a Ticket"
  - Label: gray, uppercase
  - Description text
  - Secondary CTA button: `border border-[#0089CB] text-[#0089CB]` — "Track Now" → links to ticket tracking page

### 4. Stat Line
- Light blue tint background (`#F5FBFF`), subtle bottom border
- Inline stats separated by dot separators: `10+ departments` · `500+ requests resolved` · `Avg. response: 24 hrs`
- Small text, muted — serves as social proof

### 5. How It Works
- White background, bottom border
- Section label: `HOW IT WORKS` — uppercase, bold, with `border-l-4 border-[#0089CB]` left accent
- Three numbered steps (01, 02, 03) — number in `#0089CB` except step 03 in `#FE8926`
- Each step: bold title + 1–2 sentence description
- Steps separated by thin horizontal rules (indented past the number)
- **No icons**

### 6. Departments & Services
- Light gray background (`#FAFAFA`), bottom border
- Section label: `DEPARTMENTS & SERVICES` — uppercase, bold, with `border-l-4 border-[#FE8926]` left accent
- 2-column CSS grid of department cards
  - Each card: white background, `border-t-2 border-[#0089CB]`, department name + short description
  - Last card: `border-t-2 border-[#FE8926]`, "+ View all departments →" link in `#0089CB`
- Departments listed:
  1. Information Technology Office — Systems, networks, hardware, software
  2. Physical Plant Office — Maintenance, repairs, facilities
  3. Registrar's Office — Documents, certifications, records
  4. Library Services — Resources, access, research support
  5. Finance Office — Payments, billing, scholarships
  6. (View all card)

### 7. Footer
- Background: `#111111`
- Left: University name + "Service Request System" + address
- Right: **Technical Support** label in `#FE8926`, then email, phone, and office hours
  - Email: `itsupport@bicol-u.edu.ph`
  - Phone: `(052) 820-0000 loc. 101`
  - Hours: Mon – Fri, 8:00 AM – 5:00 PM
- Bottom strip: copyright left, Privacy Policy + Terms of Use links right

## Implementation Notes

- File to edit: `resources/views/welcome.blade.php`
- Use Tailwind CSS v4 utility classes throughout — no custom CSS
- Use `flux:button` and `flux:badge` only if they produce the exact styling needed; otherwise use plain HTML elements with Tailwind classes (the design relies on custom colors not in the default Flux palette)
- Page is standalone (no layout wrapper) — keep the existing `<!DOCTYPE html>` structure
- Responsive: stack the two action columns (`flex-col` on mobile, `flex-row` on `md:`) and the department grid (`grid-cols-1` on mobile, `grid-cols-2` on `md:`)
- No auth guard on this page — it is public
- CTA buttons link to named routes: `route('login')` for Log in, `route('register')` for Register
- "Submit Now" and "Track Now" link to `route('login')` as placeholders — the actual request and tracking routes do not exist yet (out of scope)

## Out of Scope

- Actual department list page (just the "View all" link placeholder)
- Ticket request form or tracking page
- Any backend changes
