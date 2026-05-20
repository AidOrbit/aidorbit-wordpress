=== AidOrbit ===
Contributors: aidorbit
Requires at least: 6.4
Requires PHP: 8.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn a WordPress site into a live, secure AidOrbit-powered volunteer portal.

== Description ==

AidOrbit embeds public Program schedules, Mission discovery, featured Missions, Mission details, registration CTAs, and Program portals from AidOrbit into WordPress. AidOrbit remains the source of truth for visibility, capacity, eligibility, registration, and volunteer privacy.

Initial MVP surfaces:

* AidOrbit Program Schedule block and `[aidorbit_program_schedule]`
* AidOrbit Mission Finder block and `[aidorbit_mission_finder]`
* AidOrbit Featured Missions block and `[aidorbit_featured_missions]`
* AidOrbit Mission Detail block and `[aidorbit_mission_detail]`
* AidOrbit Register CTA block and `[aidorbit_register_button]`
* AidOrbit Program Portal block and `[aidorbit_program_portal]`

== Setup ==

1. Activate the plugin.
2. Open Settings > AidOrbit.
3. Enter the AidOrbit API base URL, Mission Control URL, organization ID, API token, public cache TTLs, and webhook secret.
4. Test the connection.
5. Add AidOrbit blocks to pages, or use the shortcode fallbacks.

== Privacy ==

The plugin does not store authoritative Mission, registration, eligibility, document, hours, or Volunteer profile data in WordPress. Public block data may be cached in WordPress transients. Personalized Volunteer surfaces are intentionally not included in this initial implementation.

== Changelog ==

= 0.1.0 =
* Initial MVP plugin foundation.
