=== AidOrbit ===
Contributors: aidorbit
Requires at least: 6.4
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn a WordPress site into a live, secure AidOrbit-powered volunteer portal.

== Description ==

AidOrbit embeds public Program schedules, Mission discovery, featured Missions, Mission details, registration CTAs, and Program portals from AidOrbit into WordPress. AidOrbit remains the source of truth for visibility, capacity, eligibility, registration, and volunteer privacy. The plugin also supports optional allowed Program scoping, webhook cache invalidation, redacted diagnostics, and privacy-safe aggregate analytics.

Initial MVP surfaces:

* AidOrbit Program Schedule block and `[aidorbit_program_schedule]`
* AidOrbit Mission Finder block and `[aidorbit_mission_finder]`
* AidOrbit Featured Missions block and `[aidorbit_featured_missions]`
* AidOrbit Mission Detail block and `[aidorbit_mission_detail]`
* AidOrbit Register CTA block and `[aidorbit_register_button]`
* AidOrbit Program Portal block and `[aidorbit_program_portal]`
* AidOrbit Program Directory block and `[aidorbit_program_directory]`
* AidOrbit Contact Program Staff block and `[aidorbit_contact_program_staff]`
* AidOrbit Organization Portal block and `[aidorbit_org_portal]`
* AidOrbit Volunteer Login block and `[aidorbit_volunteer_login]`
* AidOrbit Volunteer Dashboard block and `[aidorbit_volunteer_dashboard]`
* AidOrbit My Schedule block and `[aidorbit_my_schedule]`
* AidOrbit My Requirements block and `[aidorbit_my_requirements]`
* AidOrbit My Hours block and `[aidorbit_my_hours]`
* AidOrbit Recommended Missions block and `[aidorbit_recommended_missions]`
* AidOrbit Team Registration block and `[aidorbit_team_registration]`
* AidOrbit QR Check-In block and `[aidorbit_qr_checkin]`
* AidOrbit Kiosk Check-In block and `[aidorbit_kiosk_checkin]`
* AidOrbit Feedback Form block and `[aidorbit_feedback_form]`
* AidOrbit Post-Mission Feedback block and `[aidorbit_post_mission_feedback]` alias
* AidOrbit Volunteer Recognition block and `[aidorbit_volunteer_recognition]`
* AidOrbit Thank You block and `[aidorbit_thank_you]`
* AidOrbit Requirements Checklist block and `[aidorbit_requirements_checklist]`
* AidOrbit Impact Counter block and `[aidorbit_impact_counter]`

== Setup ==

1. Activate the plugin.
2. Open Settings > AidOrbit.
3. Enter the AidOrbit API base URL, Mission Control URL, organization ID, API key, public cache TTLs, and webhook secret. New installs default to `/mission-control/api/v1/wordpress`.
4. Test the connection.
5. Optionally create starter pages as drafts.
6. Add AidOrbit blocks to pages, or use the shortcode fallbacks.

Additional setup and publishing guidance is available in `docs/setup.md` and `docs/blocks-and-shortcodes.md`.
Settings > AidOrbit also includes redacted diagnostics for connection, API, webhook, and cache troubleshooting.

== Privacy ==

The plugin does not store authoritative Mission, registration, eligibility, document, hours, attendance, feedback, or Volunteer profile data in WordPress. Public block data may be cached in WordPress transients. Personalized Volunteer surfaces redirect to AidOrbit-hosted flows. Optional analytics store aggregate daily counts only.

== Changelog ==

= 0.1.0 =
* Initial MVP plugin foundation.
