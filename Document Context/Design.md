# StudentEdge Design System

Last updated: 2026-08-14

## Design Intent

StudentEdge is an operational student-affairs product. It should feel calm, premium, fast, and trustworthy. The interface combines a warm neutral base, champagne-gold identity, restrained teal success states, and Apple-inspired layered glass for navigation and overlays.

## Core Principles

1. Prioritize scanning and repeated work over decorative presentation.
2. Use Liquid Glass selectively for navigation, menus, sheets, dialogs, transient controls, and workspace hero cards.
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
- Student module Current Page headers remain below the topbar; the dashboard omits the duplicate strip to preserve vertical space.
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
- Lenis smooths wheel input in the main/document scroll context only. Touch remains native, and nested tables, notifications, filters, scanners, cropper controls, dialogs, and forms keep native scrolling.
- Long movement feeds use bounded rendering so the visible polish does not grow the DOM without limit.

## Component Rules

- Use icons for familiar actions and provide accessible labels or tooltips.
- Do not nest decorative cards inside cards.
- Keep cards at 8px radius or less unless the established component explicitly uses a larger overlay radius.
- Inputs, buttons, menus, and tables must expose visible focus states.
- Empty states should explain the absence of data without showing broken table boxes.
- Sticky elements must reserve their own space and must not cover page content.

## Appearance Settings

- Theme and locale save automatically.
- Beta accent themes are available only to students and System Admins: `gold`, `candy_blue`, `lavender`, `orchid`, and `violet`.
- Accent colors may style identity and primary actions, but semantic success, warning, error, and destructive colors remain invariant.
- Module templates (e.g. Program Management workspace) must check session identity (`student` or `system_admin`) before allowing custom accent variables (`var(--se-primary)`); non-system admin staff roles are locked to the default brand gold (`#C8A96A`).
- The canonical setting name is **Live Glass Transparency**. It controls surface opacity from 10% to 65% in 1% increments and saves automatically.
- Keep the accepted Apple-inspired control design: compact live-preview row, subtle percentage badge, thin accent track, small solid/clear endpoint icons, and a restrained frosted capsule thumb.
- Do not add nested decorative panels around the slider or expand the control into a large showcase.

## PWA Installation

- The welcome page exposes Android Install and iPhone Add to Home Screen choices.
- Android uses the native browser prompt when available and otherwise explains Chrome's Install app/Add to Home screen menu.
- iPhone guidance must direct users to Safari Share -> Add to Home Screen; browsers cannot invoke that native action directly.
- Installed-mode launch treatment runs once per app session and respects `prefers-reduced-motion`.

## Operational Components

- Active Visitors is a dense System Admin table with search, account role, IP, user agent, login time, and last activity. Copy must not imply exact real-time human presence.
- Global student deletion belongs in a clearly separated danger zone with the exact phrase `DELETE ALL STUDENTS`, an irreversible-action warning, and System Admin-only authorization.
- Student list rows show a compact circular profile photo with initials fallback.
- Authenticated problem reporting uses the normal application shell; anonymous reporting retains the standalone public layout.
- Program Management workspace views (`admin.programs.*`) use Liquid Glass cards and hero banners, 44px min-height touch targets, light/dark theme badge adaptations, and role-restricted accent color authorization.
- Monthly analytics use accent-led KPI cards with a left status edge, compact icons,
  restrained elevation, and stable content surfaces. Do not add decorative corner
  circles or curves inside cards. Module headings remain directly on the page
  background instead of being nested inside another card.
- Trend, status, and empty-state panels must share the active role accent, remain
  readable in dark mode, and fall back to flat, shadow-free A4 print styling.

## Localization

- Every user-facing static label must use Laravel translation calls and exist in
  both `lang/en.json` and `lang/ms.json`.
- Malay copy must be complete Malay; avoid mixed phrases such as `Rekod Scholarship`,
  `Kesalahan Unpaid`, or `Permohonan Pending`.
- Preserve Laravel placeholders exactly across locales.
- Run `tools/sync_translation_catalogues.py` after adding or changing interface copy.
  Use `tools/translate_missing_catalogue_values.py` only for values listed as unresolved,
  then manually review institutional and workflow terminology.
- Automated Blade localization must not rewrite JavaScript, CSS, Blade expressions,
  route names, translation namespaces, or stored database values.

## Verification

For UI changes, run:

```bash
php artisan view:cache
npm run build
```

Then verify light and dark themes at approximately 375px, 412px, 768px, and desktop widths. Check long names, long translated labels, sidebar layering, account menus, notification overlays, and scrolling performance.
