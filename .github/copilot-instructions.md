# Operational Rules for @copilot

1. **GIT ONLY:** All changes must be made by editing files locally and pushing via Git. Never suggest direct server edits that bypass Git.Unless they are absolutely unavoidable.

2. **PR PREFERRED:** For all code changes, create a Pull Request (PR) using the GitHub agent. Do not ask the user to manually edit files via nano unless specifically requested.
3. **FULL CODE:** When updating a file, ALWAYS provide the COMPLETE file content. Do not use "find and replace" or partial code snippets.
4. **NO PASSWORDS:** If a command might require a password (like HTTPS git), stop and warn me. Assume SSH keys are used.
5. **IDENTICAL LOGIC:** When asked to replicate logic from the App, prioritize the App's exact logic over new inventions.
6. **ANTICIPATE:** What migh break with an implementation so we do fix thing we already did.
