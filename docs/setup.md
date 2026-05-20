# AidOrbit WordPress Setup

This guide explains how a WordPress site admin connects AidOrbit and publishes the first public volunteer portal pages.

## Who Should Use This

Use this guide if you manage a nonprofit WordPress site and need to publish live AidOrbit Missions, Program schedules, Mission details, and registration paths.

## Connect AidOrbit

1. Install and activate the AidOrbit plugin.
2. Open Settings > AidOrbit.
3. Enter the AidOrbit API base URL.
4. Enter the Mission Control URL used for volunteer sign-in and registration redirects.
5. Enter the AidOrbit organization ID.
6. Paste an AidOrbit API token scoped to the organization or Programs this site can publish.
7. Paste a webhook secret for cache invalidation.
8. Choose public cache TTLs.
9. Save settings.
10. Run Test connection.

API tokens and webhook secrets are write-only in the settings screen. They are stored in non-autoloaded WordPress options and are not printed back into admin HTML.

## Create Starter Pages

On Settings > AidOrbit, select Create starter pages. The plugin creates these draft pages:

- Volunteer Missions: an organization-wide Mission discovery page.
- Volunteer Dashboard: a sign-in entry point for Volunteers.

Review and publish the drafts when the page title, URL, and surrounding site content are ready.

## Webhook Cache Invalidation

Configure AidOrbit to POST public Mission and Program update notifications to:

`/wp-json/aidorbit/v1/webhook`

Send the shared secret in the `x-aidorbit-webhook-secret` header. A valid request clears the public data cache version so subsequent block renders fetch fresh data.

## Privacy Notes

The plugin does not store authoritative Volunteer profile, registration, eligibility, document, waiver, form, hour, or attendance data in WordPress. Public Mission and Program responses may be cached in WordPress transients. Personalized Volunteer dashboard widgets are not part of this initial implementation.
