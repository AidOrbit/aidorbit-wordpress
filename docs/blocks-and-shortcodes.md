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

Supported views are list, calendar, grid, and compact. Date controls support next 7 days, next 14 days, next 30 days, current month, next 90 days, and custom start/end dates when supported by the AidOrbit API.

## Mission Finder

Block: AidOrbit Mission Finder

Shortcode:

```text
[aidorbit_mission_finder program="food-pantry" limit="12"]
```

Use this when visitors need to search and browse public Missions.

Mission Finder supports visitor-facing keyword, location, date range, custom start/end dates, format, family-friendly, skill, role, Mission type, status, availability, distance, minimum-age, and eligibility filters. The filters use `aidorbit_keyword`, `aidorbit_location`, `aidorbit_range`, `aidorbit_start`, `aidorbit_end`, `aidorbit_virtual`, `aidorbit_family_friendly`, `aidorbit_skill`, `aidorbit_role`, `aidorbit_type`, `aidorbit_status`, `aidorbit_availability`, `aidorbit_distance`, `aidorbit_age`, and `aidorbit_eligibility` query parameters.

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

When AidOrbit returns public shift or role options, Mission Detail displays them as read-only summaries. It also displays public capacity, registration deadline, minimum age, Mission type, and directions links when those fields are returned. Registration still routes through AidOrbit so capacity, eligibility, duplicate registration, and waitlist rules are enforced at submission time.

Set `schema="yes"` to emit crawl-safe structured metadata for public Mission pages.

## Register CTA

Block: AidOrbit Register CTA

Shortcode:

```text
[aidorbit_register_button mission="12345" shift="678" role="usher"]
```

Use this when a page already describes the Mission and only needs a registration entry point. The CTA reflects open, full, waitlist, approval-required, requirements-blocked, canceled, and closed states.

## Add to Calendar

Block: AidOrbit Add to Calendar

Shortcode:

```text
[aidorbit_add_to_calendar mission="12345"]
```

Use this on Mission detail or campaign pages when visitors should save a public Mission to their calendar before or after registration. The block builds a Google Calendar handoff from the public Mission date, title, location, and registration URL; AidOrbit remains the system of record for registration, capacity, attendance, and schedule updates.

## Share Mission

Block: AidOrbit Share Mission

Shortcode:

```text
[aidorbit_share_mission mission="12345"]
```

Use this on Mission detail, campaign, and partner landing pages when visitors should send a public Mission to someone else. The block renders email, Facebook, LinkedIn, and X sharing links from the selected Mission and the current page URL. Use `shareUrl="https://example.org/volunteer"` when the share destination should be a campaign-specific page instead.

## Mission Location

Block: AidOrbit Mission Location

Shortcode:

```text
[aidorbit_mission_location mission="12345"]
```

Use this on Mission detail, campaign, and event logistics pages when the location needs to stand alone from the full Mission card. The block renders the public location name, address, and directions link when AidOrbit returns those fields. Virtual Missions show a virtual-location notice instead of exposing private connection details.

## Mission Countdown

Block: AidOrbit Mission Countdown

Shortcode:

```text
[aidorbit_mission_countdown mission="12345"]
```

Use this on campaign, event, and recruitment pages when the start time should be emphasized apart from the full Mission card. The block renders a days, hours, or minutes countdown from the public Mission start date and keeps registration, schedule changes, and attendance in AidOrbit.

## Organization Profile

Block: AidOrbit Organization Profile

Shortcode:

```text
[aidorbit_organization_profile]
```

Use this for a public organization header or about section. The block renders AidOrbit-approved public profile fields such as display name, tagline, public description, logo, cover image, website, donation link, public contact email, and social links. It does not expose private organization settings or Volunteer data.

## Donation CTA

Block: AidOrbit Donation CTA

Shortcode:

```text
[aidorbit_donation_cta]
```

Use this when a page needs a focused donation entry point. The block uses the organization donation URL returned by AidOrbit, or a `donateUrl` override when a campaign-specific destination is needed. Donation processing remains outside WordPress.

## Program Portal

Block: AidOrbit Program Portal

Shortcode:

```text
[aidorbit_program_portal program="food-pantry"]
```

Use this as a Program landing page with Program summary, contact routing, and upcoming Missions.

## Program Directory

Block: AidOrbit Program Directory

Shortcode:

```text
[aidorbit_program_directory view="grid" limit="12"]
```

Use this when a WordPress site needs a public directory of authorized Programs. The block uses the saved AidOrbit API key, organization ID, and optional allowed Program list; Program CTAs route to AidOrbit so authoritative Program pages and volunteer actions stay in Mission Control.

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

## Recommended Missions

Block: AidOrbit Recommended Missions

Shortcode:

```text
[aidorbit_recommended_missions]
```

Use this as a focused sign-in entry point for personalized Mission recommendations. Recommendations remain AidOrbit-hosted so profile matching, Program membership, requirements, and interest data are not rendered into public WordPress pages or shared caches.

## Team Registration

Block: AidOrbit Team Registration

Shortcode:

```text
[aidorbit_team_registration mission="12345" teamSize="8" minorConsent="yes"]
```

Use this for group, family, partner, or team registration entry points. When a Mission is selected, the block opens the AidOrbit team registration flow for that Mission; otherwise it opens the general team registration flow. Capacity, eligibility, roster details, and minor consent handling remain AidOrbit-hosted.

## QR Check-In

Block: AidOrbit QR Check-In

Shortcode:

```text
[aidorbit_qr_checkin mission="12345" shift="678" expires="2026-06-01T18:00:00Z"]
```

Use this on signs, QR-code landing pages, or onsite check-in pages. When a Mission is selected, the block redirects Volunteers into the AidOrbit check-in flow for that Mission. Without a Mission, it opens the general check-in flow.

## Kiosk Check-In

Block: AidOrbit Kiosk Check-In

Shortcode:

```text
[aidorbit_kiosk_checkin mission="12345" shift="678" kiosk="yes"]
```

Use this on staff-managed tablets or onsite kiosk pages. Attendance remains AidOrbit-hosted; WordPress only renders the entry point.

## Post-Mission Feedback

Block: AidOrbit Feedback Form

Shortcode:

```text
[aidorbit_feedback_form mission="12345" anonymous="yes" attendanceRequired="yes"]
```

Use this on thank-you pages, follow-up pages, or QR-code destinations after service. Feedback collection remains AidOrbit-hosted. `[aidorbit_post_mission_feedback]` is also registered as an alias for sites that prefer the more explicit name.

## Volunteer Recognition

Block: AidOrbit Volunteer Recognition

Shortcode:

```text
[aidorbit_volunteer_recognition]
```

Use this as a redirect-first surface for Volunteer recognition, badges, milestones, and appreciation details.

## Thank You

Block: AidOrbit Thank You

Shortcode:

```text
[aidorbit_thank_you]
```

Use this after registration, check-in, feedback, or campaign pages where Volunteers should continue into impact and recommended next steps.

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
