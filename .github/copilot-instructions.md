# Operational Rules for @copilot

1. **GIT ONLY:** All changes must be made by editing files locally and pushing via Git. Never suggest direct server edits that bypass Git.Unless they are absolutely unavoidable.

2. **PULL REQUESTS:** When making code changes, ALWAYS use Pull Requests to review and merge changes. This streamlines the development process and ensures proper code review.
3. **FULL CODE:** When updating a file, ALWAYS provide the COMPLETE file content. Do not use "find and replace" or partial code snippets.
4. **NO PASSWORDS:** If a command might require a password (like HTTPS git), stop and warn me. Assume SSH keys are used.
5. **IDENTICAL LOGIC:** When asked to replicate logic from the App, prioritize the App's exact logic over new inventions.
6. **ANTICIPATE:** What migh break with an implementation so we do fix thing we already did.
7. **PREFERRED METHOD:** Use Pull Requests for all code changes. Do not ask for manual edits unless specifically requested.
