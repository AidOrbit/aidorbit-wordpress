# AidOrbit Blocks and Shortcodes

AidOrbit blocks are dynamic blocks. They render from AidOrbit API responses and do not store authoritative Mission data in post content.

In the block inserter, AidOrbit blocks appear under the AidOrbit category.

## Program Schedule

Block: AidOrbit Program Schedule

Shortcode:

```text
[aidorbit_program_schedule program="food-pantry" view="grid" range="30d" limit="12"]
```

Use this on a Program page to show upcoming public Missions for a specific Program.

Supported views are list, calendar, grid, and compact.

## Mission Finder

Block: AidOrbit Mission Finder

Shortcode:

```text
[aidorbit_mission_finder program="food-pantry" limit="12"]
```

Use this when visitors need to search and browse public Missions.

Mission Finder supports visitor-facing keyword, location, date range, format, family-friendly, skill, minimum-age, and eligibility filters. The filters use `aidorbit_keyword`, `aidorbit_location`, `aidorbit_range`, `aidorbit_virtual`, `aidorbit_family_friendly`, `aidorbit_skill`, `aidorbit_age`, and `aidorbit_eligibility` query parameters.

## Featured Missions

Block: AidOrbit Featured Missions

Shortcode:

```text
[aidorbit_featured_missions program="food-pantry" layout="grid" limit="3"]
```

Use this on home pages, Program pages, and campaign pages where a short curated list is more useful than full discovery.

## Mission Detail

Block: AidOrbit Mission Detail

Shortcode:

```text
[aidorbit_mission_detail mission="12345"]
```

Use this on a dedicated Mission page. Private, invite-only, internal, organization-only, or expired Missions are not rendered for public display.

When AidOrbit returns public shift or role options, Mission Detail displays them as read-only summaries. Registration still routes through AidOrbit so capacity, eligibility, duplicate registration, and waitlist rules are enforced at submission time.

## Register CTA

Block: AidOrbit Register CTA

Shortcode:

```text
[aidorbit_register_button mission="12345" shift="678" role="usher"]
```

Use this when a page already describes the Mission and only needs a registration entry point. The CTA reflects open, full, waitlist, approval-required, requirements-blocked, canceled, and closed states.

## Program Portal

Block: AidOrbit Program Portal

Shortcode:

```text
[aidorbit_program_portal program="food-pantry"]
```

Use this as a Program landing page with Program summary, contact routing, and upcoming Missions.

## Contact Program Staff

Block: AidOrbit Contact Program Staff

Shortcode:

```text
[aidorbit_contact_program_staff program="food-pantry"]
```

Use this when a page needs a focused contact path without rendering a full Program portal. Contact routing comes from AidOrbit Program portal data, and the block does not expose private staff contact details unless AidOrbit returns a public contact URL or label.

## Organization Portal

Block: AidOrbit Organization Portal

Shortcode:

```text
[aidorbit_org_portal view="grid" limit="12"]
```

Use this as the organization-wide volunteer entry page.

## Volunteer Login

Block: AidOrbit Volunteer Login

Shortcode:

```text
[aidorbit_volunteer_login redirect="https://example.org/volunteer-dashboard"]
```

Use this as the sign-in entry point for Volunteers. Initial dashboard widgets are redirect-first and remain AidOrbit-hosted.

## Volunteer Dashboard

Block: AidOrbit Volunteer Dashboard

Shortcode:

```text
[aidorbit_volunteer_dashboard]
```

Use this as a redirect-first dashboard landing surface. It links Volunteers to AidOrbit-hosted schedule, requirements, hours, and recommendation views, so personalized data is not rendered into public WordPress pages or shared caches.

## My Schedule

Block: AidOrbit My Schedule

Shortcode:

```text
[aidorbit_my_schedule]
```

Use this as a focused sign-in entry point for upcoming Mission schedule details.

## My Requirements

Block: AidOrbit My Requirements

Shortcode:

```text
[aidorbit_my_requirements]
```

Use this as a focused sign-in entry point for waivers, forms, training, and other readiness steps.

## My Hours

Block: AidOrbit My Hours

Shortcode:

```text
[aidorbit_my_hours]
```

Use this as a focused sign-in entry point for hours and proof-of-service details.

## Requirements Checklist

Block: AidOrbit Requirements Checklist

Shortcode:

```text
[aidorbit_requirements_checklist mission="12345"]
```

Use this to show public Mission requirement summaries when AidOrbit returns them. Personalized completion status remains AidOrbit-hosted and is reached through the requirements CTA.

## Impact Counter

Block: AidOrbit Impact Counter

Shortcode:

```text
[aidorbit_impact_counter program="food-pantry" range="year" metrics="hours,volunteers,missions"]
```

Use this to display permission-safe public impact totals. The supported MVP metrics are hours, volunteers, and Missions.
