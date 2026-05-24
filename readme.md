# AidOrbit

**Contributors:** aidorbit  
**Requires at least:** WordPress 6.4  
**Requires PHP:** 8.0  
**Stable tag:** 0.1.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Turn a WordPress site into a live, secure AidOrbit-powered volunteer portal.

## Description

AidOrbit embeds public Program schedules, Mission discovery, featured Missions, Mission details, registration CTAs, and Program portals from AidOrbit into WordPress. AidOrbit remains the source of truth for visibility, capacity, eligibility, registration, and volunteer privacy.

The plugin also supports optional allowed Program scoping, webhook cache invalidation, redacted diagnostics, and privacy-safe aggregate analytics.

## Features

### AidOrbit Connection and Administration

- Connects WordPress to the AidOrbit WordPress API contract.
- Stores the AidOrbit API base URL, Mission Control URL, organization ID, API token, webhook secret, cache TTLs, accent color, debug mode, and aggregate analytics setting.
- Stores API tokens and webhook secrets as non-autoloaded WordPress options.
- Keeps saved API tokens and webhook secrets write-only in the settings UI.
- Tests the AidOrbit connection from Settings > AidOrbit.
- Clears the public AidOrbit cache from Settings > AidOrbit.
- Shows setup status, connection status, webhook status, and cache status in the admin screen.
- Creates draft starter pages for common volunteer portal workflows.
- Supports an optional allowed Program list so a WordPress site can publish only selected token-authorized Programs.
- Provides redacted diagnostics for connection, API, webhook, and cache troubleshooting.
- Downloads a redacted JSON diagnostics bundle for support.
- Supports a configurable theme accent color for public plugin surfaces.
- Loads public and editor assets only through registered WordPress scripts and styles.
- Provides translations through the `aidorbit` text domain.

### Block Editor and Shortcode Publishing

- Registers an AidOrbit block category in the WordPress block inserter.
- Registers dynamic server-rendered blocks that fetch current AidOrbit data at render time.
- Provides shortcode fallbacks for every public block.
- Loads authorized Program options in the block sidebar.
- Loads public authorized Mission options in the block sidebar.
- Narrows Mission picker options by selected Program.
- Keeps AidOrbit API tokens out of browser-side editor payloads.

### Public Mission Discovery and Registration

- Publishes Program schedules with list, calendar, grid, and compact views.
- Publishes searchable Mission Finder pages.
- Supports Mission Finder filters for keyword, location, date range, custom dates, virtual/in-person format, family-friendly status, skill, role, Mission type, status, availability, distance, minimum age, and eligibility.
- Publishes curated featured Mission lists.
- Publishes public Mission detail pages.
- Filters out private, invite-only, internal, organization-only, and expired Missions from public rendering.
- Displays public Mission shift and role summaries when AidOrbit returns them.
- Displays public capacity, registration deadline, minimum age, Mission type, and directions links when AidOrbit returns them.
- Emits crawl-safe structured metadata for public Mission detail pages when enabled.
- Renders registration CTAs that route registration through AidOrbit.
- Reflects AidOrbit registration states including open, full, waitlist, approval-required, requirements-blocked, canceled, and closed.
- Supports AidOrbit-hosted modal registration while preserving a normal link fallback when JavaScript is unavailable.
- Starts waitlist and registration flows in AidOrbit so capacity, eligibility, duplicate registration, approval, schedule conflict, and waitlist rules remain authoritative.
- Renders Add to Google Calendar handoff links from public Mission details.
- Renders public Mission sharing links for email, Facebook, LinkedIn, and X.
- Supports campaign-specific share URL overrides.
- Renders standalone Mission location sections with directions links.
- Protects virtual Mission connection details by rendering a virtual-location notice instead of private connection data.
- Renders Mission countdown surfaces for campaign, event, and recruitment pages.

### Organization and Program Portal Content

- Publishes AidOrbit-approved public organization profile fields.
- Displays organization logo, cover image, website, donation link, public contact email, tagline, description, and social links when returned by AidOrbit.
- Publishes focused donation CTAs.
- Supports campaign-specific donation URL overrides.
- Publishes Program portals with Program summary, contact routing, and upcoming Missions.
- Publishes Program directories scoped to authorized Programs.
- Publishes contact Program staff entry points without exposing private staff details unless AidOrbit returns a public contact URL or label.
- Publishes organization-wide volunteer portal pages.

