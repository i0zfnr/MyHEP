# StudentEdge Design System

Last updated: 2026-07-23

## Design Intent

StudentEdge is an operational student-affairs product. It should feel calm, premium, fast, and trustworthy. The interface combines a warm neutral base, champagne-gold identity, restrained teal success states, and Apple-inspired layered glass for navigation and overlays.

## Core Principles

1. Prioritize scanning and repeated work over decorative presentation.
2. Use Liquid Glass selectively for navigation, menus, sheets, dialogs, and transient controls.
3. Keep content cards lightweight so scrolling remains responsive on phones.
4. Preserve clear hierarchy in both light and dark themes.
5. Never let decoration reduce contrast, obscure text, or interfere with interaction.

## Color Direction

| Role | Preferred value |
| --- | --- |
| Primary gold | `#C8A96A` |
| Light gold | `#E7D3A8` |
| Dark gold | `#8B6A34` |
| Champagne | `#F6E7C8` |
| Success teal | `#28686C` |
| Success dark | `#1F5559` |

Use design tokens from `resources/css/design-system.css`; do not scatter replacement hex values through page templates. Red remains reserved for errors or destructive actions. Gold identifies the product and primary actions but should not dominate every surface.

## Materials and Depth

- Navigation glass uses transparency, strong backdrop blur, saturation, a bright top rim, a subtle bottom rim, and soft multi-layer shadows.
- Overlay glass must remain readable over complex content in light and dark themes.
- Content cards should use a stable surface, 1px border, small radius, and restrained elevation.
- Do not add per-card reflection sweeps, pointer-tracked lighting, or large hover lifts to scrolling lists.
- Respect `prefers-reduced-motion`, `prefers-reduced-transparency`, and increased contrast preferences.

## Layout Behavior

### Mobile student shell

- The topbar remains sticky and contains hamburger, brand, notification, and account controls.
- The Current Page header remains sticky directly below the topbar.
- The overlay sidebar and its backdrop must render above both sticky headers.
- Long account names truncate with an ellipsis; fixed controls and avatars never shrink.
- The account menu is a top-level overlay so glass-container clipping cannot hide it.
- Bottom navigation order is Home, Fines, Scan QR, Aid, More.
- Scan QR is the central primary action.

### Desktop student shell

- The student dashboard has no persistent sidebar and uses the full workspace.
- Student module pages show the normal persistent sidebar.

### Admin shell

- Admin pages retain the persistent desktop sidebar.
- The desktop Current Page header remains pinned while the workspace content scrolls.
- Tables and filters should be dense enough for operational work without compromising touch and keyboard access.

## Typography and Content

- Use Plus Jakarta Sans through the existing layout.
- Use compact headings inside cards and operational panels.
- Do not scale type directly with viewport width.
- Use sentence case for actions and headings unless an existing data label requires uppercase.
- Long titles and names must wrap safely or truncate deliberately; they must never overlap adjacent controls.
- Support English and Malay without fixed-width assumptions.

## Interaction and Motion

- Minimum interactive target: 44 by 44 CSS pixels.
- Primary transitions use interruptible, spring-like cubic-bezier curves defined by system motion tokens.
- Keep transforms spatially logical and reversible.
- Avoid rigid delays, timer-driven layout changes, and animations that replay during scrolling.
- Hover elevation is desktop-only and subtle; touch interfaces use pressed feedback.
- Notification motion is the quality reference for menus, sheets, and dialogs.

## Component Rules

- Use icons for familiar actions and provide accessible labels or tooltips.
- Do not nest decorative cards inside cards.
- Keep cards at 8px radius or less unless the established component explicitly uses a larger overlay radius.
- Inputs, buttons, menus, and tables must expose visible focus states.
- Empty states should explain the absence of data without showing broken table boxes.
- Sticky elements must reserve their own space and must not cover page content.

## Verification

For UI changes, run:

```bash
php artisan view:cache
npm run build
```

Then verify light and dark themes at approximately 375px, 412px, 768px, and desktop widths. Check long names, long translated labels, sidebar layering, account menus, notification overlays, and scrolling performance.
