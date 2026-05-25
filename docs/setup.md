# AidOrbit WordPress Setup

This guide explains how a WordPress site admin connects AidOrbit and publishes the first public volunteer portal pages.

## Who Should Use This

Use this guide if you manage a nonprofit WordPress site and need to publish live AidOrbit Missions, Program schedules, Mission details, and registration paths.

## Connect AidOrbit

1. Install and activate the AidOrbit plugin.
2. Open Settings > AidOrbit.
3. Enter the AidOrbit API base URL. New installs default to the WordPress contract at `/mission-control/api/v1/wordpress`.
4. Enter the Mission Control URL used for volunteer sign-in and registration redirects.
5. Enter the AidOrbit organization ID.
6. Paste an AidOrbit API key scoped to the organization or Programs this site can publish. The key should include `missions.read`; add `reports.read` or `hours.read` when using Impact Counter blocks.
7. Optionally enter allowed Program IDs, one per line, when the site should publish only a subset of token-authorized Programs.
8. Paste a webhook secret for cache invalidation.
9. Choose public cache TTLs.
10. Choose an accent color that works with the active WordPress theme.
11. Choose whether aggregate analytics are enabled.
12. Save settings.
13. Run Test connection.

API tokens and webhook secrets are write-only in the settings screen. They are stored in non-autoloaded WordPress options and are not printed back into admin HTML.

## Create Starter Pages

On Settings > AidOrbit, select Create starter pages. The plugin creates these draft pages:

- Organization Profile: a public profile header with AidOrbit-approved organization details.
- Donate: a focused donation entry point using AidOrbit-approved donation routing.
- Volunteer Missions: an organization-wide Mission discovery page.
- Program Directory: a public directory of authorized Programs.
- Volunteer Dashboard: a sign-in entry point for Volunteers.
- My Schedule: a focused Volunteer schedule sign-in entry point.
- Recommended Missions: a focused sign-in entry point for personalized Mission recommendations.
- Account Security: a focused entry point for AidOrbit-hosted two-factor authentication setup and recovery.
- Team Registration: an AidOrbit-hosted group, family, partner, or team registration entry point.
- Volunteer Impact: a public impact counter page.
- Annual Report: a public annual impact and reporting page.
- Campaign Volunteering: a Mission collection page for campaigns.
- Partner Missions: a public-only partner embed page with referral support.
- Volunteer Check-In: a QR-friendly AidOrbit check-in entry point.
- Mission Reminders: a reminder signup and notification preferences entry point.
- Volunteer Thank You: post-service impact and recognition entry points.
- Program pages: one draft Program portal and schedule page for each configured allowed Program ID.

Review and publish the drafts when the page title, URL, and surrounding site content are ready.

## Block Editor Program Picker

When the plugin is connected, Program-aware blocks load authorized Programs into the block sidebar. Editors can select a Program from the list or paste a Program ID manually when troubleshooting or preparing content before the connection is available.

The picker is scoped by the saved AidOrbit token, organization ID, and optional allowed Program list. It does not expose API tokens to the browser.

Mission-aware blocks also load public authorized Missions. Selecting a Program narrows the Mission options shown for Mission Detail, Register CTA, Add to Calendar, Share Mission, Mission Location, Mission Countdown, Check-In, Feedback Form, and Requirements Checklist blocks.

Register CTA supports a modal registration mode. The modal loads the AidOrbit-hosted registration flow so capacity, duplicate prevention, waitlist, approval, schedule conflict, eligibility checks, and background-check consent remain enforced by AidOrbit. If JavaScript is unavailable, the CTA remains a normal link to AidOrbit.

## Two-Factor Authentication

Use the Account Security block or `[aidorbit_account_security]` shortcode when a WordPress site needs a volunteer-facing entry point for two-factor authentication.

The plugin redirects users to AidOrbit Account Settings for setup. AidOrbit generates the authenticator QR code, verifies the six-digit code, enforces organization staff 2FA policies, and remains the source of truth for enrollment state. The WordPress plugin does not store TOTP secrets, render setup QR codes, or call AidOrbit 2FA mutation endpoints.

Because the plugin only sends read requests to the AidOrbit WordPress API contract and redirects mutating workflows to AidOrbit, it does not add `Idempotency-Key` headers. If a future plugin feature directly calls AidOrbit POST, PUT, PATCH, or DELETE endpoints, that request must send a unique `Idempotency-Key` header.

## Webhook Cache Invalidation

Configure AidOrbit to POST public Mission and Program update notifications to:

`/wp-json/aidorbit/v1/webhook`

Preferred authorization is an HMAC-SHA256 signature of the raw request body in the `x-aidorbit-signature` header. The header may be either the raw hex digest or `sha256={digest}`.

For early integrations, the endpoint also accepts the shared secret in the `x-aidorbit-webhook-secret` header. A valid request clears the public data cache version so subsequent block renders fetch fresh data.

## Privacy Notes

The plugin does not store authoritative Volunteer profile, registration, eligibility, document, waiver, form, background-check consent, hour, attendance, feedback, or recognition data in WordPress. Public Mission and Program responses may be cached in WordPress transients. Personalized Volunteer dashboard, check-in, feedback, requirements, and recognition surfaces redirect to AidOrbit-hosted flows.

## Aggregate Analytics

When analytics are enabled, the plugin records daily aggregate counts for public block views, Mission detail views, registration starts, waitlist starts, and filter searches. It does not store Volunteer identity, browser identifiers, IP addresses, or request user agents in these counters.

Developers can listen to the `aidorbit_analytics_signal` action to forward privacy-safe conversion signals to an approved analytics system.

## Diagnostics

Settings > AidOrbit shows setup status, aggregate analytics, recent connection, cache, webhook, and API diagnostics. Diagnostic entries redact tokens, secrets, email addresses, phone numbers, and document-related fields before storing them in WordPress.

Use Download diagnostics to export a redacted JSON support bundle. The export indicates whether secrets are saved, but it does not include token or webhook secret values.
