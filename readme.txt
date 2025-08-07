# Custom Page/Post Restriction (custrest)

Restrict access to any post, page, or custom post type by login, user role, or time window. Includes per-post overrides, custom messages, audit logs, REST API, WP-CLI, import/export, and more.

---

## Features

- Restrict by post type, page, or custom post type
- Global and per-post/page overrides
- Role-based and time-based restrictions
- Custom restriction messages (HTML allowed)
- Ignore list for always-public content
- Shortcode: `[custrest_restricted]...[/custrest_restricted]`
- Gutenberg block for restricted content
- Full audit logging of blocked attempts
- Admin UI for logs and settings
- Import/export settings as JSON
- REST API and WP-CLI support
- Accessibility and translation-ready

---

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin via the Plugins menu.
3. Go to **Settings > Restrict Access** to configure.

---

## Usage

### Restrict Content

- Select post types to restrict in **Settings > Restrict Access**.
- Set allowed roles, time windows, and custom messages globally.
- Use the meta box in the post/page editor for per-item overrides.

### Shortcode

```
[custrest_restricted]Secret content[/custrest_restricted]
[custrest_restricted roles="editor,subscriber"]Role-based content[/custrest_restricted]
```

### Gutenberg Block

- Add the “Restricted Content” block to any post/page.

---

## Logs & Audit

- View all blocked attempts in **Settings > Restriction Logs**.
- Filter by reason, paginate, and see user/IP details.

---

## Import/Export

- Go to **Settings > Import/Export Restriction** to backup or migrate settings as JSON.

---

## REST API

- **Check restriction status:**
  ```
  GET /wp-json/custrest/v1/status/<post_id>
  ```
  Returns: `{ "post_id": 123, "restricted": true|false }`

---

## WP-CLI

- **Check restriction status:**
  ```
  wp custrest status <post_id>
  ```

---

## Developer Hooks & Filters

- `custrest_is_restricted` — Filter restriction logic.
- `custrest_redirect_url` — Filter redirect URL.
- `custrest_user_has_allowed_role` — Filter allowed role logic.
- `custrest_in_time_window` — Filter time window logic.
- Actions: `custrest_before_restriction_check`, `custrest_after_restriction_check`, `custrest_before_redirect`

---

## Uninstall

- Removes all settings, post meta, and logs.

---

## Translation

- All strings are translation-ready. Generate `.pot` with:
  ```
  wp i18n make-pot . languages/custrest.pot
  ```

---

## Screenshots

1. **Settings Page:**  
   ![Settings Page](screenshot-1.png)  
   _Configure global restrictions, allowed roles, time windows, and custom messages._

2. **Meta Box in Post Editor:**  
   ![Meta Box](screenshot-2.png)  
   _Override restriction, allowed roles, and time window per post/page._

3. **Restriction Logs UI:**  
   ![Logs UI](screenshot-3.png)  
   _View and filter all blocked access attempts._

4. **Import/Export UI:**  
   ![Import/Export](screenshot-4.png)  
   _Easily backup or migrate your settings as JSON._

---

## Changelog

### 1.0.0
- Initial release:  
  - Global and per-post/page restriction
  - Role-based and time-based access
  - Custom messages
  - Audit logs and admin UI
  - REST API and WP-CLI support
  - Import/export settings
  - Accessibility and translation-ready

---

## FAQ

**Q: Can I restrict by custom user meta or custom logic?**  
A: Yes! Use the `custrest_is_restricted` or `custrest_user_has_allowed_role` filters.

**Q: Can I show a custom message instead of redirect?**  
A: Yes, set a custom message globally or per post/page.

**Q: Is this plugin multisite compatible?**  
A: Yes, but settings are per-site by default.

---

## Support

For issues, feature requests, or contributions, please open an issue or pull request on GitHub.