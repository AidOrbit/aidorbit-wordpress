# AidOrbit Blocks and Shortcodes

AidOrbit blocks are dynamic blocks. They render from AidOrbit API responses and do not store authoritative Mission data in post content.

## Program Schedule

Block: AidOrbit Program Schedule

Shortcode:

```text
[aidorbit_program_schedule program="food-pantry" view="grid" range="30d" limit="12"]
```

Use this on a Program page to show upcoming public Missions for a specific Program.

## Mission Finder

Block: AidOrbit Mission Finder

Shortcode:

```text
[aidorbit_mission_finder program="food-pantry" limit="12"]
```

Use this when visitors need to search and browse public Missions.

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
