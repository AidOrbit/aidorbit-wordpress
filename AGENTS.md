# AGENTS.md

## Required Startup Step For Codex Agents

Before doing any analysis, planning, or code changes in this repository:

1. Read the task and inspect the relevant project guidance, docs, manifests, scripts, and current files before editing.
2. Determine whether the user's request requires changes in `aidorbit-wordpress`, `aidorbit-web`, `goodnearby-web`, or any combination of the three before choosing files to edit. Do not assume the current repository is the only target.
3. Treat this repository as public at all times. Do not commit or add planning notes, testing artifacts, credentials, customer data, private implementation details, or other sensitive information.
4. If the request touches shared AidOrbit/GoodNearby/WordPress plugin flows, integrations, branding, navigation, embedded surfaces, documentation, deployment behavior, or cross-product user experience, inspect all affected repositories and make the required coordinated updates in each.
5. If only one or two repositories need edits, state that decision in the work summary and keep the unaffected repository or repositories unchanged.
6. Prefer existing project patterns and keep changes scoped to the requested behavior.

## Scope

This requirement applies to all work in this repository, including:
- bug fixes
- refactors
- feature implementation
- tests
- documentation changes