### Volunteer Hosted Workflow Entry Points

- Publishes Volunteer login entry points.
- Publishes redirect-first Volunteer dashboard surfaces.
- Renders AidOrbit-provided dashboard intent entries and messages when available.
- Publishes focused sign-in entry points for My Schedule, My Requirements, My Hours, and Recommended Missions.
- Publishes Account Security entry points for AidOrbit-hosted two-factor authentication setup and recovery.
- Keeps two-factor authentication enrollment, authenticator QR-code generation, verification, and disablement in AidOrbit.
- Publishes team, family, partner, and group registration entry points.
- Opens AidOrbit-hosted team registration flows for selected Missions or general team registration.
- Publishes QR Check-In entry points.
- Renders print-friendly QR check-in posters with scoped check-in URLs when enabled.
- Publishes kiosk check-in entry points for staff-managed devices.
- Publishes post-Mission feedback entry points.
- Registers `[aidorbit_post_mission_feedback]` as an alias for `[aidorbit_feedback_form]`.
- Publishes Volunteer recognition entry points for badges, milestones, and appreciation details.
- Publishes Thank You surfaces for post-registration, post-check-in, feedback, campaign, impact, and next-step flows.
- Publishes requirements checklist surfaces.
- Shows hosted requirement entry types and sign-in-required status labels when AidOrbit returns requirements intent metadata.
- Publishes Mission reminder and notification preference entry points.

### Impact, Reporting, Campaign, and Partner Surfaces

- Publishes public Impact Counter blocks for hours, volunteers, and Missions.
- Publishes Annual Report blocks with permission-safe public impact totals.
- Displays AidOrbit-hosted annual report metadata for attendance, retention, and service proof summaries where permitted.
- Publishes Program Metrics entry points for staff workflows.
- Routes staff to AidOrbit-hosted Program metrics for active Volunteers, upcoming shifts, open capacity, fill rate, no-show rate, pending approvals, and hours.
- Publishes partner, sponsor, corporate volunteer, and community calendar embeds.
- Propagates partner and referral metadata into AidOrbit links and workflow metadata.
- Publishes campaign landing pages for Mission collections such as holiday drives, disaster response, corporate service days, family-friendly campaigns, and recruiting pushes.

### Starter Pages

The plugin can create these draft starter pages:

- Organization Profile
- Donate
- Volunteer Missions
- Program Directory
- Volunteer Dashboard
- My Schedule
- Recommended Missions
- Account Security
- Team Registration
- Volunteer Impact
- Annual Report
- Campaign Volunteering
- Partner Missions
- Volunteer Check-In
- Mission Reminders
- Volunteer Thank You
- Program portal and schedule pages for each configured allowed Program ID

### Security, Privacy, and Data Handling

- Keeps AidOrbit as the source of truth for Mission, registration, eligibility, document, hours, attendance, feedback, recognition, and Volunteer profile data.
- Does not store authoritative Volunteer profile, registration, eligibility, document, waiver, form, hour, attendance, feedback, or recognition data in WordPress.
- Caches public Mission, Program, organization, impact, and intent responses in WordPress transients.
- Uses separate public cache and capacity cache TTLs.
- Invalidates cached public data through a cache version option.
- Accepts AidOrbit webhook cache invalidation at `/wp-json/aidorbit/v1/webhook`.
- Authorizes webhooks with HMAC-SHA256 signatures in `x-aidorbit-signature`.
- Supports shared-secret webhook authorization through `x-aidorbit-webhook-secret` for early integrations.
- Records webhook, API, cache, and connection diagnostics with token, secret, email, phone, document, and waiver redaction.
- Records aggregate analytics only when enabled.
- Tracks daily aggregate counts for public block views, Mission detail views, registration starts, waitlist starts, and filter searches.
- Does not store Volunteer identity, browser identifiers, IP addresses, or user agents in analytics counters.
- Exposes the `aidorbit_analytics_signal` action so approved analytics systems can receive privacy-safe conversion signals.
- Limits editor REST endpoints to users who can edit posts.
- Sanitizes API query parameters before remote requests.
- Uses bearer-token authenticated requests to AidOrbit.
- Sends only read requests to the AidOrbit WordPress API contract; WordPress-hosted entry points that change state redirect to AidOrbit, so the plugin does not generate `Idempotency-Key` headers for AidOrbit mutations.

