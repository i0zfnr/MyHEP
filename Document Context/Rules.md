# StudentEdge Engineering Rules

Last updated: 2026-07-23

These rules apply to future code and UI changes unless a reviewed requirement explicitly replaces one.

## Product and Data Safety

1. Enforce authentication and authorization on the server; never trust hidden fields, route names, or client-side visibility.
2. Students may read or mutate only records linked to their current student identity.
3. Admin actions must pass the relevant scope middleware.
4. Hash passwords and tokens. Never log passwords, reset codes, session payloads, IC numbers, or provider secrets.
5. Validate all uploads by type and size, store them outside executable paths, and generate server-controlled names.
6. Write audit logs for destructive operations, password resets, privileged mode changes, payment decisions, QR changes, and other critical actions.
7. Use transactions and row locking where duplicate or concurrent writes would violate a business rule.
8. Back up data before migrations, bulk imports, bulk deletion, or schema changes.

## Laravel Implementation

1. Follow existing route, controller, middleware, Blade, and support-class patterns.
2. Move new non-trivial route logic into controllers instead of expanding large closures in `routes/web.php`.
3. Use Laravel validation or Form Requests; do not manually parse trusted-looking request strings.
4. Use parameter binding through Laravel's query builder; do not concatenate SQL.
5. Paginate large collections and select only required columns.
6. Avoid N+1 queries and add indexes for frequently searched, joined, or sorted fields.
7. Keep locale-visible strings in translation files.
8. Keep environment-specific values in configuration and `.env`; never commit real secrets.

## Database and Import Rules

1. Treat `StudentEdge.sql` as the original schema baseline and migrations as incremental history.
2. New schema changes require a reversible migration and documentation update.
3. Define foreign-key behavior deliberately; do not rely on accidental orphan records.
4. Large student imports must validate headers, normalize identifiers, detect duplicates, process in chunks, and return a useful result summary.
5. Do not load 50,000 rows into browser memory or a single unbounded PHP collection.
6. Test bulk deletion against an exact import batch, ID set, or explicit filter; never use an ambiguous broad delete.

## UI and Motion Rules

1. Maintain both light and dark themes.
2. Use the shared tokens and components in `resources/css/design-system.css`.
3. Keep Liquid Glass on navigation and overlays; repeated content cards must remain lightweight.
4. Use soft, reversible, spring-like transitions. Avoid linear primary motion and hard-coded animation delays.
5. All interactive controls must have a minimum 44px target.
6. Long names and translated labels must truncate or wrap without overlapping controls.
7. On student mobile pages, the topbar and Current Page header remain sticky. The sidebar, account menu, notifications, and dialogs must layer above them correctly.
8. The desktop student dashboard has no persistent sidebar; student modules do. Admin desktop pages retain their sidebar.
9. The shared Current Page header remains sticky on desktop so its notification, support, and account controls stay available while scrolling.
10. Preserve reduced-motion, reduced-transparency, contrast, keyboard, and focus behavior.
11. Do not add card entrance animations, pointer-tracked effects, or expensive repeated backdrop filters without mobile performance evidence.

## Change Discipline

1. Keep changes scoped to the requested behavior.
2. Preserve unrelated work in a dirty worktree.
3. Update these context files when product behavior, architecture, design rules, or schema changes materially.
4. Add or update tests in proportion to the risk and affected workflow.
5. Do not claim a production security guarantee; document remaining risk and required deployment controls.

## Required Verification

Run the checks relevant to the change:

```bash
php artisan test
php artisan route:list --except-vendor
php artisan migrate:status
php artisan view:cache
npm run build
composer audit
```

For responsive UI work, manually check mobile and desktop, light and dark themes, long content, overlay stacking, keyboard focus, and scrolling performance.
