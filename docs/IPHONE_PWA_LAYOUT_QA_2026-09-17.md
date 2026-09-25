# MyHEP iPhone layout QA — 17 September 2026

## Outcome

Signed in to the live MyHEP site using the supplied account and exercised its Student Mode and PBT Staff shortcuts. Confirmed layout defects against the live pages, then corrected and verified the local application. Changes are not deployed or committed.

### Corrected defects

| Area | Cause | Fix |
| --- | --- | --- |
| Student documents and scholarships | A global `!important` table rule forced mobile cards to 680px wide. | Restore fluid card widths at each module's existing breakpoint; retain scrolling for genuine tables. |
| Student program cards | A 260px minimum exceeded the available card width on small phones. | Allow the content to shrink and long text to wrap, retaining the preferred desktop flex basis. |
| Staff dashboard header | Header copy and actions remained in one row at small widths. | Stack the header and actions below 640px. |
| Staff dashboard program board | The recent-program table's intrinsic width expanded the entire grid. | Give the grid a zero-minimum track and allow its children to shrink. The table remains independently scrollable. |
| Staff header text | The non-glass theme's generic muted-text selector overrode the hero's light text. | Lower the generic selector's specificity. Confirmed hero paragraph color is `rgb(255, 250, 243)`. |
| Monthly report chart | Fixed bar widths imposed a minimum that overflowed at 320px. | Allow chart columns and bars to shrink. |
| Student and staff tab bars | The bottom offset subtracted from the home-indicator inset; the width expression was malformed. | Respect the full bottom safe area, use independent left/right insets, and cap the centered bar at 420px. |
| Student More menu | Height was capped only against a viewport percentage. | Also cap against the space remaining above the tab bar and below the top safe area. |
| Student and staff scanners | Fixed viewport-relative framing overlapped controls on very short screens. | Size and position the frame using available height and safe-area insets below 600px height. |

## Browser coverage

Browser rendering used the Codex in-app Chromium browser. Portrait dimensions tested, in CSS pixels:

`320×480`, `320×568`, `375×667`, `375×812`, `390×844`, `393×852`, `402×874`, `414×736`, `414×896`, `420×912`, `428×926`, `430×932`, `440×956`.

These represent iPhone screen-size families, not a claim of testing every physical model or iOS release.

### Portrait matrix

Each of these 21 pages was checked at the 13 portrait dimensions in light and dark themes: 546 page/size/theme combinations. Detected failures were corrected and the affected cases rerun. Checks measured rendered geometry and unintended overflow; intentionally scrollable tables/action strips and hidden drawers were excluded.

- Student: dashboard, programs, offenses, vehicle stickers, movements, discipline announcements, rules, documents, profile, report problem, settings, scholarships, scholarship announcements.
- Staff: dashboard, program list, program creation form, existing program detail, monthly report, AI Helper, profile, settings.

Additional checks:

- Student dashboard, programs, documents, scholarships, profile and settings in browser and simulated PWA shells at `568×320`, `667×375`, `736×414`, `844×390`, `956×440`, Android-sized `412×915`, and desktop `1440×900`.
- Staff dashboard, monthly report and AI Helper at the same additional sizes in both themes.
- Both scanner screens at all 13 portrait sizes and five landscape sizes. Verified that the frame clears the header and bottom controls. No QR transaction was submitted.
- Simulated portrait insets of top 59px/bottom 34px and landscape insets of left/right 59px/bottom 21px. Student scanner frame, navigation and More-sheet geometry were checked with these values. Staff navigation was checked with a 34px bottom inset.
- Visual inspection of the student dashboard, documents, scholarships, More sheet, staff dashboard, and scanners. Opened the staff sidebar. Confirmed the documents' Download action remains reachable by scrolling.

The temporary local harness loaded actual authenticated pages in an iframe. It applied the existing `pwa-standalone` / `has-student-bottom-nav` classes and simulated CSS safe-area values. It was removed after testing. This verifies layout rules, not native Safari, the actual display-mode media feature, installation, camera hardware, keyboard behavior, push delivery, or every possible populated record/form state. Real-device Safari/PWA validation remains outstanding.

## Separate issue found and resolved locally

The staff dashboard could return HTTP 500 after its initial successful load when a cached `Illuminate\\Support\\Collection` was restored as an incomplete object before the Blade view called `isEmpty()` on `programDashboard['recent']`.

`buildStaffProgramDashboard()` now caches recent program rows as plain arrays, reconstructs the collection after reading the cache, and uses a versioned key to bypass stale serialized entries. `clearProgramCaches()` clears both the current and legacy staff dashboard keys. Regression coverage verifies the initial render and a cache-hit render, with cached rows remaining arrays.

Validation after the fix: `php artisan test --compact --filter=LecturerDashboardTest` passed (2 tests, 12 assertions). The change is in the local working tree only; the live host was not modified or redeployed. The live browser session was restored to System Admin mode.

## Project validation

- `npm run build`: passed after the final CSS edits.
- `php artisan view:cache`: passed.
- `php artisan test --compact --filter=PwaAssetsTest`: 2 tests passed, 13 assertions.
- `git diff --check -- resources/css`: passed.

Existing changes under `.tmp/node_modules` were left untouched. No student/staff records, approvals, uploads, or account credentials were modified.