## Blocks and Shortcodes

| Block | Shortcode |
| --- | --- |
| AidOrbit Program Schedule | `[aidorbit_program_schedule]` |
| AidOrbit Mission Finder | `[aidorbit_mission_finder]` |
| AidOrbit Featured Missions | `[aidorbit_featured_missions]` |
| AidOrbit Mission Detail | `[aidorbit_mission_detail]` |
| AidOrbit Register CTA | `[aidorbit_register_button]` |
| AidOrbit Add to Calendar | `[aidorbit_add_to_calendar]` |
| AidOrbit Share Mission | `[aidorbit_share_mission]` |
| AidOrbit Mission Location | `[aidorbit_mission_location]` |
| AidOrbit Mission Countdown | `[aidorbit_mission_countdown]` |
| AidOrbit Organization Profile | `[aidorbit_organization_profile]` |
| AidOrbit Donation CTA | `[aidorbit_donation_cta]` |
| AidOrbit Program Portal | `[aidorbit_program_portal]` |
| AidOrbit Program Directory | `[aidorbit_program_directory]` |
| AidOrbit Contact Program Staff | `[aidorbit_contact_program_staff]` |
| AidOrbit Organization Portal | `[aidorbit_org_portal]` |
| AidOrbit Volunteer Login | `[aidorbit_volunteer_login]` |
| AidOrbit Volunteer Dashboard | `[aidorbit_volunteer_dashboard]` |
| AidOrbit My Schedule | `[aidorbit_my_schedule]` |
| AidOrbit My Requirements | `[aidorbit_my_requirements]` |
| AidOrbit My Hours | `[aidorbit_my_hours]` |
| AidOrbit Recommended Missions | `[aidorbit_recommended_missions]` |
| AidOrbit Account Security | `[aidorbit_account_security]` |
| AidOrbit Team Registration | `[aidorbit_team_registration]` |
| AidOrbit QR Check-In | `[aidorbit_qr_checkin]` |
| AidOrbit Kiosk Check-In | `[aidorbit_kiosk_checkin]` |
| AidOrbit Feedback Form | `[aidorbit_feedback_form]` |
| AidOrbit Post-Mission Feedback | `[aidorbit_post_mission_feedback]` |
| AidOrbit Volunteer Recognition | `[aidorbit_volunteer_recognition]` |
| AidOrbit Thank You | `[aidorbit_thank_you]` |
| AidOrbit Requirements Checklist | `[aidorbit_requirements_checklist]` |
| AidOrbit Impact Counter | `[aidorbit_impact_counter]` |
| AidOrbit Annual Report | `[aidorbit_annual_report]` |
| AidOrbit Program Metrics | `[aidorbit_program_metrics]` |
| AidOrbit Partner Embed | `[aidorbit_partner_embed]` |
| AidOrbit Campaign Landing | `[aidorbit_campaign_landing]` |
| AidOrbit Mission Reminders | `[aidorbit_mission_reminders]` |

## Setup

1. Activate the plugin.
2. Open Settings > AidOrbit.
3. Enter the AidOrbit API base URL, Mission Control URL, organization ID, API key, public cache TTLs, and webhook secret. New installs default to `/mission-control/api/v1/wordpress`.
4. Test the connection.
5. Optionally create starter pages as drafts.
6. Add AidOrbit blocks to pages, or use the shortcode fallbacks.

Additional setup and publishing guidance is available in `docs/setup.md` and `docs/blocks-and-shortcodes.md`.

Settings > AidOrbit also includes redacted diagnostics for connection, API, webhook, and cache troubleshooting.

## Privacy

The plugin does not store authoritative Mission, registration, eligibility, document, hours, attendance, feedback, or Volunteer profile data in WordPress. Public block data may be cached in WordPress transients. Personalized Volunteer surfaces redirect to AidOrbit-hosted flows. Optional analytics store aggregate daily counts only.

## Changelog

### 0.1.0

- Initial MVP plugin foundation.
