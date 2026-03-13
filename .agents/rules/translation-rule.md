# Translation Rule

Any time a new text is added to the application, or an existing text is modified, it MUST be translated into both Czech (CZ) and English (EN).

## Requirements
- Use Laravel's translation system (`__('key')` or `@lang('key')`) for all user-facing strings.
- Maintain translation files in `resources/lang/cs.json` and `resources/lang/en.json` (or traditional `resources/lang/cs/` and `resources/lang/en/` directories).
- If one language version is updated, the other must be updated as well to maintain parity.
- Ensure that the UI can handle varying text lengths between languages.
